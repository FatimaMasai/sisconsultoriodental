<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Carbon\Carbon;


class SupplierController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('can:admin.suppliers.index')->only('index');
        $this->middleware('can:admin.suppliers.create')->only('create', 'store');
        $this->middleware('can:admin.suppliers.edit')->only('edit', 'update');
        $this->middleware('can:admin.suppliers.destroy')->only('destroy');
        $this->middleware('can:admin.suppliers.pdf')->only('pdf');
    }

    public function index()
    {
        $suppliers = Supplier::where('status',1)
        ->with('person') //obtiene los datos de la tabla persona
        ->orderBy('id', 'desc')
        ->paginate(10);

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
        $persons = Person::all();
        return view('admin.suppliers.create', compact('persons'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'company' => 'required',
            'nit' => 'required', 

        ]);

        Supplier::create([
            'company' => ucfirst(strtolower($request->company)),
            'nit' => $request->nit,
            'person_id' => $request->person_id,
            'status' => 1,
        ]);

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
        $persons = Person::all();
        return view('admin.suppliers.edit', compact('supplier', 'persons'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'company' => 'required',
            'nit' => 'required', 

        ]);

        $supplier->update([ 

            'company' => ucfirst(strtolower($request->company)),
            'nit' => $request->nit,
            'status' => 1,
            'person_id' => $request->person_id,
        ]);

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
         
    }
}
