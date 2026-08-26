<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategory;
use App\Http\Controllers\Concerns\ExportsExcel;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class ServiceCategoryController extends Controller
{
    use ExportsExcel;

    public function __construct()
    {
        $this->middleware('can:admin.service_categories.index')->only('index');
        $this->middleware('can:admin.service_categories.create')->only('create', 'store');
        $this->middleware('can:admin.service_categories.edit')->only('edit', 'update');
        $this->middleware('can:admin.service_categories.destroy')->only('destroy');
        $this->middleware('can:admin.service_categories.pdf')->only('pdf', 'excel');
    }


    public function index(Request $request)
    {
        $query = ServiceCategory::where('status', 1);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . trim($request->search) . '%');
        }

        $service_categories = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();
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

    public function excel()
    {
        $service_categories = ServiceCategory::where('status', 1)->orderBy('id', 'desc')->get();

        $rows = $service_categories->map(function (ServiceCategory $category) {
            return [
                $category->name,
                $this->formatDate($category->created_at),
            ];
        });

        return $this->streamExcel('categorias_servicio_' . now()->format('Y-m-d') . '.xlsx', [
            'Nombre', 'Fecha de registro',
        ], $rows);
    }

}
