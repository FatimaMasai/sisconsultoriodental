<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf as PDF; 

class ServiceController extends Controller
{
    

    public function __construct()
    {
        $this->middleware('can:admin.services.index')->only('index');
        $this->middleware('can:admin.services.create')->only('create', 'store');
        $this->middleware('can:admin.services.edit')->only('edit', 'update');
        $this->middleware('can:admin.services.destroy')->only('destroy');
        $this->middleware('can:admin.services.pdf')->only('pdf');
    }

    public function index()
    { 
        return view('admin.services.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
        $serviceCategories = ServiceCategory::all();

        return view('admin.services.create', compact('serviceCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric'
        ]);

         

        Service::create([
            'name' => ucwords(strtolower($request->name)),
            'price' => $request->price,
            'status' => 1, // "Alta"
            'service_category_id' => $request->service_category_id,
        ]);
        

        session()->flash('swal', [
            'title' => 'Servicio Creado',
            'text' => '¡Bien Hecho!.',
            'icon' => 'success',
        ]);

        return redirect()->route('admin.services.index');

    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        
    }

     


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service)
    {
        $serviceCategories = ServiceCategory::all();
        return view('admin.services.edit', compact('service', 'serviceCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Service $service)
    {
        $request->validate([
            'service_category_id' => 'required|exists:service_categories,id',
            'name' => 'required',
            'price' => 'required|numeric'
        ]);
 

        $service->update([
            'name' => ucwords(strtolower($request->name)),
            'price' => $request->price,
            'status' => $request->status, // El valor de status se actualiza con el select
            'service_category_id' => $request->service_category_id,
        ]);

        session()->flash('swal', [
            'title' => 'Servicio Actualizado',
            'text' => '¡Bien Hecho!.',
            'icon' => 'success',
        ]);

        return redirect()->route('admin.services.edit', $service);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {

        // Cambiar el estado a 0 (baja) en lugar de eliminar el registro
        $service->update(['status' => 0]);

        //$service->delete();
        
        session()->flash('swal', [
            'title' => 'Servicio eliminado',
            'text' => '¡Bien Hecho!.',
            'icon' => 'success',
        ]);
        return redirect()->route('admin.services.index'); 
    
    }

    public function pdf()
    {
        // Obtener las categorías de servicio activas
        $services = Service::where('status',1)->orderBy('id', 'desc')->get();
        
        // Generar el PDF a partir de la vista 'service.pdf
        $pdf = PDF::loadView('admin.services.pdf', compact('services')); 

        // Mostrar el PDF en el navegador
        return $pdf->stream('admin.services.pdf');
    }



}
