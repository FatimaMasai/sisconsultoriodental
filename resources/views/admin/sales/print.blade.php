<!-- resources/views/admin/sales/print.blade.php -->
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
        <p><strong>Paciente:</strong> {{ $sale->patient->person->name }} {{ $sale->patient->person->last_name_father }} {{ $sale->patient->person->last_name_mother }}</p>
        
        <!-- Datos adicionales del paciente -->
        <p><strong>Edad:</strong> {{ \Carbon\Carbon::parse($sale->patient->person->birth_date)->age }} años</p> <!-- Calcular edad -->
        <p><strong>Teléfono:</strong> {{ $sale->patient->person->phone }}</p> <!-- Teléfono -->
        <p><strong>Carnet:</strong> {{ $sale->patient->person->identity_card }}</p> <!-- Carnet -->

        <p><strong>Doctor:</strong> {{ $sale->doctor->person->name }} {{ $sale->doctor->person->last_name_father }} {{ $sale->doctor->person->last_name_mother }}</p>
        <p><strong>Especialidad:</strong> {{ $sale->doctor->speciality->name }} </p>
        <p><strong>Fecha de Venta:</strong> {{ $sale->sale_date }} </p>
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
        <p><strong>Importe Total: Bs. {{ $sale->total }}</strong></p> <!-- Total en números -->
        {{-- <p><strong>Son:</strong> {{ $totalLiteral }} 00/100 BOLIVIANOS</p>    --}}
    </div>


     


    <!-- Firma -->
    <div class="footer">
        <p><strong>Procesado por:</strong> {{ $user->name }}</p> 
        <p><strong>Fecha de Impresión:</strong> {{ now()->format('d/m/Y H:i:s') }} </p>

    </div>

</body>
</html>
