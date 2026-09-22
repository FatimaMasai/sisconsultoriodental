<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plan de tratamiento</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 18px;
            padding-bottom: 14px;
            border-bottom: 2px solid #14b8a6;
        }

        {{-- Misma banda con el logo que se usa en la receta médica, para
             que todas las PDF del sistema compartan el mismo look. --}}
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
            width: 90px;
            color: #667;
            font-weight: bold;
        }

        table.tratamientos {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        table.tratamientos th, table.tratamientos td {
            border: 1px solid #dcdfe0;
            padding: 7px 8px;
            font-size: 12px;
            text-align: left;
        }

        table.tratamientos th {
            background-color: #f0f4f3;
            color: #333;
            text-transform: uppercase;
            font-size: 10.5px;
            letter-spacing: 0.3px;
        }

        table.tratamientos td.precio {
            text-align: right;
            white-space: nowrap;
        }

        .totales {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .totales td {
            padding: 6px 8px;
            font-size: 13px;
        }

        .totales td.label {
            color: #555;
        }

        .totales td.valor {
            text-align: right;
            font-weight: bold;
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
        <h1>Plan de tratamiento</h1>
        <p>{{ $expediente->speciality->name ?? 'Especialidad' }}</p>
        @include('admin.settings.partials.pdf-contact-line')
    </div>

    <div class="details">
        <table>
            <tr>
                <td class="label">Paciente:</td>
                <td>
                    {{ $expediente->patient->person->name }}
                    {{ $expediente->patient->person->last_name_father }}
                    {{ $expediente->patient->person->last_name_mother }}
                </td>
            </tr>
            @if ($doctor)
                <tr>
                    <td class="label">Doctor:</td>
                    <td>{{ $doctor->person->name ?? '—' }} {{ $doctor->person->last_name_father ?? '' }}</td>
                </tr>
            @endif
            <tr>
                <td class="label">Fecha:</td>
                <td>{{ now()->format('d/m/Y') }}</td>
            </tr>
        </table>
    </div>

    @if ($treatments->isEmpty())
        <p style="text-align: center; color: #777; margin: 30px 0;">Todavía no hay tratamientos registrados en el odontograma.</p>
    @else
        <table class="tratamientos">
            <thead>
                <tr>
                    <th>Pieza</th>
                    <th>Diagnóstico / Tratamiento</th>
                    <th>Fecha</th>
                    <th style="text-align: right;">Precio</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($treatments->sortBy('tooth_number') as $treatment)
                    <tr>
                        <td>{{ $treatment->tooth_number ?: 'General' }}</td>
                        <td>{{ $treatment->treatment }}</td>
                        <td>{{ $treatment->date ? \Carbon\Carbon::parse($treatment->date)->format('d/m/Y') : '—' }}</td>
                        <td class="precio">{{ $treatment->price !== null ? 'Bs. ' . number_format((float) $treatment->price, 2) : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totales">
            <tr class="total-final">
                <td class="label">Total</td>
                <td class="valor">Bs. {{ number_format($totalRegistrado, 2) }}</td>
            </tr>
        </table>
    @endif

    <div class="footer">
        <p>Fecha de impresión: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

</body>
</html>
