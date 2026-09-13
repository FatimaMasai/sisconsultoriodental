<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Abonos</title>
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

    @php $logoDataUri = \App\Models\ClinicSetting::instance()->logoBase64(); @endphp
    @if ($logoDataUri)
        <div style="text-align: center; margin-bottom: 10px;">
            <img src="{{ $logoDataUri }}" alt="Mi Consulta" style="height: 42px;">
        </div>
    @endif

    <h1>Reporte de Historial de Abonos</h1>
    @include('admin.settings.partials.pdf-contact-line')
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
                <th>Concepto</th>
                <th>Monto</th>
                <th>Método</th>
                <th>Fecha de pago</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($abonos as $index => $payment)
                @php
                    $sale = $payment->sale;
                    $paciente = trim($sale->patient->person->name . ' ' . $sale->patient->person->last_name_father . ' ' . $sale->patient->person->last_name_mother);
                    $concepto = $payment->payment_status === 'Cuota Inicial'
                        ? 'Cuota inicial'
                        : ($payment->installment ? 'Cuota #' . $payment->installment->number : $payment->payment_status);
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $sale->numero }}</td>
                    <td>{{ $paciente }}</td>
                    <td>{{ $concepto }}</td>
                    <td>Bs. {{ number_format($payment->amount, 0, '', '.') }}</td>
                    <td>{{ $payment->payment_method }}</td>
                    <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">No se encontraron abonos.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($abonos->count())
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
