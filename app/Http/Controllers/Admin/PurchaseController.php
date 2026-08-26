<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Supplier;
use App\Http\Controllers\Concerns\ExportsExcel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


use Barryvdh\DomPDF\Facade\Pdf as PDF;
use NumberToWords\NumberToWords;

class PurchaseController extends Controller
{
    use ExportsExcel;

    public function __construct()
    {
        $this->middleware('can:admin.purchases.index')->only('index');
        $this->middleware('can:admin.purchases.create')->only('create', 'store');
        $this->middleware('can:admin.purchases.edit')->only('edit', 'update');
        $this->middleware('can:admin.purchases.destroy')->only('destroy');
        $this->middleware('can:admin.purchases.pdf')->only('pdf', 'excel');
        $this->middleware('can:admin.purchases.cancel')->only('cancel');
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
        return $pdf->stream('comprobante_' . $purchase->numero . '.pdf');
    }

    /**
     * Consulta base para los reportes de compras (PDF/Excel): todas las
     * compras (activas o anuladas), igual que ya se muestran en el listado.
     */
    private function purchasesForExport()
    {
        return Purchase::with('supplier.person')->orderBy('id', 'desc')->get();
    }

    public function pdf()
    {
        $purchases = $this->purchasesForExport();

        $pdf = PDF::loadView('admin.purchases.pdf', compact('purchases'));

        return $pdf->stream('listado_compras.pdf');
    }

    public function excel()
    {
        $purchases = $this->purchasesForExport();

        $rows = $purchases->map(function (Purchase $purchase) {
            $contacto = trim($purchase->supplier->person->name . ' ' . $purchase->supplier->person->last_name_father);

            return [
                $this->formatDate($purchase->date),
                $purchase->supplier->company,
                $contacto,
                $purchase->supplier->nit,
                (float) $purchase->total,
                $purchase->status == 1 ? 'Activa' : 'Anulada',
            ];
        });

        return $this->streamExcel('compras_' . now()->format('Y-m-d') . '.xlsx', [
            'Fecha', 'Proveedor', 'Contacto', 'NIT', 'Total', 'Estado',
        ], $rows);
    }

    public function cancel(Purchase $purchase)
    {
        // Verificar si ya está anulada
        if ($purchase->status == 0) {
            return redirect()->back()->with('info', 'Esta compra ya está anulada.');
        }

        DB::beginTransaction();
        try {
            // Cambiar estado de la compra
            $purchase->update(['status' => 0]);

            // Cambiar estado de los detalles (igual que se hace con las ventas)
            foreach ($purchase->purchaseDetails as $detail) {
                $detail->update(['subtotal' => 0]);
            }

            // Cambiar estado de los pagos si existen
            foreach ($purchase->payments as $payment) {
                $payment->update([
                    'payment_status' => 'Anulado'
                ]);
            }

            $purchase->loadMissing('supplier');

            AuditLog::record(
                'purchase.cancelled',
                $purchase,
                "Anuló la compra a {$purchase->supplier->company} (total: {$purchase->total})",
                ['total' => (float) $purchase->total, 'supplier_id' => $purchase->supplier_id]
            );

            DB::commit();

            session()->flash('swal', [
                'title' => 'Compra anulada con éxito',
                'text' => 'La compra fue anulada correctamente.',
                'icon' => 'success'
            ]);

            return redirect()->route('admin.purchases.index');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors([
                'error' => 'Ocurrió un error al anular la compra: ' . $e->getMessage()
            ]);
        }
    }
}
