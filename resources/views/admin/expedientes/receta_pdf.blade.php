<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receta médica</title>
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

        {{-- Banda de color arriba de todo, con el logo adentro (en una
             chapita negra con borde turquesa, el mismo look que la barra
             de navegación del sistema) — el mismo estilo que la recetario
             física del consultorio. --}}
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

        .rx-symbol {
            font-size: 24px;
            font-weight: bold;
            color: #14b8a6;
            margin: 0 0 8px 0;
        }

        .contenido {
            border: 1px solid #dcdfe0;
            border-radius: 8px;
            padding: 18px;
            min-height: 260px;
            font-size: 14px;
            line-height: 1.8;
            white-space: pre-line;
        }

        .signature {
            margin-top: 70px;
            text-align: center;
        }

        .signature .line {
            width: 260px;
            margin: 0 auto;
            border-top: 1px solid #333;
            padding-top: 6px;
            font-size: 12px;
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
        <h1>Receta médica</h1>
        <p>{{ $receta->consulta->expediente->speciality->name ?? 'Especialidad' }}</p>
        @include('admin.settings.partials.pdf-contact-line')
    </div>

    <div class="details">
        <table>
            <tr>
                <td class="label">Paciente:</td>
                <td>
                    {{ $receta->consulta->expediente->patient->person->name }}
                    {{ $receta->consulta->expediente->patient->person->last_name_father }}
                    {{ $receta->consulta->expediente->patient->person->last_name_mother }}
                </td>
            </tr>
            <tr>
                <td class="label">Doctor:</td>
                <td>{{ $receta->consulta->doctor->person->name ?? '—' }} {{ $receta->consulta->doctor->person->last_name_father ?? '' }}</td>
            </tr>
            <tr>
                <td class="label">Fecha:</td>
                <td>{{ $receta->created_at->format('d/m/Y') }}</td>
            </tr>
        </table>
    </div>

    <div class="rx-symbol">Rx</div>

    <div class="contenido">{{ $receta->contenido }}</div>

    <div class="signature">
        <div class="line">
            {{ $receta->consulta->doctor->person->name ?? '—' }} {{ $receta->consulta->doctor->person->last_name_father ?? '' }}
        </div>
    </div>

    <div class="footer">
        <p>Fecha de impresión: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

</body>
</html>
