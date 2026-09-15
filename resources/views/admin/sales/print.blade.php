<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Venta {{ $sale->numero }}</title>
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
            width: 110px;
            color: #667;
            font-weight: bold;
        }

        .details hr {
            border: none;
            border-top: 1px solid #e2e8e8;
            margin: 6px 0;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        table.items th, table.items td {
            border: 1px solid #dcdfe0;
            padding: 7px 8px;
            font-size: 12px;
            text-align: left;
        }

        table.items th {
            background-color: #f0f4f3;
            color: #333;
            text-transform: uppercase;
            font-size: 10.5px;
            letter-spacing: 0.3px;
        }

        table.items td.precio {
            text-align: right;
            white-space: nowrap;
        }

        .totales {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .totales td {
            padding: 4px 8px;
            font-size: 12.5px;
        }

        .totales td.label {
            color: #555;
            text-align: right;
        }

        .totales td.valor {
            text-align: right;
            font-weight: bold;
            width: 140px;
            white-space: nowrap;
        }

        .totales tr.total-final td {
            border-top: 2px solid #33463f;
            padding-top: 8px;
            font-size: 15px;
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
        <h1>Comprobante de venta</h1>
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
                <td class="label">Edad:</td>
                <td>{{ \Carbon\Carbon::parse($sale->patient->person->birth_date)->age }} años</td>
            </tr>
            <tr>
                <td class="label">Teléfono:</td>
                <td>{{ $sale->patient->person->phone }}</td>
            </tr>
            <tr>
                <td class="label">Carnet:</td>
                <td>{{ $sale->patient->person->identity_card }}</td>
            </tr>
        </table>
        <hr>
        <table>
            <tr>
                <td class="label">Doctor:</td>
                <td>{{ $sale->doctor->person->name }} {{ $sale->doctor->person->last_name_father }} {{ $sale->doctor->person->last_name_mother }}</td>
            </tr>
            <tr>
                <td class="label">Especialidad:</td>
                <td>{{ $sale->doctor->speciality->name }}</td>
            </tr>
        </table>
        <hr>
        <table>
            <tr>
                <td class="label">Fecha de venta:</td>
                <td>{{ $sale->sale_date }}</td>
            </tr>
            <tr>
                <td class="label">Método de pago:</td>
                <td>{{ $payment->payment_method }}</td>
            </tr>
            <tr>
                <td class="label">Forma de pago:</td>
                <td>{{ $payment->payment_status }}</td>
            </tr>
        </table>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Nro</th>
                <th>Detalle</th>
                <th>Cant.</th>
                <th style="text-align: right;">P. unit.</th>
                <th style="text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sale->saleDetails as $index => $detail)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $detail->service->name }}</td>
                    <td>{{ $detail->quantity }}</td>
                    <td class="precio">{{ $detail->price }}</td>
                    <td class="precio">{{ $detail->subtotal }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totales">
        @if ($sale->discount > 0)
            <tr>
                <td class="label">Subtotal</td>
                <td class="valor">Bs. {{ number_format($sale->subtotal, 0, '', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Descuento</td>
                <td class="valor">- Bs. {{ number_format($sale->discount, 0, '', '.') }}</td>
            </tr>
        @endif
        <tr class="total-final">
            <td class="label">Importe total</td>
            <td class="valor">Bs. {{ number_format($sale->total, 0, '', '.') }}</td>
        </tr>
    </table>

    <div class="footer">
        <p>Procesado por: {{ $user->name }}</p>
        <p>Fecha de impresión: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

</body>
</html>
