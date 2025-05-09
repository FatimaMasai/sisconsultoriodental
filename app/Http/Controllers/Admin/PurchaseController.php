<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Supplier;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('can:admin.purchases.index')->only('index');
        $this->middleware('can:admin.purchases.create')->only('create', 'store');
        $this->middleware('can:admin.purchases.edit')->only('edit', 'update');
        $this->middleware('can:admin.purchases.destroy')->only('destroy');
        $this->middleware('can:admin.purchases.pdf')->only('pdf');
    }


    public function index()
    {
        $purchases = Purchase::with('supplier')->orderBy('id', 'desc')->paginate(10);
        return view('admin.purchases.index', compact('purchases'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $suppliers = Supplier::with('person')->where('status', 1)->get();
        $products = Product::where('status', 1)->get();
        return view('admin.purchases.create', compact('suppliers', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([ 
            'supplier_id' => 'required|exists:suppliers,id',

            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',            
            'payment_status' => 'required|in:Contado',
        ]);


        //crear compra
        $purchase = Purchase::create([
            'supplier_id' => $request->supplier_id,
            
            'date' => now(),
            'total' => 0,
            'status' => 1, 

        ]);


        $total = 0;

        /// Crear los detalles de la venta
        foreach ($request->products as $product) {
            //obtener servicio y calcular subtotal
            $productDetails = Product::find($product['product_id']);
            $subtotal = $productDetails->price * $product['quantity'];

            // Crear el detalle de venta
            PurchaseDetail::create([
                'price' => $productDetails->price,
                'quantity' => $product['quantity'],
                'subtotal' => $subtotal,
                'purchase_id' => $purchase->id,
                'product_id' => $product['product_id'],
            ]);
 


            // Sumar al total
            $total += $subtotal;
        }
        

        // Verificar si el monto pagado es igual al total
        if ($request->amount != $total) {
            return redirect()->back()->withErrors(['amount' => 'El monto pagado no coincide con el total de la compra.']);
        }

            // Registrar el pago
            Payment::create([
                
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_status,
                'purchase_id' => $purchase->id,
            ]);


            // Actualizar el total de la venta
            $purchase->update(['total' => $total]);
        


        session()->flash('swal', [
            'title' => 'Compra Registrada',
            'text' => 'La compra fue registrada exitosamente.',
            'icon' => 'success',
        ]);
    
        return redirect()->route('admin.purchases.index'); 

    }

    /**
     * Display the specified resource.
     */
    public function show(Purchase $purchase)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Purchase $purchase)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Purchase $purchase)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Purchase $purchase)
    {
        //
    }

    public function pdf()
    {
         
    }
}
