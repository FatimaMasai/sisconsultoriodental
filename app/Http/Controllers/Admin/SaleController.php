<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\History;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Barryvdh\DomPDF\Facade\Pdf as PDF; 
use NumberToWords\NumberToWords;


class SaleController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('can:admin.sales.index')->only('index');
        $this->middleware('can:admin.sales.create')->only('create', 'store');
        $this->middleware('can:admin.sales.edit')->only('edit', 'update');
        $this->middleware('can:admin.sales.pdf')->only('pdf');
    }
    

    public function index()
    {
        // Obtener todas las ventas junto con la relación con pacientes, servicios, médicos y pagos.
        $sales = Sale::where('status',1)
        ->orderBy('id', 'DESC') // Ordenar las ventas por la fecha de la venta
        ->paginate(50); // Paginación para limitar los resultados

    // Pasar las ventas a la vista
    return view('admin.sales.index', compact('sales'));

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
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',            
            'payment_status' => 'required|in:Contado',
        ], [
            'patient_id.required' => 'El campo paciente es obligatorio.',
            'doctor_id.required' => 'El campo doctor es obligatorio.',
            'services.required' => 'Debe agregar al menos un servicio.',
            'services.*.service_id.required' => 'Debe seleccionar un servicio.',
            'services.*.quantity.required' => 'Debe ingresar la cantidad del servicio.',
        ]);

        DB::beginTransaction();
        try {
            //crear venta
            $sale = Sale::create([
                'sale_date' => now(),
                'total' => 0,
                'status' => 1,
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

            // validar monto antes de registrar el pago
            if ($request->amount != $total) {
                DB::rollBack(); // <- cancelar todo lo anterior
                return redirect()->back()->withErrors([
                    'amount' => 'El monto pagado no coincide con el total de la venta.'
                ])->withInput();
            }

            // pago
            Payment::create([
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_status,
                'sale_id' => $sale->id,
            ]);

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
        //
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
        return $pdf->stream('comprante_venta_' . $sale->id . '.pdf'); 
    }
}