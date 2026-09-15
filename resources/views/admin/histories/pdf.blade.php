<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial Médico</title>
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
            width: 90px;
            color: #667;
            font-weight: bold;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #222;
            margin: 0 0 10px 0;
        }

        .note {
            background-color: #f8fafa;
            border-left: 4px solid #14b8a6;
            padding: 10px 12px;
            margin-bottom: 12px;
            border-radius: 5px;
        }

        .note .note-date {
            font-size: 11px;
            color: #888;
            margin: 0 0 5px 0;
            font-weight: bold;
        }

        .note .note-content {
            font-size: 12.5px;
            color: #444;
            line-height: 1.6;
            margin: 0;
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
        <h1>Historial Médico</h1>
        <p class="subtitle">{{ $history->service->name ?? 'Servicio' }}</p>
        @include('admin.settings.partials.pdf-contact-line')
    </div>

    <div class="details">
        <table>
            <tr>
                <td class="label">Paciente:</td>
                <td>{{ $history->patient->person->name }} {{ $history->patient->person->last_name_father }} {{ $history->patient->person->last_name_mother }}</td>
            </tr>
            <tr>
                <td class="label">Doctor:</td>
                <td>{{ $history->doctor->person->name }} {{ $history->doctor->person->last_name_father }} {{ $history->doctor->person->last_name_mother }}</td>
            </tr>
            <tr>
                <td class="label">Servicio:</td>
                <td>{{ $history->service->name }}</td>
            </tr>
            <tr>
                <td class="label">Fecha:</td>
                <td>{{ \Carbon\Carbon::parse($history->date)->format('d/m/Y') }}</td>
            </tr>
        </table>
    </div>

    <p class="section-title">Historial de Consultas</p>

    @forelse ($history->notes as $note)
        <div class="note">
            <p class="note-date">Consulta del {{ \Carbon\Carbon::parse($note->created_at)->format('d/m/Y') }}</p>
            <p class="note-content">{{ $note->note }}</p>
        </div>
    @empty
        <p style="text-align: center; color: #777; margin: 20px 0;">Todavía no hay notas registradas.</p>
    @endforelse

    <div class="footer">
        <p>Fecha de impresión: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

</body>
</html>
