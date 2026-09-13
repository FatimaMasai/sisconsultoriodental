<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta</title>
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

        .header img {
            height: 46px;
            margin-bottom: 8px;
        }

        .header h1 {
            font-size: 18px;
            margin: 0;
        }

        .header p {
            font-size: 13px;
            margin: 5px 0;
            color: #555;
        }

        .details p {
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            background-color: #f0f0f0;
            padding: 5px 8px;
            margin-top: 18px;
            margin-bottom: 8px;
        }

        table.campos {
            width: 100%;
            border-collapse: collapse;
        }

        table.campos td {
            padding: 4px 6px;
            vertical-align: top;
            font-size: 12px;
            border-bottom: 1px solid #eee;
        }

        table.campos td.etiqueta {
            width: 40%;
            color: #555;
        }

        table.repetible {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0 8px 0;
        }

        table.repetible th, table.repetible td {
            border: 1px solid #ddd;
            padding: 3px 6px;
            font-size: 11px;
            text-align: left;
        }

        table.repetible th {
            background-color: #fafafa;
        }

        .note {
            background-color: #f9f9f9;
            border-left: 5px solid #4A90E2;
            padding: 8px;
            margin-bottom: 10px;
            border-radius: 5px;
        }

        .note .note-date {
            font-size: 11px;
            color: #888;
            margin-bottom: 3px;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 11px;
            color: #888;
        }
    </style>
</head>
<body>

    <div class="header">
        @php $logoDataUri = \App\Models\ClinicSetting::instance()->logoBase64(); @endphp
        @if ($logoDataUri)
            <img src="{{ $logoDataUri }}" alt="Logo">
        @endif
        <h1>Consulta — {{ $consulta->expediente->speciality->name ?? 'Especialidad' }}</h1>
        <p>{{ $consulta->formTemplate->name ?? 'Historial clínico' }}</p>
        @include('admin.settings.partials.pdf-contact-line')
    </div>

    <div class="details">
        <p><strong>Paciente:</strong>
            {{ $consulta->expediente->patient->person->name }}
            {{ $consulta->expediente->patient->person->last_name_father }}
            {{ $consulta->expediente->patient->person->last_name_mother }}
        </p>
        <p><strong>Atendido por:</strong>
            {{ $consulta->doctor->person->name ?? '—' }} {{ $consulta->doctor->person->last_name_father ?? '' }}
        </p>
        <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($consulta->date)->format('d/m/Y') }}</p>
        @if ($consulta->description)
            <p><strong>Descripción:</strong> {{ $consulta->description }}</p>
        @endif
    </div>

    @if ($consulta->formTemplate && ! empty($consulta->data))
        @foreach ($consulta->formTemplate->sections as $section)
            @php
                $camposConValor = $section->fields->filter(function ($field) use ($consulta) {
                    $valor = $consulta->data[$field->id] ?? null;
                    return $valor !== null && $valor !== '' && $valor !== [];
                });
            @endphp

            @if ($camposConValor->isNotEmpty())
                <div class="section-title">{{ $section->title }}</div>

                <table class="campos">
                    @foreach ($camposConValor as $field)
                        @php $valor = $consulta->data[$field->id]; @endphp
                        <tr>
                            <td class="etiqueta">{{ $field->label }}</td>
                            <td>
                                @if ($field->type === 'tabla_repetible')
                                    <table class="repetible">
                                        <thead>
                                            <tr>
                                                @foreach ($field->options ?? [] as $columna)
                                                    <th>{{ $columna }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($valor as $fila)
                                                <tr>
                                                    @foreach ($field->options ?? [] as $colIndex => $columna)
                                                        <td>{{ $fila[$colIndex] ?? '' }}</td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @elseif (is_array($valor))
                                    {{ implode(', ', $valor) }}
                                @else
                                    {{ $valor }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </table>
            @endif
        @endforeach
    @endif

    @if ($consulta->notes->isNotEmpty())
        <div class="section-title">Notas</div>
        @foreach ($consulta->notes->sortByDesc('created_at') as $note)
            <div class="note">
                <p class="note-date">{{ \Carbon\Carbon::parse($note->created_at)->format('d/m/Y H:i') }}</p>
                <p>{{ $note->note }}</p>
            </div>
        @endforeach
    @endif

    <div class="footer">
        <p>Fecha de impresión: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

</body>
</html>
