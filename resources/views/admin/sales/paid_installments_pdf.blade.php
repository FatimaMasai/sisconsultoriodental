<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Abonos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .logo-band {
            background-color: #000;
            padding: 10px 0;
            margin: 0 0 18px 0;
            border-radius: 8px;
            text-align: center;
        }

        .logo-band .logo-badge {
            display: inline-block;
            background-color: #000;
            border: 2px solid #2dd4bf;
            border-radius: 6px;
            padding: 6px 14px;
        }

        .logo-band img {
            height: 42px;
            display: block;
        }

        .header {
            text-align: center;
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 2px solid #14b8a6;
        }

        .header h1 {
            font-size: 19px;
            margin: 0;
            color: #222;
            letter-spacing: 0.5px;
        }

        .header p.subtitle {
            font-size: 12px;
            margin: 4px 0 0 0;
            color: #14b8a6;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table.listado {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        table.listado th, table.listado td {
            border: 1px solid #dcdfe0;
            padding: 7px 8px;
            font-size: 12px;
            text-align: left;
        }

        table.listado th {
            background-color: #f0f4f3;
            color: #333;
            text-transform: uppercase;
            font-size: 10.5px;
            letter-spacing: 0.3px;
        }

        table.listado td.precio {
            text-align: right;
            white-space: nowrap;
        }

        table.listado tfoot td {
            font-weight: bold;
            background-color: #f0f4f3;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10.5px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 8px;
        }
    </style>
</head>
<body>

    @php $logoDataUri = \App\Models\ClinicSetting::instance()->logoBase64(); @endphp
    @if ($logoDataUri)
        <div class="logo-band">
            <span class="logo-badge">
                <img src="{{ $logoDataUri }}" alt="Logo">
            </span>
        </div>
    @endif

    <div class="header">
        <h1>Reporte de historial de abonos</h1>
        <p class="subtitle">
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
        @include('admin.settings.partials.pdf-contact-line')
    </div>

    <table class="listado">
        <thead>
            <tr>
                <th>#</th>
                <th>Comprobante</th>
                <th>Paciente</th>
                <th>Concepto</th>
                <th style="text-align: right;">Monto</th>
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
                    <td class="precio">Bs. {{ number_format($payment->amount, 0, '', '.') }}</td>
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
                    <td class="precio">Bs. {{ number_format($totalCobrado, 0, '', '.') }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="footer">
        <p>Fecha de impresión: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

</body>
</html>
