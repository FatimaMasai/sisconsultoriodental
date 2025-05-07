<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Person;
use App\Models\Speciality;
use Illuminate\Http\Request;

use Carbon\Carbon;


class DoctorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $doctors = Doctor::where('status',1)->orderBy('id', 'desc')->paginate(10);

         // Calcula la edad de cada persona asociada al paciente
         foreach ($doctors as $doctor) {
            // Calcula la edad de la persona asociada al paciente
            $doctor->person->age = Carbon::parse($doctor->person->birth_date)->age;
        }

        return view('admin.doctors.index', compact('doctors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $persons = Person::all();
        $specialities = Speciality::all();
        return view('admin.doctors.create', compact('persons', 'specialities'));

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Doctor::create([
            'status' => 1,
            'person_id' => $request->person_id,
            'speciality_id' => $request->speciality_id
        ]);

        session()->flash('swal', [
            'title' => 'Servicio Creado',
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
        $persons = Person::all();
        $specialities = Speciality::all();

        return view('admin.doctors.edit', compact('persons', 'specialities', 'doctor'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Doctor $doctor)
    {
        
        $doctor->update([
            'status' => 1,
            'person_id' => $request->person_id,
            'speciality_id' => $request->speciality_id
        ]);
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
         
    }
}
