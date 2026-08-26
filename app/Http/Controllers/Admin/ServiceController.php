<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Http\Controllers\Concerns\ExportsExcel;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class ServiceController extends Controller
{
    use ExportsExcel;

    public function __construct()
    {
        $this->middleware('can:admin.services.index')->only('index');
        $this->middleware('can:admin.services.create')->only('create', 'store');
        $this->middleware('can:admin.services.edit')->only('edit', 'update');
        $this->middleware('can:admin.services.destroy')->only('destroy');
        $this->middleware('can:admin.services.pdf')->only('pdf', 'excel');
    }

    public function index(Request $request)
    {
        $query = Service::with('serviceCategory')->where('status', 1);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('serviceCategory', function ($categoryQuery) use ($search) {
                        $categoryQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $services = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('admin.services.index', compact('services'));
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

        return redirect()->route('admin.services.index', $service);
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

    public function excel()
    {
        $services = Service::where('status', 1)->with('serviceCategory')->orderBy('id', 'desc')->get();

        $rows = $services->map(function (Service $service) {
            return [
                $service->name,
                $service->serviceCategory->name ?? '—',
                (float) $service->price,
                $this->formatDate($service->created_at),
            ];
        });

        return $this->streamExcel('servicios_' . now()->format('Y-m-d') . '.xlsx', [
            'Servicio', 'Categoría', 'Precio', 'Fecha de registro',
        ], $rows);
    }

}
