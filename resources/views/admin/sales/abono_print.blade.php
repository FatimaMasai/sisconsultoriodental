<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Abono {{ $sale->numero }}</title>
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
            font-size: 16px;
            margin: 0;
        }

        .header p {
            font-size: 12px;
            margin: 4px 0;
        }

        .details p {
            font-size: 12px;
            margin: 0;
            padding: 2px 0;
        }

        .monto {
            text-align: center;
            margin: 18px 0;
            padding: 10px;
            border: 1px dashed #333;
        }

        .monto p {
            margin: 0;
        }

        .monto .valor {
            font-size: 20px;
            font-weight: bold;
        }

        .firmas {
            margin-top: 50px;
        }

        .firma-linea {
            border-top: 1px solid #000;
            width: 90%;
            margin: 40px auto 4px auto;
        }

        .firma-label {
            text-align: center;
            font-size: 11px;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #555;
        }
    </style>
</head>
<body>

    <!-- Encabezado -->
    <div class="header">
        @php $logoDataUri = \App\Models\ClinicSetting::instance()->logoBase64(); @endphp
        @if ($logoDataUri)
            <img src="{{ $logoDataUri }}" alt="Logo" style="height: 36px; margin-bottom: 8px;">
        @endif
        <h1>COMPROBANTE DE ABONO</h1>
        <p>Venta: {{ $sale->numero }}</p>
        @include('admin.settings.partials.pdf-contact-line')
    </div>

    <!-- Datos -->
    <div class="details">
        <p><strong>Paciente:</strong> {{ $sale->patient->person->name }} {{ $sale->patient->person->last_name_father }} {{ $sale->patient->person->last_name_mother }}</p>
        <p><strong>Doctor:</strong> {{ $sale->doctor->person->name }} {{ $sale->doctor->person->last_name_father }}</p>
        <p><strong>Fecha de pago:</strong> {{ $payment->created_at->format('d/m/Y H:i') }}</p>
        <p><strong>Método de pago:</strong> {{ $payment->payment_method }}</p>
    </div>

    <!-- Monto del abono -->
    <div class="monto">
        <p>Monto abonado</p>
        <p class="valor">Bs. {{ number_format($payment->amount, 2, '.', ',') }}</p>
    </div>

    <div class="details">
        <p><strong>Total de la venta:</strong> Bs. {{ number_format($sale->total, 2, '.', ',') }}</p>
        <p><strong>Saldo pendiente:</strong> Bs. {{ number_format($saldoEnEseMomento, 2, '.', ',') }}</p>
    </div>

    <!-- Firmas -->
    <div class="firmas">
        <div class="firma-linea"></div>
        <p class="firma-label">Firma del Doctor</p>

        <div class="firma-linea"></div>
        <p class="firma-label">Firma del Paciente</p>
    </div>

    <div class="footer">
        <p>Impreso el {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

</body>
</html>
