<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuotas Pagadas - {{ $sale->numero }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        h1 {
            text-align: center;
            margin-bottom: 4px;
        }
        p.subtitulo {
            text-align: center;
            color: #555;
            margin-top: 0;
            margin-bottom: 20px;
        }
        .info-box {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 20px;
        }
        .info-box p {
            margin: 4px 0;
        }
        .label {
            color: #666;
        }
        tfoot td {
            font-weight: bold;
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>

    @if (function_exists('imagecreatefrompng') && file_exists(public_path('images/logo.png')))
        <div style="text-align: center; margin-bottom: 10px;">
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/logo.png'))) }}" alt="Mi Consulta" style="height: 42px;">
        </div>
    @endif

    <h1>Comprobante de Cuotas Pagadas</h1>
    <p class="subtitulo">Venta {{ $sale->numero }} &middot; Generado el {{ now()->format('d/m/Y H:i') }}</p>

    <div class="info-box">
        <p><span class="label">Paciente:</span> {{ trim($sale->patient->person->name . ' ' . $sale->patient->person->last_name_father . ' ' . $sale->patient->person->last_name_mother) }}</p>
        <p><span class="label">Doctor:</span> {{ trim($sale->doctor->person->name . ' ' . $sale->doctor->person->last_name_father) }}</p>
        <p><span class="label">Fecha de venta:</span> {{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') }}</p>
        <p><span class="label">Total de la venta:</span> Bs. {{ number_format($sale->total, 0, '', '.') }}</p>
        <p><span class="label">Saldo pendiente:</span> Bs. {{ number_format($sale->saldo_pendiente, 0, '', '.') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Concepto</th>
                <th>Monto</th>
                <th>Método</th>
                <th>Fecha de pago</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pagos as $index => $pago)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        @if ($pago->payment_status === 'Cuota Inicial')
                            Cuota inicial
                        @elseif ($pago->installment)
                            Cuota #{{ $pago->installment->number }}
                        @else
                            {{ $pago->payment_status }}
                        @endif
                    </td>
                    <td>Bs. {{ number_format($pago->amount, 0, '', '.') }}</td>
                    <td>{{ $pago->payment_method }}</td>
                    <td>{{ $pago->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">Todavía no se registró ningún pago.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($pagos->count())
            <tfoot>
                <tr>
                    <td colspan="2">Total pagado</td>
                    <td>Bs. {{ number_format($pagos->sum('amount'), 0, '', '.') }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        @endif
    </table>

</body>
</html>
