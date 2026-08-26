<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Doctor;
use App\Models\History;
use App\Models\Installment;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Service;
use App\Http\Controllers\Concerns\ExportsExcel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Barryvdh\DomPDF\Facade\Pdf as PDF;
use NumberToWords\NumberToWords;


class SaleController extends Controller
{
    use ExportsExcel;

    public function __construct()
    {
        $this->middleware('can:admin.sales.index')->only('index', 'show');
        $this->middleware('can:admin.sales.create')->only('create', 'store');
        $this->middleware('can:admin.sales.edit')->only('edit', 'update');
        $this->middleware('can:admin.sales.pdf')->only('pdf', 'excel');
        $this->middleware('can:admin.sales.cancel')->only('cancel');
        $this->middleware('can:admin.sales.payInstallment')->only('payInstallment');
        $this->middleware('can:admin.sales.index')->only('paidInstallments', 'paidInstallmentsExcel', 'paidInstallmentsPdf', 'salePaidInstallmentsPdf');

    }


    public function index(Request $request)
    {
        $query = $this->filteredSales($request);

        $hasFilters = $request->filled('search') || $request->filled('payment_type')
            || $request->filled('date_from') || $request->filled('date_to');

        $sales = $query->orderBy('id', 'DESC') // Ordenar las ventas por id
            ->paginate(50) // Paginación para limitar los resultados
            ->withQueryString(); // conservar los filtros al cambiar de página

        // Pasar las ventas a la vista
        return view('admin.sales.index', compact('sales', 'hasFilters'));
    }

    /**
     * Consulta de ventas (activas o anuladas) con los mismos filtros de
     * búsqueda que usa index(). Reutilizada también por excel() para que
     * la exportación respete los filtros aplicados en el listado.
     */
    private function filteredSales(Request $request)
    {
        $query = Sale::with(['patient.person', 'doctor.person', 'installments']);

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

        return $query;
    }

    /**
     * Exporta a Excel el listado de ventas, respetando los mismos filtros
     * (búsqueda, tipo de pago, rango de fechas) que estén aplicados en el
     * listado. Incluye el estado de crédito, algo que el PDF de recibo
     * individual no muestra.
     */
    public function excel(Request $request)
    {
        $sales = $this->filteredSales($request)->orderBy('id', 'desc')->get();

        $rows = $sales->map(function (Sale $sale) {
            $paciente = trim($sale->patient->person->name . ' ' . $sale->patient->person->last_name_father . ' ' . $sale->patient->person->last_name_mother);
            $doctor = trim($sale->doctor->person->name . ' ' . $sale->doctor->person->last_name_father);

            return [
                $sale->numero,
                $this->formatDate($sale->sale_date),
                $paciente,
                $doctor,
                $sale->payment_type,
                (float) $sale->total,
                (float) $sale->initial_amount,
                (float) $sale->saldo_pendiente,
                $sale->estado_credito ?? '—',
                $sale->status == 1 ? 'Activa' : 'Anulada',
            ];
        });

        return $this->streamExcel('ventas_' . now()->format('Y-m-d') . '.xlsx', [
            'Comprobante', 'Fecha', 'Paciente', 'Doctor', 'Tipo de pago',
            'Total', 'Cuota inicial', 'Saldo pendiente', 'Estado de crédito', 'Estado',
        ], $rows);
    }

