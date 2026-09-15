<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Abono {{ $sale->numero }}</title>
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
            height: 36px;
            display: block;
        }

        .header {
            text-align: center;
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 2px solid #14b8a6;
        }

        .header h1 {
            font-size: 17px;
            margin: 0;
            color: #222;
            letter-spacing: 0.5px;
        }

        .header p {
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
            width: 130px;
            color: #667;
            font-weight: bold;
        }

        .monto {
            text-align: center;
            margin: 18px 0;
            padding: 14px;
            background-color: #000;
            border: 2px solid #2dd4bf;
            border-radius: 8px;
        }

        .monto p {
            margin: 0;
            color: #fff;
        }

        .monto .etiqueta {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #2dd4bf;
            margin-bottom: 4px;
        }

        .monto .valor {
            font-size: 22px;
            font-weight: bold;
        }

        .firmas {
            margin-top: 50px;
        }

        .firma-linea {
            border-top: 1px solid #333;
            width: 90%;
            margin: 40px auto 4px auto;
        }

        .firma-label {
            text-align: center;
            font-size: 11px;
            color: #555;
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
        <h1>Comprobante de abono</h1>
        <p>Venta: {{ $sale->numero }}</p>
        @include('admin.settings.partials.pdf-contact-line')
    </div>

    <div class="details">
        <table>
            <tr>
                <td class="label">Paciente:</td>
                <td>{{ $sale->patient->person->name }} {{ $sale->patient->person->last_name_father }} {{ $sale->patient->person->last_name_mother }}</td>
            </tr>
            <tr>
                <td class="label">Doctor:</td>
                <td>{{ $sale->doctor->person->name }} {{ $sale->doctor->person->last_name_father }}</td>
            </tr>
            <tr>
                <td class="label">Fecha de pago:</td>
                <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td class="label">Método de pago:</td>
                <td>{{ $payment->payment_method }}</td>
            </tr>
        </table>
    </div>

    <div class="monto">
        <p class="etiqueta">Monto abonado</p>
        <p class="valor">Bs. {{ number_format($payment->amount, 2, '.', ',') }}</p>
    </div>

    <div class="details">
        <table>
            <tr>
                <td class="label">Total de la venta:</td>
                <td>Bs. {{ number_format($sale->total, 2, '.', ',') }}</td>
            </tr>
            <tr>
                <td class="label">Saldo pendiente:</td>
                <td>Bs. {{ number_format($saldoEnEseMomento, 2, '.', ',') }}</td>
            </tr>
        </table>
    </div>

    <div class="firmas">
        <div class="firma-linea"></div>
        <p class="firma-label">Firma del Doctor</p>

        <div class="firma-linea"></div>
        <p class="firma-label">Firma del Paciente</p>
    </div>

    <div class="footer">
        <p>Fecha de impresión: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

</body>
</html>
