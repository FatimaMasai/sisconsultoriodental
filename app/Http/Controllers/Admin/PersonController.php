<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Person;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PersonController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:admin.persons.index')->only('index');
        $this->middleware('can:admin.persons.create')->only('create', 'store');
        $this->middleware('can:admin.persons.edit')->only('edit', 'update');
        $this->middleware('can:admin.persons.destroy')->only('destroy');
        $this->middleware('can:admin.persons.pdf')->only('pdf');
    }

    public function index()
    {
        $persons = Person::where('status',1)->orderBy('id', 'desc')->paginate(10);
         
        // Calcula la edad de cada persona
        foreach ($persons as $person) {
            $person->age = Carbon::parse($person->birth_date)->age; // Esto calculará la edad
        }

        return view('admin.persons.index', compact('persons'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.persons.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
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
 
        Person::create([
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

        session()->flash('swal', [
            'title' => 'Persona Creada',
            'text' => '¡Bien Hecho!.',
            'icon' => 'success',
        ]);

        return redirect()->route('admin.persons.index');


    }

    /**
     * Display the specified resource.
     */
    public function show(Person $person)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Person $person)
    {
        return view('admin.persons.edit', compact('person'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Person $person)
    {
        $request->validate([
            'name' => 'required',
            'last_name_father' => 'required',
            'last_name_mother' => 'required', 

             
            'identity_card' => 'required|numeric|unique:people,identity_card,' . $person->id, // Evitar conflicto con el valor actual

            'birth_date' => 'required|date_format:Y-m-d',
            'gender' => 'required',

            'phone' => 'required|numeric',
            'email' => 'required|email',
            'address' => 'required', 

        ]);

        

        $person->update([
            'name' => ucwords(strtolower($request->name)),
            'last_name_father' => ucwords(strtolower($request->last_name_father)),
            'last_name_mother' => ucwords(strtolower($request->last_name_mother)),

            'identity_card' => $request->identity_card,
            'birth_date' => $request->birth_date,
            'gender' => $request->gender,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,

            'status' => $request->status, // El valor de status se actualiza con el select
        ]);

        session()->flash('swal', [
            'title' => 'Persona Actualizada',
            'text' => '¡Bien Hecho!.',
            'icon' => 'success',
        ]);

        return redirect()->route('admin.persons.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Person $person)
    {

        // Cambiar el estado a 0 (baja) en lugar de eliminar el registro
        $person->update(['status' => 0]);

        //$person->delete();
        session()->flash('swal', [ 

            'title' => 'Persona eliminada',
            'text' => '¡Bien Hecho!.',
            'icon' => 'success',
        ]);
        return redirect()->route('admin.persons.index'); 
    }
    
    public function pdf()
    {
         
    }
} 