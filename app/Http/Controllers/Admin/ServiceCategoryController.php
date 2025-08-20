<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf as PDF; 

class ServiceCategoryController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('can:admin.service_categories.index')->only('index');
        $this->middleware('can:admin.service_categories.create')->only('create', 'store');
        $this->middleware('can:admin.service_categories.edit')->only('edit', 'update');
        $this->middleware('can:admin.service_categories.destroy')->only('destroy');
        $this->middleware('can:admin.service_categories.pdf')->only('pdf');
    }


    public function index()
    {
        $service_categories = ServiceCategory::where('status',1)->orderBy('id', 'desc')->paginate(10);
        return view('admin.service_categories.index', compact('service_categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $service_categories = ServiceCategory::all();
        return view('admin.service_categories.create', compact('service_categories'));

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required'
        ]);
 

        ServiceCategory::create([
            'name' => ucwords(strtolower($request->name)),
        ]);

        session()->flash('swal', [
            'title' => 'Categoria Creada',
            'text' => '¡Bien Hecho!.',
            'icon' => 'success',
        ]);

        return redirect()->route('admin.service_categories.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(ServiceCategory $serviceCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ServiceCategory $serviceCategory)
    {
        return view('admin.service_categories.edit', compact('serviceCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ServiceCategory $serviceCategory)
    {
        $request->validate([
            'name' => 'required'
        ]); 
   
        $serviceCategory->update([
            'name' => ucwords(strtolower($request->name)),
        ]);

        session()->flash('swal', [
            'title' => 'Bien hecho!',
            'text' => 'Familia actualizada correctamente',
            'icon' => 'success'
        ]);
        return redirect()->route('admin.service_categories.index', $serviceCategory);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServiceCategory $serviceCategory)
    {

        if($serviceCategory->services()->count() > 0){
            session()->flash('swal', [
                'title' => 'Error!',
                'text' => 'No se puede eliminar la categoria porque tiene servicios asociados',
                'icon' => 'error'
            ]);
            return redirect()->route('admin.service_categories.index', $serviceCategory);
        }

        // Cambiar el estado a 0 (baja) en lugar de eliminar el registro
        $serviceCategory->update(['status' => 0]);

        //$serviceCategory->delete();

        
        session()->flash('swal', [
            'title' => 'Categoria eliminada',
            'text' => 'Bien hecho!',
            'icon' => 'success'  
                     

        ]);
        return redirect()->route('admin.service_categories.index');


        // $serviceCategory->delete();
        // session()->flash('swal', [
        //     'title' => 'Categoria eliminada',
        //     'text' => '¡Bien Hecho!.',
        //     'icon' => 'success',
        // ]);
        // return redirect()->route('admin.service_categories.index'); 
    }


    public function pdf()
    {
        // Obtener las categorías de servicio activas
        $service_categories = ServiceCategory::where('status',1)->orderBy('id', 'desc')->get();
        
        // Generar el PDF a partir de la vista 'service_categories.pdf
        $pdf = PDF::loadView('admin.service_categories.pdf', compact('service_categories')); 

        // Mostrar el PDF en el navegador
        return $pdf->stream('admin.service_categories.pdf');
    }



}
