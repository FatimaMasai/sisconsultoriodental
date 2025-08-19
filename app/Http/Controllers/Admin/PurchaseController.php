<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


use Barryvdh\DomPDF\Facade\Pdf as PDF; 
use NumberToWords\NumberToWords;

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

    DB::beginTransaction();

    try {
        $total = 0;

        // Calcular el total primero
        foreach ($request->products as $product) {
            $productDetails = Product::findOrFail($product['product_id']);
            $subtotal = $productDetails->price * $product['quantity'];
            $total += $subtotal;
        }

        // Validar monto pagado
        if ($request->amount != $total) {
            return redirect()->back()
                ->withErrors(['amount' => 'El monto pagado no coincide con el total de la compra.'])
                ->withInput();
        }

        // Crear compra
        $purchase = Purchase::create([
            'supplier_id' => $request->supplier_id,
            'date' => now(),
            'total' => $total,
            'status' => 1,
        ]);

        // Crear detalles
        foreach ($request->products as $product) {
            $productDetails = Product::findOrFail($product['product_id']);
            $subtotal = $productDetails->price * $product['quantity'];

            PurchaseDetail::create([
                'price' => $productDetails->price,
                'quantity' => $product['quantity'],
                'subtotal' => $subtotal,
                'purchase_id' => $purchase->id,
                'product_id' => $product['product_id'],
            ]);
        }

        // Registrar pago
        Payment::create([
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'payment_status' => $request->payment_status,
            'purchase_id' => $purchase->id,
        ]);

        DB::commit();

        session()->flash('swal', [
            'title' => 'Compra Registrada',
            'text' => 'La compra fue registrada exitosamente.',
            'icon' => 'success',
        ]);

        return redirect()->route('admin.purchases.index');

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()
            ->withErrors(['error' => 'Ocurrió un error al registrar la compra: ' . $e->getMessage()])
            ->withInput();
    }
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

    public function print($purchasePrint){

        //obtener la compra con sus detalles
        $purchase = Purchase::with('supplier', 'purchaseDetails.product')->findOrFail($purchasePrint);
        $payment = $purchase->payments->first(); // Obtener el primer pago asociado a la venta

           // Obtener el nombre del usuario logueado
        $user = auth()->user();  // Obtiene el usuario autenticado

        // Crear la instancia de NumberToWords
        $numberToWords = new NumberToWords();
        $numberTransformer = $numberToWords->getNumberTransformer('es');  // 'es' es el idioma español

        // Convertir el total a palabras
        $totalLiteral = ucwords(strtolower( $numberTransformer->toWords($purchase->total))) ;

        //generar el pdf a partir de la vista print
        $pdf = PDF::loadView('admin.purchases.print', compact('purchase','totalLiteral','user','payment'))
        ->setPaper([0, 0, 300, 600], 'portrait');   // Configurar el tamaño y la orientación del papel;

        //descargar pdf
        return $pdf->stream('comprante_venta_' . $purchase->id . '.pdf'); 
    }
}
