<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Person;
use App\Models\Speciality;
use App\Http\Controllers\Concerns\ExportsExcel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

use Barryvdh\DomPDF\Facade\Pdf as PDF;


class DoctorController extends Controller
{
    use ExportsExcel;

    public function __construct()
    {
        // $this->middleware('auth');
        $this->middleware('can:admin.doctors.index')->only('index');
        $this->middleware('can:admin.doctors.create')->only('create', 'store');
        $this->middleware('can:admin.doctors.edit')->only('edit', 'update');
        $this->middleware('can:admin.doctors.pdf')->only('pdf', 'excel');
    }


    public function index()
    {
        // El listado y la búsqueda en tiempo real los maneja el componente
        // Livewire admin.doctor-search (ver app/Livewire/Admin/DoctorSearch.php).
        return view('admin.doctors.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Solo personas que todavía no tienen un registro de doctor asociado.
        $persons = Person::where('status', 1)->whereDoesntHave('doctor')->orderBy('id', 'desc')->get();
        $specialities = Speciality::all();
        return view('admin.doctors.create', compact('persons', 'specialities'));

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $personMode = $request->person_mode === 'existing' ? 'existing' : 'new';

        $rules = [
            'person_mode' => 'required|in:new,existing',
            'speciality_id' => 'required|exists:specialities,id',
        ];

        $messages = [
            'person_mode.required' => 'Debe indicar si la persona es nueva o ya existe.',
            'speciality_id.required' => 'Debe seleccionar una especialidad.',
            'speciality_id.exists' => 'La especialidad seleccionada no es válida.',
        ];

        if ($personMode === 'existing') {
            $rules['person_id'] = 'required|exists:people,id';
            $messages['person_id.required'] = 'Debe seleccionar una persona.';
        } else {
            $rules = array_merge($rules, [
                'name' => 'required',
                'last_name_father' => 'required',
                'last_name_mother' => 'nullable',
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
                    'last_name_mother' => $request->last_name_mother ? ucwords(strtolower($request->last_name_mother)) : null,
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

            Doctor::create([
                'status' => 1,
                'person_id' => $personId,
                'speciality_id' => $request->speciality_id,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            session()->flash('swal', [
                'title' => 'Error',
                'text' => 'No se pudo registrar el doctor. Intente nuevamente.',
                'icon' => 'error',
            ]);

            return back()->withInput();
        }

        session()->flash('swal', [
            'title' => 'Doctor Creado',
            'text' => '¡Bien Hecho!.',
            'icon' => 'success',
        ]);

        return redirect()->route('admin.doctors.index');

    }

    /**
     * Display the specified resource.
     */
    public function show(Doctor $doctor)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Doctor $doctor)
    {
        $doctor->load('person');
        $specialities = Speciality::all();

        return view('admin.doctors.edit', compact('specialities', 'doctor'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Doctor $doctor)
    {
        $request->validate([
            'name' => 'required',
            'last_name_father' => 'required',
            'last_name_mother' => 'nullable',
            'identity_card' => 'required|numeric|unique:people,identity_card,' . $doctor->person_id,
            'birth_date' => 'required|date_format:Y-m-d',
            'gender' => 'required',
            'phone' => 'required|numeric',
            'email' => 'required|email',
            'address' => 'required',

            'speciality_id' => 'required|exists:specialities,id',
            'status' => 'required|in:0,1',
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'last_name_father.required' => 'El apellido paterno es obligatorio.',
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

            'speciality_id.required' => 'Debe seleccionar una especialidad.',
            'speciality_id.exists' => 'La especialidad seleccionada no es válida.',
            'status.required' => 'Debe seleccionar el estado.',
        ]);

        DB::beginTransaction();
        try {
            $doctor->person->update([
                'name' => ucwords(strtolower($request->name)),
                'last_name_father' => ucwords(strtolower($request->last_name_father)),
                'last_name_mother' => $request->last_name_mother ? ucwords(strtolower($request->last_name_mother)) : null,
                'identity_card' => $request->identity_card,
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
            ]);

            $doctor->update([
                'status' => $request->status,
                'speciality_id' => $request->speciality_id,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            session()->flash('swal', [
                'title' => 'Error',
                'text' => 'No se pudo actualizar el doctor. Intente nuevamente.',
                'icon' => 'error',
            ]);

            return back()->withInput();
        }

        session()->flash('swal', [
            'title' => 'Doctor Actualizado',
            'text' => '¡Bien Hecho!.',
            'icon' => 'success',
        ]);

        return redirect()->route('admin.doctors.index');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Doctor $doctor)
    {

        $doctor->update([
            'status' => 0
        ]);

        session()->flash('swal', [
            'title' => 'Doctor Eliminado',
            'text' => '¡Bien Hecho!.',
            'icon' => 'success',
        ]);
        return redirect()->route('admin.doctors.index');

    }

    public function pdf()
    {
        $doctors = Doctor::where('status', 1)->with('person')->orderBy('id', 'desc')->get();

        $pdf = PDF::loadView('admin.doctors.pdf', compact('doctors'));

        return $pdf->stream('admin.doctors.pdf');

    }

    public function excel()
    {
        $doctors = Doctor::where('status', 1)->with(['person', 'speciality'])->orderBy('id', 'desc')->get();

        $rows = $doctors->map(function (Doctor $doctor) {
            return [
                trim($doctor->person->name . ' ' . $doctor->person->last_name_father . ' ' . $doctor->person->last_name_mother),
                $doctor->speciality->name ?? '—',
                $doctor->person->gender,
                $doctor->person->phone,
                $this->formatDate($doctor->person->created_at),
            ];
        });

        return $this->streamExcel('doctores_' . now()->format('Y-m-d') . '.xlsx', [
            'Nombre', 'Especialidad', 'Sexo', 'Celular', 'Fecha de registro',
        ], $rows);
    }
}
