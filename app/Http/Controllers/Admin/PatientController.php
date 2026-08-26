<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Person;
use App\Http\Controllers\Concerns\ExportsExcel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class PatientController extends Controller
{
    use ExportsExcel;

    /**
     * Display a listing of the resource.
     */

    public function __construct()
    {
        $this->middleware('can:admin.patients.index')->only('index');
        $this->middleware('can:admin.patients.create')->only('create', 'store');
        $this->middleware('can:admin.patients.edit')->only('edit', 'update');
        $this->middleware('can:admin.patients.destroy')->only('destroy');
        $this->middleware('can:admin.patients.pdf')->only('pdf', 'excel');
    }

    public function index(Request $request)
    {
        $query = Patient::where('status', 1)->with('person');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->whereHas('person', function ($personQuery) use ($search) {
                $personQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('last_name_father', 'like', "%{$search}%")
                    ->orWhere('last_name_mother', 'like', "%{$search}%");
            });
        }

        $patients = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

         // Calcula la edad de cada persona asociada al paciente
        foreach ($patients as $patient) {
            // Calcula la edad de la persona asociada al paciente
            $patient->person->age = Carbon::parse($patient->person->birth_date)->age;
        }



        return view('admin.patients.index', compact('patients'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Solo personas que todavía no tienen un registro de paciente asociado.
        $persons = Person::where('status', 1)->whereDoesntHave('patient')->orderBy('id', 'desc')->get();
        return view('admin.patients.create', compact('persons'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $personMode = $request->person_mode === 'existing' ? 'existing' : 'new';

        $rules = [
            'person_mode' => 'required|in:new,existing',

            'allergy' => 'required',
            'observation' => 'required',
            'recommended_by' => 'required',
            'responsible_person' => 'required',
            'medical_history' => 'required', //antecedentes
        ];

        $messages = [
            'person_mode.required' => 'Debe indicar si la persona es nueva o ya existe.',
            'allergy.required' => 'El campo alergia es obligatorio.',
            'observation.required' => 'El campo observación es obligatorio.',
            'recommended_by.required' => 'El campo recomendado por es obligatorio.',
            'responsible_person.required' => 'El campo persona responsable es obligatorio.',
            'medical_history.required' => 'El campo antecedentes es obligatorio.',
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

            Patient::create([
                'allergy' => ucfirst(strtolower($request->allergy)),
                'observation' => ucfirst(strtolower($request->observation)),
                'recommended_by' => ucfirst(strtolower($request->recommended_by)),
                'responsible_person' => ucfirst(strtolower($request->responsible_person)),
                'medical_history' => ucfirst(strtolower($request->medical_history)), //antecedentes
                'status' => 1, // "Alta"
                'person_id' => $personId,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            session()->flash('swal', [
                'title' => 'Error',
                'text' => 'No se pudo registrar el paciente. Intente nuevamente.',
                'icon' => 'error',
            ]);

            return back()->withInput();
        }

        session()->flash('swal', [
            'title' => 'Paciente Creado',
            'text' => '¡Bien Hecho!.',
            'icon' => 'success',
        ]);

        return redirect()->route('admin.patients.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Patient $patient)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Patient $patient)
    {
        $patient->load('person');
        return view('admin.patients.edit', compact('patient'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Patient $patient)
    {
        $request->validate([
            'name' => 'required',
            'last_name_father' => 'required',
            'last_name_mother' => 'required',
            'identity_card' => 'required|numeric|unique:people,identity_card,' . $patient->person_id,
            'birth_date' => 'required|date_format:Y-m-d',
            'gender' => 'required',
            'phone' => 'required|numeric',
            'email' => 'required|email',
            'address' => 'required',

            'allergy' => 'required',
            'observation' => 'required',
            'recommended_by' => 'required',
            'responsible_person' => 'required',
            'medical_history' => 'required', //antecedentes
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

            'allergy.required' => 'El campo alergia es obligatorio.',
            'observation.required' => 'El campo observación es obligatorio.',
            'recommended_by.required' => 'El campo recomendado por es obligatorio.',
            'responsible_person.required' => 'El campo persona responsable es obligatorio.',
            'medical_history.required' => 'El campo antecedentes es obligatorio.',
            'status.required' => 'Debe seleccionar el estado.',
        ]);

        DB::beginTransaction();
        try {
            $patient->person->update([
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

            $patient->update([
                'allergy' => ucfirst(strtolower($request->allergy)),
                'observation' => ucfirst(strtolower($request->observation)),
                'recommended_by' => ucfirst(strtolower($request->recommended_by)),
                'responsible_person' => ucfirst(strtolower($request->responsible_person)),
                'medical_history' => ucfirst(strtolower($request->medical_history)),
                'status' => $request->status, // El valor de status se actualiza con el select
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            session()->flash('swal', [
                'title' => 'Error',
                'text' => 'No se pudo actualizar el paciente. Intente nuevamente.',
                'icon' => 'error',
            ]);

            return back()->withInput();
        }

        session()->flash('swal', [
            'title' => 'Paciente Actualizado',
            'text' => '¡Bien Hecho!.',
            'icon' => 'success',
        ]);

        return redirect()->route('admin.patients.index');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient)
    {
        $patient->update(['status' =>  0]);

        //$patient->delete();
        session()->flash('swal', [
            'title' => 'Paciente eliminado',
            'text' => '¡Bien Hecho!.',
            'icon' => 'success',
        ]);
        return redirect()->route('admin.patients.index');


    }

    public function pdf()
    {
        $patients = Patient::where('status', 1)->with('person')->orderBy('id', 'desc')->get();

        $pdf = PDF::loadView('admin.patients.pdf', compact('patients'));

        return $pdf->stream('admin.patients.pdf');

    }

    public function excel()
    {
        $patients = Patient::where('status', 1)->with('person')->orderBy('id', 'desc')->get();

        $rows = $patients->map(function (Patient $patient) {
            $edad = $patient->person->birth_date ? Carbon::parse($patient->person->birth_date)->age : '';

            return [
                trim($patient->person->name . ' ' . $patient->person->last_name_father . ' ' . $patient->person->last_name_mother),
                $patient->person->gender,
                $edad,
                $patient->person->phone,
                $patient->allergy,
                $patient->observation,
                $this->formatDate($patient->person->created_at),
            ];
        });

        return $this->streamExcel('pacientes_' . now()->format('Y-m-d') . '.xlsx', [
            'Paciente', 'Sexo', 'Edad', 'Celular', 'Alergias', 'Observación', 'Fecha de registro',
        ], $rows);
    }
}
