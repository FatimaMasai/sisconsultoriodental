<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Models\Supplier;
use App\Http\Controllers\Concerns\ExportsExcel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class SupplierController extends Controller
{
    use ExportsExcel;

    public function __construct()
    {
        $this->middleware('can:admin.suppliers.index')->only('index');
        $this->middleware('can:admin.suppliers.create')->only('create', 'store');
        $this->middleware('can:admin.suppliers.edit')->only('edit', 'update');
        $this->middleware('can:admin.suppliers.destroy')->only('destroy');
        $this->middleware('can:admin.suppliers.pdf')->only('pdf', 'excel');
    }

    public function index(Request $request)
    {
        $query = Supplier::where('status', 1)->with('person');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('company', 'like', "%{$search}%")
                    ->orWhere('nit', 'like', "%{$search}%")
                    ->orWhereHas('person', function ($personQuery) use ($search) {
                        $personQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('last_name_father', 'like', "%{$search}%")
                            ->orWhere('last_name_mother', 'like', "%{$search}%");
                    });
            });
        }

        $suppliers = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        foreach ($suppliers as $supplier) {
            // Calcula la edad de la persona asociada al proveedor
            $supplier->person->age = Carbon::parse($supplier->person->birth_date)->age;
        }
        return view('admin.suppliers.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Solo personas que todavía no tienen un registro de proveedor asociado.
        $persons = Person::where('status', 1)->whereDoesntHave('supplier')->orderBy('id', 'desc')->get();
        return view('admin.suppliers.create', compact('persons'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $personMode = $request->person_mode === 'existing' ? 'existing' : 'new';

        $rules = [
            'person_mode' => 'required|in:new,existing',
            'company' => 'required',
            'nit' => 'required|numeric',
        ];

        $messages = [
            'person_mode.required' => 'Debe indicar si la persona es nueva o ya existe.',
            'company.required' => 'La empresa es obligatoria.',
            'nit.required' => 'El NIT es obligatorio.',
            'nit.numeric' => 'El NIT solo debe contener números.',
        ];

        if ($personMode === 'existing') {
            $rules['person_id'] = 'required|exists:people,id';
            $messages['person_id.required'] = 'Debe seleccionar una persona.';
        } else {
            $rules = array_merge($rules, [
                'name' => 'required',
                'last_name_father' => 'required',
                'last_name_mother' => 'required',
                'identity_card' => 'required|numeric|unique:people,identity_card',
                'birth_date' => 'required|date_format:Y-m-d',
                'gender' => 'required',
                'phone' => 'required|numeric',
                'email' => 'required|email',
                'address' => 'required',
            ]);

            $messages = array_merge($messages, [
                'name.required' => 'El nombre es obligatorio.',
                'last_name_father.required' => 'El apellido paterno es obligatorio.',
                'last_name_mother.required' => 'El apellido materno es obligatorio.',
                'identity_card.required' => 'El carnet de identidad es obligatorio.',
                'identity_card.numeric' => 'El carnet de identidad solo debe contener números.',
                'identity_card.unique' => 'Ese número de carnet de identidad ya está registrado.',
                'birth_date.required' => 'La fecha de nacimiento es obligatoria.',
                'birth_date.date_format' => 'La fecha de nacimiento no es válida.',
                'gender.required' => 'Debe seleccionar el sexo.',
                'phone.required' => 'El celular es obligatorio.',
                'phone.numeric' => 'El celular solo debe contener números.',
                'email.required' => 'El email es obligatorio.',
                'email.email' => 'Ingrese un email válido.',
                'address.required' => 'La dirección es obligatoria.',
            ]);
        }

        $request->validate($rules, $messages);

        DB::beginTransaction();
        try {
            if ($personMode === 'existing') {
                $personId = $request->person_id;
            } else {
                $person = Person::create([
                    'name' => ucwords(strtolower($request->name)),
                    'last_name_father' => ucwords(strtolower($request->last_name_father)),
                    'last_name_mother' => ucwords(strtolower($request->last_name_mother)),
                    'identity_card' => $request->identity_card,
                    'birth_date' => $request->birth_date,
                    'gender' => $request->gender,
                    'phone' => $request->phone,
                    'email' => $request->email,
                    'address' => $request->address,
                    'status' => 1, // "Alta"
                ]);
                $personId = $person->id;
            }

            Supplier::create([
                'company' => ucfirst(strtolower($request->company)),
                'nit' => $request->nit,
                'person_id' => $personId,
                'status' => 1,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            session()->flash('swal', [
                'title' => 'Error',
                'text' => 'No se pudo registrar el proveedor. Intente nuevamente.',
                'icon' => 'error',
            ]);

            return back()->withInput();
        }

        session()->flash('swal', [
            'title' => 'Proveedor Creado',
            'text' => '¡Bien Hecho!.',
            'icon' => 'success',

        ]);

        return redirect()->route('admin.suppliers.index');



    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier)
    {
        $supplier->load('person');
        return view('admin.suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => 'required',
            'last_name_father' => 'required',
            'last_name_mother' => 'required',
            'identity_card' => 'required|numeric|unique:people,identity_card,' . $supplier->person_id,
            'birth_date' => 'required|date_format:Y-m-d',
            'gender' => 'required',
            'phone' => 'required|numeric',
            'email' => 'required|email',
            'address' => 'required',

            'company' => 'required',
            'nit' => 'required|numeric',
            'status' => 'required|in:0,1',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'last_name_father.required' => 'El apellido paterno es obligatorio.',
            'last_name_mother.required' => 'El apellido materno es obligatorio.',
            'identity_card.required' => 'El carnet de identidad es obligatorio.',
            'identity_card.numeric' => 'El carnet de identidad solo debe contener números.',
            'identity_card.unique' => 'Ese número de carnet de identidad ya está registrado.',
            'birth_date.required' => 'La fecha de nacimiento es obligatoria.',
            'birth_date.date_format' => 'La fecha de nacimiento no es válida.',
            'gender.required' => 'Debe seleccionar el sexo.',
            'phone.required' => 'El celular es obligatorio.',
            'phone.numeric' => 'El celular solo debe contener números.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'Ingrese un email válido.',
            'address.required' => 'La dirección es obligatoria.',

            'company.required' => 'La empresa es obligatoria.',
            'nit.required' => 'El NIT es obligatorio.',
            'nit.numeric' => 'El NIT solo debe contener números.',
            'status.required' => 'Debe seleccionar el estado.',
        ]);

        DB::beginTransaction();
        try {
            $supplier->person->update([
                'name' => ucwords(strtolower($request->name)),
                'last_name_father' => ucwords(strtolower($request->last_name_father)),
                'last_name_mother' => ucwords(strtolower($request->last_name_mother)),
                'identity_card' => $request->identity_card,
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
            ]);

            $supplier->update([
                'company' => ucfirst(strtolower($request->company)),
                'nit' => $request->nit,
                'status' => $request->status,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            session()->flash('swal', [
                'title' => 'Error',
                'text' => 'No se pudo actualizar el proveedor. Intente nuevamente.',
                'icon' => 'error',

            ]);

            return back()->withInput();
        }

        session()->flash('swal', [
            'title' => 'Proveedor Actualizado',
            'text' => '¡Bien Hecho!.',
            'icon' => 'success',

        ]);

        return redirect()->route('admin.suppliers.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        $supplier->update([
            'status' => 0,
        ]);

        session()->flash('swal', [
            'title' => 'Proveedor Eliminado',
            'text' => '¡Bien Hecho!.',
            'icon' => 'success',

        ]);

        return redirect()->route('admin.suppliers.index');
    }
    public function pdf()
    {
        $suppliers = Supplier::where('status', 1)->with('person')->orderBy('id', 'desc')->get();

        $pdf = PDF::loadView('admin.suppliers.pdf', compact('suppliers'));

        return $pdf->stream('admin.suppliers.pdf');

    }

    public function excel()
    {
        $suppliers = Supplier::where('status', 1)->with('person')->orderBy('id', 'desc')->get();

        $rows = $suppliers->map(function (Supplier $supplier) {
            return [
                trim($supplier->person->name . ' ' . $supplier->person->last_name_father . ' ' . $supplier->person->last_name_mother),
                $supplier->person->phone,
                $supplier->nit,
                $supplier->company,
                $this->formatDate($supplier->person->created_at),
            ];
        });

        return $this->streamExcel('proveedores_' . now()->format('Y-m-d') . '.xlsx', [
            'Nombre', 'Celular', 'NIT', 'Empresa', 'Fecha de registro',
        ], $rows);
    }
}
