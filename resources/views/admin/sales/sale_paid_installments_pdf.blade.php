<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Abonos - {{ $sale->numero }}</title>
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

        .details {
            background-color: #f8fafa;
            border: 1px solid #e2e8e8;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 18px;
        }

        .details table {
            width: 100%;
            border-collapse: collapse;
        }

        .details td {
            padding: 3px 0;
            font-size: 12.5px;
            line-height: 1.5;
        }

        .details td.label {
            width: 140px;
            color: #667;
            font-weight: bold;
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
        <h1>Comprobante de historial de abonos</h1>
        <p class="subtitle">Venta {{ $sale->numero }} &middot; Generado el {{ now()->format('d/m/Y H:i') }}</p>
        @include('admin.settings.partials.pdf-contact-line')
    </div>

    <div class="details">
        <table>
            <tr>
                <td class="label">Paciente:</td>
                <td>{{ trim($sale->patient->person->name . ' ' . $sale->patient->person->last_name_father . ' ' . $sale->patient->person->last_name_mother) }}</td>
            </tr>
            <tr>
                <td class="label">Doctor:</td>
                <td>{{ trim($sale->doctor->person->name . ' ' . $sale->doctor->person->last_name_father) }}</td>
            </tr>
            <tr>
                <td class="label">Fecha de venta:</td>
                <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="label">Total de la venta:</td>
                <td>Bs. {{ number_format($sale->total, 0, '', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Saldo pendiente:</td>
                <td>Bs. {{ number_format($sale->saldo_pendiente, 0, '', '.') }}</td>
            </tr>
        </table>
    </div>

    <table class="listado">
        <thead>
            <tr>
                <th>#</th>
                <th>Concepto</th>
                <th style="text-align: right;">Monto</th>
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
                    <td class="precio">Bs. {{ number_format($pago->amount, 0, '', '.') }}</td>
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
                    <td class="precio">Bs. {{ number_format($pagos->sum('amount'), 0, '', '.') }}</td>
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
