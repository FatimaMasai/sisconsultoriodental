<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuotas Pagadas</title>
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

    <h1>Reporte de Cuotas Pagadas</h1>
    <p class="subtitulo">
        Generado el {{ now()->format('d/m/Y H:i') }}
        @if ($request->filled('search'))
            &middot; Filtro: "{{ $request->search }}"
        @endif
        @if ($request->filled('date_from') || $request->filled('date_to'))
            &middot; Del {{ $request->date_from ? \Carbon\Carbon::parse($request->date_from)->format('d/m/Y') : '—' }}
            al {{ $request->date_to ? \Carbon\Carbon::parse($request->date_to)->format('d/m/Y') : '—' }}
        @endif
        @if ($request->filled('payment_method'))
            &middot; Método: {{ $request->payment_method }}
        @endif
    </p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Comprobante</th>
                <th>Paciente</th>
                <th>Cuota</th>
                <th>Monto</th>
                <th>Método</th>
                <th>Fecha de pago</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($installments as $index => $installment)
                @php
                    $sale = $installment->sale;
                    $paciente = trim($sale->patient->person->name . ' ' . $sale->patient->person->last_name_father . ' ' . $sale->patient->person->last_name_mother);
                    $metodoPago = optional($installment->payments->first())->payment_method ?? '—';
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $sale->numero }}</td>
                    <td>{{ $paciente }}</td>
                    <td>#{{ $installment->number }}</td>
                    <td>Bs. {{ number_format($installment->amount, 0, '', '.') }}</td>
                    <td>{{ $metodoPago }}</td>
                    <td>{{ $installment->paid_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">No se encontraron cuotas pagadas.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($installments->count())
            <tfoot>
                <tr>
                    <td colspan="4"></td>
                    <td>Bs. {{ number_format($totalCobrado, 0, '', '.') }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        @endif
    </table>

</body>
</html>
