<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Speciality;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf as PDF; 


class SpecialityController extends Controller
{
   

    public function __construct()
    {
        $this->middleware('can:admin.specialities.index')->only('index');
        $this->middleware('can:admin.specialities.create')->only('create', 'store');
        $this->middleware('can:admin.specialities.edit')->only('edit', 'update');
        $this->middleware('can:admin.specialities.destroy')->only('destroy');
        $this->middleware('can:admin.specialities.pdf')->only('pdf');
    }

    public function index()
    {
        $specialities = Speciality::where('status',1)->orderBy('id', 'desc')->paginate(10);
        return view('admin.specialities.index', compact('specialities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $specialities = Speciality::all();
        return view('admin.specialities.create', compact('specialities'));

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([

            'name' => 'required'

        ]);

        Speciality::create([
            'name' => ucwords(strtolower($request->name)),
            'status' => 1, // "Alta"
        ]);

        session()->flash('swal', [
            'title' => 'Especialidad Creada',
            'text' => '¡Bien Hecho!.',
            'icon' => 'success',
        ]);

        return redirect()->route('admin.specialities.index');

    }

    /**
     * Display the specified resource.
     */
    public function show(Speciality $speciality)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Speciality $speciality)
    {
        return view('admin.specialities.edit', compact('speciality'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Speciality $speciality)
    {
        $request->validate([

            'name' => 'required'

        ]);

        $speciality->update([
            'name' => ucwords(strtolower($request->name)),
        ]);

        session()->flash('swal', [
            'title' => 'Especialidad actualizada',
            'text' => '¡Bien Hecho!.',
            'icon' => 'success',
        ]);

        return redirect()->route('admin.specialities.edit', $speciality);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Speciality $speciality)
    {

        if($speciality->doctors()->count() > 0){
            session()->flash('swal', [
                'title' => 'Error!',
                'text' => 'No se puede eliminar la categoria porque tiene servicios asociados',
                'icon' => 'error'
            ]);
            return redirect()->route('admin.specialities.index', $speciality);
        }

        // Cambiar el estado a 0 (baja) en lugar de eliminar el registro
        $speciality->update(['status' => 0]);

        //$serviceCategory->delete();

        
        session()->flash('swal', [
            'title' => 'Especialidad eliminada',
            'text' =>  '¡Bien hecho!',
            'icon' => 'success'           

        ]);
        return redirect()->route('admin.specialities.index');

    }
    public function pdf()
    {
        // Obtener las categorías de servicio activas
        $specialities = Speciality::where('status',1)->orderBy('id', 'desc')->get();
        
        // Generar el PDF a partir de la vista 'specialities.pdf
        $pdf = PDF::loadView('admin.specialities.pdf', compact('specialities')); 

        // Mostrar el PDF en el navegador
        return $pdf->stream('admin.specialities.pdf');
    }
}
