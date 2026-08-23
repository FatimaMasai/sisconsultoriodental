<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\History;
use App\Models\Installment;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Barryvdh\DomPDF\Facade\Pdf as PDF;
use NumberToWords\NumberToWords;


class SaleController extends Controller
{

    public function __construct()
    {
        $this->middleware('can:admin.sales.index')->only('index', 'show');
        $this->middleware('can:admin.sales.create')->only('create', 'store');
        $this->middleware('can:admin.sales.edit')->only('edit', 'update');
        $this->middleware('can:admin.sales.pdf')->only('pdf');
        $this->middleware('can:admin.sales.cancel')->only('cancel');
        $this->middleware('can:admin.sales.payInstallment')->only('payInstallment');

    }


    public function index(Request $request)
    {
        // Obtener todas las ventas (activas o anuladas), con filtros de búsqueda opcionales
        $query = Sale::with(['patient.person', 'doctor.person', 'installments']);

        $hasFilters = $request->filled('search') || $request->filled('payment_type')
            || $request->filled('date_from') || $request->filled('date_to');

        if ($request->filled('search')) {
            $search = trim($request->search);
            // Permite buscar por el número de comprobante (ej: "V-0007", "v-7") o por el id crudo.
            $searchId = preg_replace('/^v-?/i', '', $search);

            $query->where(function ($q) use ($search, $searchId) {
                if (is_numeric($searchId)) {
                    $q->where('id', (int) $searchId);
                }

                $q->orWhereHas('patient.person', function ($personQuery) use ($search) {
                    $personQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('last_name_father', 'like', "%{$search}%")
                        ->orWhere('last_name_mother', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('sale_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('sale_date', '<=', $request->date_to);
        }

        $sales = $query->orderBy('id', 'DESC') // Ordenar las ventas por id
            ->paginate(50) // Paginación para limitar los resultados
            ->withQueryString(); // conservar los filtros al cambiar de página

        // Pasar las ventas a la vista
        return view('admin.sales.index', compact('sales', 'hasFilters'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $patients = Patient::with('person')->where('status',1)->orderBy('id', 'desc')->get();
        $doctors = Doctor::with('person')->where('status',1)->orderBy('id', 'desc')->get();

        $services = Service::where('status', 1)->orderBy('id', 'desc')->get(); // Obtener servicios activos

        return view('admin.sales.create', compact('patients', 'services', 'doctors'));

    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'services' => 'required|array',
            'services.*.service_id' => 'required|exists:services,id',
            'services.*.quantity' => 'required|integer|min:1',

            'payment_type' => 'required|in:Contado,Credito',

            // Venta al Contado
            'amount' => 'required_if:payment_type,Contado|nullable|numeric|min:0',

            // Venta a Credito (cuotas)
            'initial_amount' => 'required_if:payment_type,Credito|nullable|numeric|min:0',
            'installments_count' => 'required_if:payment_type,Credito|nullable|integer|min:1|max:36',
        ], [
            'patient_id.required' => 'El campo paciente es obligatorio.',
            'doctor_id.required' => 'El campo doctor es obligatorio.',
            'services.required' => 'Debe agregar al menos un servicio.',
            'services.*.service_id.required' => 'Debe seleccionar un servicio.',
            'services.*.quantity.required' => 'Debe ingresar la cantidad del servicio.',
            'payment_type.required' => 'Debe seleccionar el tipo de venta (Contado o Crédito).',
            'amount.required_if' => 'Debe ingresar el monto pagado.',
            'initial_amount.required_if' => 'Debe ingresar el monto de la cuota inicial (puede ser 0).',
            'installments_count.required_if' => 'Debe indicar en cuántas cuotas se financiará el saldo.',
        ]);

        $paymentType = $request->payment_type;
        $needsPaymentMethod = $paymentType === 'Contado'
            || ($paymentType === 'Credito' && (float) $request->initial_amount > 0);

        if ($needsPaymentMethod && ! $request->filled('payment_method')) {
            return redirect()->back()->withErrors([
                'payment_method' => 'Debe seleccionar el método de pago.',
            ])->withInput();
        }

        DB::beginTransaction();
        try {
            //crear venta
            $sale = Sale::create([
                'sale_date' => now(),
                'total' => 0,
                'status' => 1,
                'payment_type' => $paymentType,
                'initial_amount' => $paymentType === 'Credito' ? (float) $request->initial_amount : 0,
                'patient_id' => $request->patient_id,
                'doctor_id' => $request->doctor_id,
            ]);

            $total = 0;

            foreach ($request->services as $service) {
                $serviceDetails = Service::findOrFail($service['service_id']);
                $subtotal = $serviceDetails->price * $service['quantity'];

                // detalle venta
                SaleDetail::create([
                    'price' => $serviceDetails->price,
                    'quantity' => $service['quantity'],
                    'subtotal' => $subtotal,
                    'sale_id' => $sale->id,
                    'service_id' => $service['service_id'],
                ]);

                // historial
                History::create([
                    'description' => 'Venta de servicio',
                    'date' => now(),
                    'patient_id' => $request->patient_id,
                    'doctor_id' => $request->doctor_id,
                    'service_id' => $service['service_id'],
                ]);

                $total += $subtotal;
            }

            if ($paymentType === 'Contado') {
                // validar monto antes de registrar el pago
                if ((float) $request->amount != (float) $total) {
                    DB::rollBack(); // <- cancelar todo lo anterior
                    return redirect()->back()->withErrors([
                        'amount' => 'El monto pagado no coincide con el total de la venta.'
                    ])->withInput();
                }

                // pago
                Payment::create([
                    'amount' => $request->amount,
                    'payment_method' => $request->payment_method,
                    'payment_status' => 'Contado',
                    'sale_id' => $sale->id,
                ]);
            } else {
                // Venta a Credito: cuota inicial + cuotas mensuales sobre el saldo
                $initialAmount = (float) $request->initial_amount;
                $installmentsCount = (int) $request->installments_count;

                if ($initialAmount >= $total) {
                    DB::rollBack();
                    return redirect()->back()->withErrors([
                        'initial_amount' => 'La cuota inicial debe ser menor al total de la venta.'
                    ])->withInput();
                }

                // pago de la cuota inicial (si el paciente aportó algo por adelantado)
                if ($initialAmount > 0) {
                    Payment::create([
                        'amount' => $initialAmount,
                        'payment_method' => $request->payment_method,
                        'payment_status' => 'Cuota Inicial',
                        'sale_id' => $sale->id,
                    ]);
                }

                // generar el plan de cuotas mensuales sobre el saldo restante
                $saldoFinanciado = round($total - $initialAmount, 2);
                $montoBase = round($saldoFinanciado / $installmentsCount, 2);
                $acumulado = 0;

                for ($i = 1; $i <= $installmentsCount; $i++) {
                    // la última cuota absorbe el ajuste de redondeo para que la suma cuadre exacto
                    $monto = $i < $installmentsCount
                        ? $montoBase
                        : round($saldoFinanciado - $acumulado, 2);

                    $acumulado += $monto;

                    Installment::create([
                        'number' => $i,
                        'due_date' => Carbon::parse($sale->sale_date)->addMonthsNoOverflow($i),
                        'amount' => $monto,
                        'status' => 'Pendiente',
                        'sale_id' => $sale->id,
                    ]);
                }
            }

            // actualizar total
            $sale->update(['total' => $total]);

            DB::commit();

            session()->flash('swal', [
                'title' => 'Venta Realizada con éxito.',
                'text' => 'Bien hecho!',
                'icon' => 'success'
            ]);

            return redirect()->route('admin.sales.index');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors([
                'error' => 'Ocurrió un error al registrar la venta: ' . $e->getMessage()
            ])->withInput();
        }
    }


    public function show(Sale $sale)
    {
        $sale->load(['patient.person', 'doctor.person', 'saleDetails.service', 'installments', 'payments.installment']);

        return view('admin.sales.show', compact('sale'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sale $sale)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sale $sale)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale)
    {
        //
    }

    /**
     * Registrar el pago de una cuota de una venta a Credito.
     */
    public function payInstallment(Request $request, Sale $sale, Installment $installment)
    {
        abort_unless($installment->sale_id === $sale->id, 404);

        if ($sale->status == 0) {
            return redirect()->back()->with('info', 'Esta venta está anulada, no se pueden registrar pagos.');
        }

        if ($installment->status === 'Pagada') {
            return redirect()->back()->with('info', 'Esta cuota ya fue pagada.');
        }

        $request->validate([
            'payment_method' => 'required|string',
        ], [
            'payment_method.required' => 'Debe seleccionar el método de pago.',
        ]);

        DB::beginTransaction();
        try {
            Payment::create([
                'amount' => $installment->amount,
                'payment_method' => $request->payment_method,
                'payment_status' => 'Cuota',
                'sale_id' => $sale->id,
                'installment_id' => $installment->id,
            ]);

            $installment->update([
                'status' => 'Pagada',
                'paid_at' => now(),
            ]);

            DB::commit();

            session()->flash('swal', [
                'title' => 'Cuota #' . $installment->number . ' pagada',
                'text' => 'El pago se registró correctamente.',
                'icon' => 'success'
            ]);

            return redirect()->route('admin.sales.show', $sale);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors([
                'error' => 'Ocurrió un error al registrar el pago: ' . $e->getMessage()
            ]);
        }
    }

    // Función para convertir números en palabras
    public function numberToWords($number)
    {
        $formatter = new \NumberFormatter("es", \NumberFormatter::SPELLOUT);
        return $formatter->format($number);
    }



    public function print($salePrint){

        //obtener la venta con sus detalles
        $sale = Sale::with('patient', 'doctor', 'saleDetails.service')->findOrFail($salePrint);
        $payment = $sale->payments->first(); // Obtener el primer pago asociado a la venta

           // Obtener el nombre del usuario logueado
        $user = auth()->user();  // Obtiene el usuario autenticado

        // Crear la instancia de NumberToWords
        $numberToWords = new NumberToWords();
        $numberTransformer = $numberToWords->getNumberTransformer('es');  // 'es' es el idioma español

        // Convertir el total a palabras
        $totalLiteral = ucwords(strtolower( $numberTransformer->toWords($sale->total))) ;

        //generar el pdf a partir de la vista print
        $pdf = PDF::loadView('admin.sales.print', compact('sale','totalLiteral','user','payment'))
        ->setPaper([0, 0, 300, 600], 'portrait');   // Configurar el tamaño y la orientación del papel;

        //descargar pdf
        return $pdf->stream('comprobante_' . $sale->numero . '.pdf');
    }


    public function cancel(Sale $sale)
    {
        // Verificar si ya está anulada
        if ($sale->status == 0) {
            return redirect()->back()->with('info', 'Esta venta ya está anulada.');
        }

        DB::beginTransaction();
        try {
            // Cambiar estado de la venta
            $sale->update(['status' => 0]);

            // Cambiar estado de los detalles si quieres (opcional)
            foreach ($sale->saleDetails as $detail) {
                $detail->update(['subtotal' => 0]);
            }

            // Cambiar estado de los pagos si existen
            foreach ($sale->payments as $payment) {
                $payment->update([
                    'payment_status' => 'Anulado'
                ]);
            }

            // Anular también las cuotas que aún estaban pendientes
            foreach ($sale->installments as $installment) {
                if ($installment->status !== 'Pagada') {
                    $installment->update(['status' => 'Anulada']);
                }
            }

            DB::commit();

            session()->flash('swal', [
                'title' => 'Venta anulada con éxito',
                'text' => 'La venta fue anulada correctamente.',
                'icon' => 'success'
            ]);

            return redirect()->route('admin.sales.index');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors([
                'error' => 'Ocurrió un error al anular la venta: ' . $e->getMessage()
            ]);
        }
    }



}
