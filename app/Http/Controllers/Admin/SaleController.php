<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Person;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Service;
use Illuminate\Http\Request;

use Barryvdh\DomPDF\Facade\Pdf as PDF; 
use NumberToWords\NumberToWords;


class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    

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
         
        $patients = Patient::with('person')->where('status',1)->get(); 
        $doctors = Doctor::with('person')->where('status',1)->get();

        $services = Service::where('status', 1)->get(); // Obtener servicios activos

        return view('admin.sales.create', compact('patients', 'services', 'doctors'));

    }

    /**
     * Store a newly created resource in storage.
     */
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
            'payment_status' => 'required|in:contado',

        ]);

        //crear venta
        $sale = Sale::create([
            'sale_date' => now(),
            'total' => 0,
            'status' => 1,

            'patient_id' => $request->patient_id,
            'doctor_id' => $request->doctor_id,
        ]);

        $total = 0;
        
        /// Crear los detalles de la venta
        foreach ($request->services as $service) {
            //obtener servicio y calcular subtotal
            $serviceDetails = Service::find($service['service_id']);
            $subtotal = $serviceDetails->price * $service['quantity'];

            // Crear el detalle de venta
            SaleDetail::create([
                'price' => $serviceDetails->price,
                'quantity' => $service['quantity'],
                'subtotal' => $subtotal,
                'sale_id' => $sale->id,
                'service_id' => $service['service_id'],
            ]);

            // Sumar al total
            $total += $subtotal;
        }

        // Verificar si el monto pagado es igual al total
    if ($request->amount != $total) {
        return redirect()->back()->withErrors(['amount' => 'El monto pagado no coincide con el total de la venta.']);
    }

        // Registrar el pago
        Payment::create([
            
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'payment_status' => $request->payment_status,
            'sale_id' => $sale->id,
        ]);


        // Actualizar el total de la venta
        $sale->update(['total' => $total]);

        session()->flash('swal', [
            'title' => 'Venta Realizada con éxito.',
            'text' => 'Bien hecho!',
            'icon' => 'success'
        ]);
 
        return redirect()->route('admin.sales.index');





    }

    /**
     * Display the specified resource.
     */
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
        $sale = Sale::with('patient', 'doctor', 'saleDetails.service', 'payments')->findOrFail($salePrint);

           // Obtener el nombre del usuario logueado
        $user = auth()->user();  // Obtiene el usuario autenticado

        // Crear la instancia de NumberToWords
        $numberToWords = new NumberToWords();
        $numberTransformer = $numberToWords->getNumberTransformer('es');  // 'es' es el idioma español

        // Convertir el total a palabras
        $totalLiteral = ucwords(strtolower( $numberTransformer->toWords($sale->total))) ;

        //generar el pdf a partir de la vista print
        $pdf = PDF::loadView('admin.sales.print', compact('sale','totalLiteral','user'))
        ->setPaper([0, 0, 300, 600], 'portrait');   // Configurar el tamaño y la orientación del papel;

        //descargar pdf
        return $pdf->stream('comprante_venta_' . $sale->id . '.pdf'); 
    }
}
