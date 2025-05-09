<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function __construct()
    {
        $this->middleware('can:admin.product_categories.index')->only('index');
        $this->middleware('can:admin.product_categories.create')->only('create', 'store');
        $this->middleware('can:admin.product_categories.edit')->only('edit', 'update');
        $this->middleware('can:admin.product_categories.destroy')->only('destroy');
        $this->middleware('can:admin.product_categories.pdf')->only('pdf');
    } 
    public function index()
    {
        $product_categories = ProductCategory::where('status', 1)
        ->orderBy('id', 'desc')
        ->paginate(10);

        return view('admin.product_categories.index', compact('product_categories'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $product_categories = ProductCategory::all();
        return view('admin.product_categories.create', compact('product_categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required'
        ]);


        ProductCategory::create([
            'name' => ucwords(strtolower($request->name)),
        ]);
        

        session()->flash('swal', [
            'title' => 'Categoria Creada',
            'text' => '¡Bien Hecho!.',
            'icon' => 'success',
        ]);
        return redirect()->route('admin.product_categories.index');



    }

    /**
     * Display the specified resource.
     */
    public function show(ProductCategory $productCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductCategory $productCategory)
    {
        return view('admin.product_categories.edit', compact('productCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductCategory $productCategory)
    {
        $request->validate([
            'name' => 'required'
        ]);

        $productCategory->update([
            'name' => ucwords(strtolower($request->name)),
        ]);

        session()->flash('swal', [
            'title' => 'Categoria Actualizada',
            'text' => '¡Bien Hecho!.',
            'icon' => 'success',
        ]);
        return redirect()->route('admin.product_categories.index');
         
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductCategory $productCategory)
    {
        if($productCategory->products()->count() > 0)
        {
            session()->flash('swal', [
                'title' => 'Error',
                'text' => 'No se puede eliminar la categoria porque tiene productos asociados',
                'icon' => 'error',
            ]);
            return redirect()->route('admin.product_categories.index', $productCategory);
        }

        //no eliminamos sino damos de baja el registro
        $productCategory->update(['status' => 0]);

        session()->flash('swal', [
            'title' => 'Categoria Eliminada',
            'text' => '¡Bien Hecho!.',
            'icon' => 'success',
        ]);
        return redirect()->route('admin.product_categories.index', $productCategory);
    }

    public function pdf()
    {
         
    }
    
}
