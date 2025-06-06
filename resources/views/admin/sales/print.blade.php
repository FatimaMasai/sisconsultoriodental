<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Venta #{{ $sale->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 18px;
            margin: 0;
        }

        .header p {
            font-size: 14px;
            margin: 5px 0;
        }

        .details {
            margin-top: 20px;
        }

        .details p {
            font-size: 12px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th, .table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }

        .total {
            text-align: right;
            font-weight: bold;
            margin-top: 20px;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
        }
    </style>
</head>
<body>

    <!-- Encabezado -->
    <div class="header">
        <h1>COMPROBANTE DE VENTA</h1>
        <p>Venta: # {{ $sale->id }}  </p>
    </div>

    <!-- Datos del paciente -->
    <div class="details">

        <!-- Datos del paciente -->
        <p style="margin: 0; padding: 0;"><strong>Paciente:</strong> {{ $sale->patient->person->name }} {{ $sale->patient->person->last_name_father }} {{ $sale->patient->person->last_name_mother }}</p>       
        <p style="margin: 0; padding: 0;"><strong>Edad:</strong> {{ \Carbon\Carbon::parse($sale->patient->person->birth_date)->age }} años</p> <!-- Calcular edad -->
        <p style="margin: 0; padding: 0;"><strong>Teléfono:</strong> {{ $sale->patient->person->phone }}</p> <!-- Teléfono -->
        <p style="margin: 0; padding: 0;"><strong>Carnet:</strong> {{ $sale->patient->person->identity_card }}</p> <!-- Carnet -->
        
        <hr>
    
        <!-- Datos Doctor -->
        <p style="margin: 0; padding: 0;"><strong>Doctor:</strong> {{ $sale->doctor->person->name }} {{ $sale->doctor->person->last_name_father }} {{ $sale->doctor->person->last_name_mother }}</p>
        <p style="margin: 0; padding: 0;"><strong>Especialidad:</strong> {{ $sale->doctor->speciality->name }} </p>
    
        <hr>
    
        <!-- Datos pagos -->
        <p style="margin: 0; padding: 0;"><strong>Fecha de Venta:</strong> {{ $sale->sale_date }} </p>
        <p style="margin: 0; padding: 0;"><strong>Método de pago:</strong> {{ $payment->payment_method }} </p>
        <p style="margin: 0; padding: 0;"><strong>Forma de pago:</strong> {{ $payment->payment_status }} </p>
    </div>
    

    <!-- Detalle de los servicios -->
    <table class="table">
        <thead>
            <tr>
                <th>NRO</th> 
                <th>DETALLE</th>
                <th>CANT.</th>
                <th>P.UNIT</th>
                <th>SUBTOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sale->saleDetails as $index => $detail)
                <tr>
                    <td>{{ $index + 1 }}</td> 
                    <td>{{ $detail->service->name }}</td>
                    <td>{{ $detail->quantity }}</td>
                    <td>{{ $detail->price }}</td>
                    <td>{{ $detail->subtotal }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Total de la venta -->
    <div class="total">
        <p style="margin: 0; padding: 0;"><strong>Importe Total: Bs. {{ $sale->total }}</strong></p> <!-- Total en números -->
        {{-- <p><strong>Son:</strong> {{ $totalLiteral }} 00/100 BOLIVIANOS</p>    --}}
    </div>


     


    <!-- Firma -->
   <br> 
    <div class="footer">
        <p style="margin: 0; padding: 0;"><strong>Procesado por:</strong> {{ $user->name }}</p> 
        <p style="margin: 0; padding: 0;"><strong>Fecha de Impresión:</strong> {{ now()->format('d/m/Y H:i:s') }} </p>

    </div>

</body>
</html>
