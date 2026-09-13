<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Consulta;
use App\Models\Doctor;
use App\Models\Expediente;
use App\Models\Installment;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Service;
use App\Models\ToothTreatment;
use App\Http\Controllers\Concerns\ExportsExcel;
use App\Services\VeriPagosService;
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
        $this->middleware('can:admin.sales.create')->only('create', 'store', 'pendingCharges');
        $this->middleware('can:admin.sales.edit')->only('edit', 'update');
        $this->middleware('can:admin.sales.pdf')->only('pdf', 'excel');
        $this->middleware('can:admin.sales.cancel')->only('cancel');
        $this->middleware('can:admin.sales.payInstallment')->only('payInstallment', 'addAbono');
        $this->middleware('can:admin.sales.index')->only('paidInstallments', 'paidInstallmentsExcel', 'paidInstallmentsPdf', 'salePaidInstallmentsPdf', 'printAbono');
        $this->middleware('can:admin.sales.print')->only('print');
        $this->middleware('can:admin.sales.destroy')->only('destroy');

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
     * Consulta base del reporte de "Historial de Abonos": todos los pagos de
     * ventas a Crédito (abonos libres del sistema nuevo + cuota inicial/cuotas
     * del sistema viejo, para no perder el histórico), con los mismos filtros
     * (paciente/comprobante, rango de fechas) reutilizados por la pantalla y
     * por las exportaciones, igual que filteredSales()/excel().
     *
     * Antes esto leía de la tabla Installment (plan de cuotas fijas). Desde
     * que el Crédito pasó a ser abono libre, las ventas nuevas ya no generan
     * cuotas, así que esa consulta se quedaba siempre en 0 para todo lo
     * nuevo. Ahora se lee directo de Payment, que es donde de verdad quedan
     * registrados tanto los abonos nuevos como las cuotas viejas.
     */
    private function filteredAbonos(Request $request)
    {
        $query = Payment::query()
            ->whereIn('payment_status', ['Cuota Inicial', 'Cuota', 'Abono'])
            ->whereHas('sale', fn ($s) => $s->where('payment_type', 'Credito'))
            ->with(['sale.patient.person', 'sale.doctor.person', 'installment']);

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
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        return $query;
    }

    /**
     * Reporte de todos los abonos registrados en ventas a Crédito (abonos
     * libres nuevos + cuota inicial/cuotas del sistema viejo).
     */
    public function paidInstallments(Request $request)
    {
        $baseQuery = $this->filteredAbonos($request);

        $hasFilters = $request->filled('search') || $request->filled('date_from') || $request->filled('date_to') || $request->filled('payment_method');

        // Totales sobre TODO lo filtrado, no solo la página actual (se calculan
        // antes de paginar, con un clone, para no consumir el query builder).
        $totalCobrado = (clone $baseQuery)->sum('amount');
        $totalAbonos = (clone $baseQuery)->count();

        $abonos = $baseQuery->orderBy('created_at', 'desc')
            ->paginate(50)
            ->withQueryString();

        return view('admin.sales.paid_installments', compact('abonos', 'hasFilters', 'totalCobrado', 'totalAbonos'));
    }

    /**
     * Exporta a Excel el historial de abonos, respetando los mismos filtros
     * que estén aplicados en la pantalla.
     */
    public function paidInstallmentsExcel(Request $request)
    {
        $abonos = $this->filteredAbonos($request)->orderBy('created_at', 'desc')->get();

        $rows = $abonos->map(function (Payment $payment) {
            $sale = $payment->sale;
            $paciente = trim($sale->patient->person->name . ' ' . $sale->patient->person->last_name_father . ' ' . $sale->patient->person->last_name_mother);
            $concepto = $payment->payment_status === 'Cuota Inicial'
                ? 'Cuota inicial'
                : ($payment->installment ? 'Cuota #' . $payment->installment->number : $payment->payment_status);

            return [
                $sale->numero,
                $paciente,
                $concepto,
                (float) $payment->amount,
                $payment->payment_method,
                $this->formatDate($payment->created_at, 'd/m/Y H:i'),
            ];
        });

        return $this->streamExcel('historial_abonos_' . now()->format('Y-m-d') . '.xlsx', [
            'Comprobante', 'Paciente', 'Concepto', 'Monto', 'Método de pago', 'Fecha de pago',
        ], $rows);
    }

    /**
     * Exporta a PDF el historial de abonos, respetando los mismos filtros
     * que estén aplicados en la pantalla.
     */
    public function paidInstallmentsPdf(Request $request)
    {
        $abonos = $this->filteredAbonos($request)->orderBy('created_at', 'desc')->get();

        $totalCobrado = $abonos->sum('amount');

        $pdf = PDF::loadView('admin.sales.paid_installments_pdf', compact('abonos', 'totalCobrado', 'request'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('historial_abonos_' . now()->format('Y-m-d') . '.pdf');
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
    public function create(Request $request)
    {

        $patients = Patient::with('person')->where('status',1)->orderBy('id', 'desc')->get();
        $doctors = Doctor::with('person')->where('status',1)->orderBy('id', 'desc')->get();

        $services = Service::where('status', 1)->orderBy('id', 'desc')->get(); // Obtener servicios activos

        // Si se llega desde el Odontograma con "Cobrar pendientes", acá se
        // recuperan esos tratamientos (siempre del mismo paciente que llega
        // en la URL, por las dudas) solo para mostrarlos como referencia y
        // sugerir el total a cobrar; el que carga los Servicios y confirma
        // la venta sigue siendo quien está en caja, como siempre.
        $pendingToothTreatmentIds = array_filter(explode(',', (string) $request->query('tooth_treatments', '')));

        $pendingToothTreatments = empty($pendingToothTreatmentIds)
            ? collect()
            : ToothTreatment::whereIn('id', $pendingToothTreatmentIds)
                ->whereNull('sale_id')
                ->whereHas('expediente', fn ($q) => $q->where('patient_id', $request->query('patient_id')))
                ->get();

        return view('admin.sales.create', compact('patients', 'services', 'doctors', 'pendingToothTreatments'));

    }

    /**
     * Lista, para quien esté en caja (Recepción o el propio doctor), todos
     * los pacientes que tienen tratamientos del Odontograma sin cobrar
     * todavía, sin depender de que el doctor le mande el link desde el
     * Odontograma de un paciente puntual. Cada grupo lleva directo a
     * "Nueva venta" con ese paciente y esos tratamientos ya armados, igual
     * que el botón "Cobrar pendientes" del Odontograma.
     */
    public function pendingCharges()
    {
        $pendientesPorPaciente = ToothTreatment::whereNull('sale_id')
            ->with('expediente.patient.person')
            ->get()
            ->filter(fn ($treatment) => $treatment->expediente?->patient !== null)
            ->groupBy(fn ($treatment) => $treatment->expediente->patient_id)
            ->map(fn ($treatments) => [
                'patient' => $treatments->first()->expediente->patient,
                'treatments' => $treatments,
                'total' => $treatments->sum('price'),
            ])
            ->sortByDesc(fn ($grupo) => $grupo['treatments']->max('date'));

        return view('admin.sales.pending_charges', compact('pendientesPorPaciente'));
    }

    public function store(Request $request, VeriPagosService $veripagos)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'services' => 'required|array',
            'services.*.service_id' => 'required|exists:services,id',
            'services.*.quantity' => 'required|integer|min:1',
            // Precio editable por línea: si se deja vacío, se usa el precio
            // de lista del Servicio (ver el foreach más abajo). Sirve para
            // casos puntuales que cuestan distinto al precio de catálogo.
            'services.*.price' => 'nullable|numeric|min:0',

            // Descuento fijo en Bs. sobre el total de la venta (opcional).
            'discount' => 'nullable|numeric|min:0',

            'payment_type' => 'required|in:Contado,Credito',

            // Venta al Contado: se paga el total completo.
            // Venta a Credito: "amount" es opcional y es el abono que el paciente deja
            // hoy mismo (puede ser 0 si todavía no paga nada) - el resto se va abonando
            // libremente más adelante desde el detalle de la venta, sin plan de cuotas fijo.
            'amount' => 'required_if:payment_type,Contado|nullable|numeric|min:0',

            // Cobro por QR (VeriPagos): lo llena el modal cuando confirma el pago.
            'qr_movimiento_id' => 'nullable|string',

            // Si esta venta viene de "Cobrar pendientes" en el Odontograma,
            // acá llegan los ids de los tratamientos que se están cobrando
            // (ver create() y el bloque más abajo que los marca como
            // cobrados una vez creada la venta).
            'tooth_treatment_ids' => 'nullable|array',
            'tooth_treatment_ids.*' => 'exists:tooth_treatments,id',
        ], [
            'patient_id.required' => 'El campo paciente es obligatorio.',
            'doctor_id.required' => 'El campo doctor es obligatorio.',
            'services.required' => 'Debe agregar al menos un servicio.',
            'services.*.service_id.required' => 'Debe seleccionar un servicio.',
            'services.*.quantity.required' => 'Debe ingresar la cantidad del servicio.',
            'payment_type.required' => 'Debe seleccionar el tipo de venta (Contado o Crédito).',
            'amount.required_if' => 'Debe ingresar el monto pagado.',
        ]);

        $paymentType = $request->payment_type;

        $needsPaymentMethod = $paymentType === 'Contado'
            || ($paymentType === 'Credito' && (float) ($request->amount ?? 0) > 0);

        if ($needsPaymentMethod && ! $request->filled('payment_method')) {
            return redirect()->back()->withErrors([
                'payment_method' => 'Debe seleccionar el método de pago.',
            ])->withInput();
        }

        // "QR" es por ahora una opción más de método de pago, como Efectivo o
        // Transferencia: no dispara ninguna verificación contra la API de
        // VeriPagos. $qrVerificado se deja siempre en null a propósito, para
        // que el resto del método (que ya sabe ignorar el cobro por QR cuando
        // esto es null) siga funcionando sin cambios y sea fácil reactivar la
        // verificación real más adelante si se resuelve la integración.
        $qrVerificado = null;

        DB::beginTransaction();
        try {
            //crear venta
            $sale = Sale::create([
                'sale_date' => now(),
                'total' => 0,
                'status' => 1,
                'payment_type' => $paymentType,
                'initial_amount' => 0,
                'patient_id' => $request->patient_id,
                'doctor_id' => $request->doctor_id,
            ]);

            $subtotalVenta = 0;
            $serviceNames = [];

            foreach ($request->services as $service) {
                $serviceDetails = Service::findOrFail($service['service_id']);

                // Si vino un precio editado desde el formulario, se usa ese
                // (para casos puntuales que cuestan distinto); si no, el
                // precio de lista del Servicio, como siempre.
                $price = isset($service['price']) && $service['price'] !== ''
                    ? (float) $service['price']
                    : (float) $serviceDetails->price;

                $subtotal = $price * $service['quantity'];

                // detalle venta
                SaleDetail::create([
                    'price' => $price,
                    'quantity' => $service['quantity'],
                    'subtotal' => $subtotal,
                    'sale_id' => $sale->id,
                    'service_id' => $service['service_id'],
                ]);

                $serviceNames[] = $serviceDetails->name;
                $subtotalVenta += $subtotal;
            }

            // Descuento fijo en Bs. sobre el subtotal de los servicios. No
            // puede superar el subtotal (una venta no puede terminar en
            // negativo).
            $discount = round((float) ($request->discount ?? 0), 2);

            if ($discount > $subtotalVenta) {
                DB::rollBack();

                return redirect()->back()->withErrors([
                    'discount' => 'El descuento (Bs. ' . number_format($discount, 2) . ') no puede ser mayor al subtotal de la venta (Bs. ' . number_format($subtotalVenta, 2) . ').',
                ])->withInput();
            }

            $total = round($subtotalVenta - $discount, 2);

            // Historial clínico: toda venta representa una visita del paciente,
            // así que se registra como UNA sola Consulta dentro de su expediente
            // de la especialidad del doctor. Antes se creaba un historial por
            // cada servicio vendido (se repetía si vendías varios juntos), y
            // las visitas de solo "Consulta" ni siquiera quedaban registradas;
            // ahora toda venta deja rastro en el expediente, incluida esa.
            $doctor = Doctor::findOrFail($request->doctor_id);

            if ($doctor->speciality_id) {
                $expediente = Expediente::firstOrCreate(
                    ['patient_id' => $request->patient_id, 'speciality_id' => $doctor->speciality_id],
                    ['status' => true]
                );

                Consulta::create([
                    'expediente_id' => $expediente->id,
                    'doctor_id' => $doctor->id,
                    'sale_id' => $sale->id,
                    'description' => implode(', ', $serviceNames),
                    'date' => now(),
                ]);
            }

            // Si esta venta viene de "Cobrar pendientes" en el Odontograma,
            // se marcan esos tratamientos como cobrados con esta venta. Se
            // filtra de nuevo por paciente (whereHas) para que nadie pueda
            // colar, a mano en el formulario, el id de un tratamiento de
            // otro paciente.
            if ($request->filled('tooth_treatment_ids')) {
                ToothTreatment::whereIn('id', $request->tooth_treatment_ids)
                    ->whereNull('sale_id')
                    ->whereHas('expediente', fn ($q) => $q->where('patient_id', $request->patient_id))
                    ->update(['sale_id' => $sale->id]);
            }

            if ($paymentType === 'Contado') {
                // validar monto antes de registrar el pago
                if ((float) $request->amount != (float) $total) {
                    DB::rollBack(); // <- cancelar todo lo anterior
                    return redirect()->back()->withErrors([
                        'amount' => 'El monto pagado no coincide con el total de la venta.'
                    ])->withInput();
                }

                // Si fue pago por QR, el monto realmente pagado (según VeriPagos) también
                // debe coincidir con el total, no solo lo que envió el formulario.
                if ($qrVerificado && round((float) ($qrVerificado['monto'] ?? 0), 2) != round((float) $total, 2)) {
                    DB::rollBack();
                    return redirect()->back()->withErrors([
                        'amount' => 'El monto pagado por QR no coincide con el total de la venta.'
                    ])->withInput();
                }

                // pago
                Payment::create([
                    'amount' => $request->amount,
                    'payment_method' => $request->payment_method,
                    'payment_status' => 'Contado',
                    'sale_id' => $sale->id,
                    'qr_movimiento_id' => $qrVerificado ? $request->qr_movimiento_id : null,
                    'qr_remitente' => $qrVerificado['remitente'] ?? null,
                ]);
            } else {
                // Venta a Credito: queda el total como saldo pendiente y de ahí en más se
                // va abonando libremente (cualquier monto, cualquier fecha, sin plan de
                // cuotas fijo) desde el detalle de la venta - ver SaleController::addAbono().
                // Opcionalmente, si el paciente deja algo de una vez al momento de la venta,
                // ese primer abono se registra ahora mismo con lo que vino en "amount".
                $abonoInicial = round((float) ($request->amount ?? 0), 2);

                if ($abonoInicial > $total) {
                    DB::rollBack();
                    return redirect()->back()->withErrors([
                        'amount' => 'El abono no puede ser mayor al total de la venta.'
                    ])->withInput();
                }

                // Si el abono de hoy se hizo por QR, el monto realmente pagado debe coincidir.
                if ($qrVerificado && round((float) ($qrVerificado['monto'] ?? 0), 2) != $abonoInicial) {
                    DB::rollBack();
                    return redirect()->back()->withErrors([
                        'payment_method' => 'El monto pagado por QR no coincide con el abono ingresado.'
                    ])->withInput();
                }

                if ($abonoInicial > 0) {
                    Payment::create([
                        'amount' => $abonoInicial,
                        'payment_method' => $request->payment_method,
                        'payment_status' => 'Abono',
                        'sale_id' => $sale->id,
                        'qr_movimiento_id' => $qrVerificado ? $request->qr_movimiento_id : null,
                        'qr_remitente' => $qrVerificado['remitente'] ?? null,
                    ]);
                }
            }

            // actualizar total (ya con el descuento restado) y guardar el
            // descuento aplicado para poder mostrarlo en el comprobante
            $sale->update(['total' => $total, 'discount' => $discount]);

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
    public function payInstallment(Request $request, Sale $sale, Installment $installment, VeriPagosService $veripagos)
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
            'qr_movimiento_id' => 'nullable|string',
        ], [
            'payment_method.required' => 'Debe seleccionar el método de pago.',
        ]);

        // "QR" es por ahora una opción más de método de pago, como Efectivo o
        // Transferencia: no dispara ninguna verificación contra la API de
        // VeriPagos. $qrVerificado se deja siempre en null a propósito, para
        // que el resto del método siga funcionando sin cambios y sea fácil
        // reactivar la verificación real más adelante si se resuelve la
        // integración.
        $qrVerificado = null;

        DB::beginTransaction();
        try {
            Payment::create([
                'amount' => $installment->amount,
                'payment_method' => $request->payment_method,
                'payment_status' => 'Cuota',
                'sale_id' => $sale->id,
                'installment_id' => $installment->id,
                'qr_movimiento_id' => $qrVerificado ? $request->qr_movimiento_id : null,
                'qr_remitente' => $qrVerificado['remitente'] ?? null,
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

    /**
     * Registrar un abono libre (cualquier monto, cualquier fecha) de una
     * venta a Credito. Reemplaza al viejo plan de cuotas fijas: no hay
     * número de cuota ni fecha de vencimiento, solo se va descontando del
     * saldo pendiente cada vez que el paciente deja algo a cuenta.
     */
    public function addAbono(Request $request, Sale $sale)
    {
        if (! $sale->isCredito()) {
            return redirect()->back()->with('info', 'Esta venta no es a crédito, no se pueden registrar abonos.');
        }

        if ($sale->status == 0) {
            return redirect()->back()->with('info', 'Esta venta está anulada, no se pueden registrar pagos.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'qr_movimiento_id' => 'nullable|string',
        ], [
            'amount.required' => 'Debe ingresar el monto del abono.',
            'amount.min' => 'El abono debe ser mayor a 0.',
            'payment_method.required' => 'Debe seleccionar el método de pago.',
        ]);

        $monto = round((float) $request->amount, 2);
        $saldoPendiente = (float) $sale->saldo_pendiente;

        // Pequeño margen (1 centavo) para tolerar errores de redondeo, no para
        // permitir de verdad un sobrepago.
        if ($monto > $saldoPendiente + 0.01) {
            return redirect()->back()->withErrors([
                'amount' => 'El abono (Bs. ' . number_format($monto, 2) . ') no puede ser mayor al saldo pendiente (Bs. ' . number_format($saldoPendiente, 2) . ').',
            ])->withInput();
        }

        // "QR" es por ahora una opción más de método de pago, como Efectivo o
        // Transferencia: no dispara ninguna verificación contra la API de
        // VeriPagos (ver la misma nota en store()).
        $qrVerificado = null;

        Payment::create([
            'amount' => $monto,
            'payment_method' => $request->payment_method,
            'payment_status' => 'Abono',
            'sale_id' => $sale->id,
            'qr_movimiento_id' => $qrVerificado ? $request->qr_movimiento_id : null,
            'qr_remitente' => $qrVerificado['remitente'] ?? null,
        ]);

        session()->flash('swal', [
            'title' => 'Abono registrado',
            'text' => 'Se registró el abono correctamente.',
            'icon' => 'success',
        ]);

        return redirect()->route('admin.sales.show', $sale);
    }

    /**
     * Comprobante imprimible de UN abono puntual, con espacio para que el
     * doctor y el paciente firmen a mano al momento de imprimirlo (se saca
     * por duplicado: una copia para cada uno).
     */
    public function printAbono(Sale $sale, Payment $payment)
    {
        abort_unless($payment->sale_id === $sale->id, 404);

        $sale->load('patient.person', 'doctor.person');

        // Saldo pendiente justo después de este abono (no el saldo actual de
        // la venta, que puede tener abonos posteriores): total menos todo lo
        // pagado hasta este pago inclusive.
        $pagadoHastaEsteAbono = $sale->payments()
            ->where('payment_status', '!=', 'Anulado')
            ->where('created_at', '<=', $payment->created_at)
            ->sum('amount');

        $saldoEnEseMomento = round((float) $sale->total - (float) $pagadoHastaEsteAbono, 2);

        $pdf = PDF::loadView('admin.sales.abono_print', compact('sale', 'payment', 'saldoEnEseMomento'))
            ->setPaper([0, 0, 300, 600], 'portrait');

        return $pdf->stream('abono_' . $sale->numero . '_' . $payment->id . '.pdf');
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