    /**
     * Consulta base del reporte de cuotas pagadas, con los mismos filtros
     * (paciente/comprobante, rango de fechas) reutilizados por la pantalla
     * y por la exportación a Excel, igual que filteredSales()/excel().
     */
    private function filteredPaidInstallments(Request $request)
    {
        $query = Installment::query()
            ->where('status', 'Pagada')
            ->with(['sale.patient.person', 'sale.doctor.person', 'payments']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $searchId = preg_replace('/^v-?/i', '', $search);

            $query->where(function ($q) use ($search, $searchId) {
                if (is_numeric($searchId)) {
                    $q->orWhereHas('sale', fn ($s) => $s->where('id', (int) $searchId));
                }

                $q->orWhereHas('sale.patient.person', function ($personQuery) use ($search) {
                    $personQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('last_name_father', 'like', "%{$search}%")
                        ->orWhere('last_name_mother', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('paid_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('paid_at', '<=', $request->date_to);
        }

        if ($request->filled('payment_method')) {
            $query->whereHas('payments', function ($q) use ($request) {
                $q->where('payment_method', $request->payment_method);
            });
        }

        return $query;
    }

    /**
     * Reporte de todas las cuotas pagadas, de cualquier venta a Crédito.
     */
    public function paidInstallments(Request $request)
    {
        $baseQuery = $this->filteredPaidInstallments($request);

        $hasFilters = $request->filled('search') || $request->filled('date_from') || $request->filled('date_to') || $request->filled('payment_method');

        // Totales sobre TODO lo filtrado, no solo la página actual (se calculan
        // antes de paginar, con un clone, para no consumir el query builder).
        $totalCobrado = (clone $baseQuery)->sum('amount');
        $totalCuotas = (clone $baseQuery)->count();

        $installments = $baseQuery->orderBy('paid_at', 'desc')
            ->paginate(50)
            ->withQueryString();

        return view('admin.sales.paid_installments', compact('installments', 'hasFilters', 'totalCobrado', 'totalCuotas'));
    }

    /**
     * Exporta a Excel el reporte de cuotas pagadas, respetando los mismos
     * filtros que estén aplicados en la pantalla.
     */
    public function paidInstallmentsExcel(Request $request)
    {
        $installments = $this->filteredPaidInstallments($request)->orderBy('paid_at', 'desc')->get();

        $rows = $installments->map(function (Installment $installment) {
            $sale = $installment->sale;
            $paciente = trim($sale->patient->person->name . ' ' . $sale->patient->person->last_name_father . ' ' . $sale->patient->person->last_name_mother);
            $metodo = optional($installment->payments->first())->payment_method ?? '—';

            return [
                $sale->numero,
                $paciente,
                $installment->number,
                (float) $installment->amount,
                $metodo,
                $this->formatDate($installment->paid_at, 'd/m/Y H:i'),
            ];
        });

        return $this->streamExcel('cuotas_pagadas_' . now()->format('Y-m-d') . '.xlsx', [
            'Comprobante', 'Paciente', 'N° Cuota', 'Monto', 'Método de pago', 'Fecha de pago',
        ], $rows);
    }

    /**
     * Exporta a PDF el reporte de cuotas pagadas, respetando los mismos
     * filtros que estén aplicados en la pantalla.
     */
    public function paidInstallmentsPdf(Request $request)
    {
        $installments = $this->filteredPaidInstallments($request)->orderBy('paid_at', 'desc')->get();

        $totalCobrado = $installments->sum('amount');

        $pdf = PDF::loadView('admin.sales.paid_installments_pdf', compact('installments', 'totalCobrado', 'request'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('cuotas_pagadas_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Reporte en PDF de las cuotas pagadas de UNA venta a Crédito en
     * particular, pensado para entregárselo al paciente (por ejemplo por
     * WhatsApp) como comprobante de cuánto lleva pagado.
     */
    public function salePaidInstallmentsPdf(Sale $sale)
    {
        $sale->load(['patient.person', 'doctor.person', 'payments.installment']);

        $pagos = $sale->payments->where('payment_status', '!=', 'Anulado')->sortBy('created_at')->values();

        $pdf = PDF::loadView('admin.sales.sale_paid_installments_pdf', compact('sale', 'pagos'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('cuotas_pagadas_' . $sale->numero . '.pdf');
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

        // La Consulta se cobra siempre de inmediato (aunque el resto de la venta sea a
        // crédito), así que si está entre los servicios vendidos también exige método de pago.
        $serviceIds = collect($request->services)->pluck('service_id');
        $hasConsulta = Service::whereIn('id', $serviceIds)->get()
            ->contains(fn ($s) => trim(strtolower($s->name)) === 'consulta');

        $needsPaymentMethod = $paymentType === 'Contado'
            || ($paymentType === 'Credito' && ((float) $request->initial_amount > 0 || $hasConsulta));

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
            $consultaTotal = 0; // suma de los subtotales del servicio "Consulta" en esta venta

            foreach ($request->services as $service) {
                $serviceDetails = Service::findOrFail($service['service_id']);
                $subtotal = $serviceDetails->price * $service['quantity'];
                $esConsulta = trim(strtolower($serviceDetails->name)) === 'consulta';

                // detalle venta
                SaleDetail::create([
                    'price' => $serviceDetails->price,
                    'quantity' => $service['quantity'],
                    'subtotal' => $subtotal,
                    'sale_id' => $sale->id,
                    'service_id' => $service['service_id'],
                ]);

                // historial: no aplica para "Consulta" (no implica tratamiento ni seguimiento)
                if (! $esConsulta) {
                    History::create([
                        'description' => 'Venta de servicio',
                        'date' => now(),
                        'patient_id' => $request->patient_id,
                        'doctor_id' => $request->doctor_id,
                        'service_id' => $service['service_id'],
                    ]);
                } else {
                    $consultaTotal += $subtotal;
                }

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

                // La Consulta se paga siempre de inmediato, sin importar que el resto de
                // la venta sea a crédito, así que no entra en el monto a financiar.
                $totalFinanciable = round($total - $consultaTotal, 2);

                if ($totalFinanciable > 0 && $initialAmount >= $totalFinanciable) {
                    DB::rollBack();
                    return redirect()->back()->withErrors([
                        'initial_amount' => 'La cuota inicial debe ser menor al monto financiable (sin contar la Consulta).'
                    ])->withInput();
                }

                // cobro automático de la Consulta (se cobra sí o sí, de una vez)
                if ($consultaTotal > 0) {
                    Payment::create([
                        'amount' => $consultaTotal,
                        'payment_method' => $request->payment_method,
                        'payment_status' => 'Consulta',
                        'sale_id' => $sale->id,
                    ]);
                }

                // pago de la cuota inicial (si el paciente aportó algo por adelantado sobre lo financiable)
                if ($initialAmount > 0) {
                    Payment::create([
                        'amount' => $initialAmount,
                        'payment_method' => $request->payment_method,
                        'payment_status' => 'Cuota Inicial',
                        'sale_id' => $sale->id,
                    ]);
                }

                // generar el plan de cuotas mensuales sobre el saldo restante (sin contar la Consulta)
                // (el cálculo de montos vive en Installment::planAmounts() y
                // tiene sus propios tests unitarios, ver tests/Unit)
                $saldoFinanciado = round($totalFinanciable - $initialAmount, 2);

                if ($saldoFinanciado > 0 && $installmentsCount > 0) {
                    $montos = Installment::planAmounts($saldoFinanciado, $installmentsCount);

                    foreach ($montos as $index => $monto) {
                        $i = $index + 1;

                        Installment::create([
                            'number' => $i,
                            'due_date' => Carbon::parse($sale->sale_date)->addMonthsNoOverflow($i),
                            'amount' => $monto,
                            'status' => 'Pendiente',
                            'sale_id' => $sale->id,
                        ]);
                    }
                }
            }

            // actualizar total
            $sale->update(['total' => $total]);

            DB::commit();

            session()->flash('swal', [
                'title' => 'El pago se realizó con éxito.',
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

            $sale->loadMissing('patient.person');
            $paciente = trim($sale->patient->person->name . ' ' . $sale->patient->person->last_name_father);

            AuditLog::record(
                'sale.cancelled',
                $sale,
                "Anuló la venta {$sale->numero} (paciente: {$paciente}, total: {$sale->total})",
                ['total' => (float) $sale->total, 'patient_id' => $sale->patient_id]
            );

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
