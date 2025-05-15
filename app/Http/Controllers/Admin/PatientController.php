<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Person;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function __construct()
    {
        $this->middleware('can:admin.patients.index')->only('index');
        $this->middleware('can:admin.patients.create')->only('create', 'store');
        $this->middleware('can:admin.patients.edit')->only('edit', 'update');
        $this->middleware('can:admin.patients.destroy')->only('destroy');
        $this->middleware('can:admin.patients.pdf')->only('pdf');
    }

    public function index()
    {
        $patients = Patient::where('status',1)
        ->with('person') // Esto carga la relación 'person' para obtener los datos de la persona
        ->orderBy('id', 'desc')
        ->paginate(10);

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
        $persons = Person::all();
        return view('admin.patients.create', compact( 'persons'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([

            'allergy' => 'required',
            'observation' => 'required',
            'recommended_by' => 'required',

            'responsible_person' => 'required',
            'medical_history' => 'required', //antecedentes

        ]);


        Patient::create([

            'allergy' => ucfirst(strtolower($request->allergy)),
            'observation' => ucfirst(strtolower($request->observation)),
            'recommended_by' => ucfirst(strtolower($request->recommended_by)),

            'responsible_person' => ucfirst(strtolower($request->responsible_person)),
            'medical_history' => ucfirst(strtolower($request->medical_history)), //antecedentes

            'status' => 1, // "Alta"
            'person_id' => $request->person_id,

        ]);


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
        $persons = Person::all();
        return view('admin.patients.edit', compact('patient', 'persons'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Patient $patient)
    {
        $request->validate([

            'allergy' => 'required',
            'observation' => 'required',
            'recommended_by' => 'required',

            'responsible_person' => 'required',
            'medical_history' => 'required', //antecedentes

        ]);

        $patient->update([

            'allergy' => ucfirst(strtolower($request->allergy)),
            'observation' => ucfirst(strtolower($request->observation)),
            'recommended_by' => ucfirst(strtolower($request->recommended_by)),

            'responsible_person' => ucfirst(strtolower($request->responsible_person)),
            'medical_history' => ucfirst(strtolower($request->medical_history)),

            'status' => $request->status, // El valor de status se actualiza con el select
            'person_id' => $request->person_id,

        ]);

        session()->flash('swal', [
            'title' => 'Persona Actualizada',
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
}
