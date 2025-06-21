<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Venta #{{ $purchase->id }}</title>
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
        <h1>COMPROBANTE DE COMPRA</h1>
        <p>Compra: # {{ $purchase->id }}  </p>
    </div>

    <!-- Datos del Proveedor -->
    <div class="details">

        <!-- Datos del Proveedor -->
        <p style="margin: 0; padding: 0;"><strong>Proveedor:</strong> {{ $purchase->supplier->person->name }} {{ $purchase->supplier->person->last_name_father }} {{ $purchase->supplier->person->last_name_mother }}</p>       
        <p style="margin: 0; padding: 0;"><strong>Edad:</strong> {{ \Carbon\Carbon::parse($purchase->supplier->person->birth_date)->age }} años</p> <!-- Calcular edad -->
        <p style="margin: 0; padding: 0;"><strong>Teléfono:</strong> {{ $purchase->supplier->person->phone }}</p> <!-- Teléfono -->
        <p style="margin: 0; padding: 0;"><strong>Carnet:</strong> {{ $purchase->supplier->person->identity_card }}</p> <!-- Carnet -->
        
        <hr>
    
        <!-- Datos Doctor -->
        <p style="margin: 0; padding: 0;"><strong>Empresa:</strong> {{ $purchase->supplier->company}} </p>
        <p style="margin: 0; padding: 0;"><strong>Nit:</strong> {{ $purchase->supplier->nit}} </p>
    
        <hr>
    
        <!-- Datos pagos -->
        <p style="margin: 0; padding: 0;"><strong>Fecha de Venta:</strong> {{ $purchase->date }} </p>
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
            @foreach ($purchase->purchaseDetails as $index => $detail)
                <tr>
                    <td>{{ $index + 1 }}</td> 
                    <td>{{ $detail->product->name }}</td>
                    <td>{{ $detail->quantity }}</td>
                    <td>{{ $detail->price }}</td>
                    <td>{{ $detail->subtotal }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Total de la venta -->
    <div class="total">
        <p style="margin: 0; padding: 0;"><strong>Importe Total: Bs. {{ $purchase->total }}</strong></p> <!-- Total en números -->
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
