<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::where('status', 1)
        ->orderBy('id', 'desc')
        ->paginate(10);


        return view('admin.products.index', compact('products'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $productCategories = ProductCategory::all();

        return view('admin.products.create', compact('productCategories'));
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

        Product::create([
            'name' => ucwords(strtolower($request->name)),
            'price' => $request->price,
            'status' => 1, // "Alta"
            'product_category_id' => $request->product_category_id,
        ]);
        session()->flash('swal', [
            'title' => 'Producto Creado',
            'text' => '¡Bien Hecho!.',
            'icon' => 'success',
        ]);
        return redirect()->route('admin.products.index');


    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $productCategories = ProductCategory::all();

        return view('admin.products.edit', compact('product', 'productCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([

            'name' => 'required',
            'price' => 'required|numeric',

            'product_category_id' => 'required|exists:product_categories,id'

        ]);

        $product->update([
            'name' => ucwords(strtolower($request->name)),
            'price' => $request->price,
            'status' => 1, // "Alta"
            'product_category_id' => $request->product_category_id,
        ]);
        session()->flash('swal', [
            'title' => 'Producto Actualizado',
            'text' => '¡Bien Hecho!.',
            'icon' => 'success',
        ]);
        return redirect()->route('admin.products.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->update([
            'status' => 0, // "Baja"
        ]);

        session()->flash('swal', [
            'title' => 'Producto Eliminado',
            'text' => '¡Bien Hecho!.',
            'icon' => 'success',
        ]);
        return redirect()->route('admin.products.index');
    }
}
