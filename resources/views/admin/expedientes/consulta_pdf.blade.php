<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta</title>
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

        .section-title {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            background-color: #f0f4f3;
            color: #333;
            padding: 6px 8px;
            margin-top: 18px;
            margin-bottom: 8px;
            border-radius: 4px;
        }

        table.campos {
            width: 100%;
            border-collapse: collapse;
        }

        table.campos td {
            padding: 5px 6px;
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
            border: 1px solid #dcdfe0;
            padding: 4px 6px;
            font-size: 11px;
            text-align: left;
        }

        table.repetible th {
            background-color: #f0f4f3;
            text-transform: uppercase;
            font-size: 10px;
        }

        .note {
            background-color: #f8fafa;
            border-left: 4px solid #14b8a6;
            padding: 8px 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }

        .note .note-date {
            font-size: 11px;
            color: #888;
            margin: 0 0 3px 0;
            font-weight: bold;
        }

        .note p {
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
        <h1>Consulta — {{ $consulta->expediente->speciality->name ?? 'Especialidad' }}</h1>
        <p>{{ $consulta->formTemplate->name ?? 'Historial clínico' }}</p>
        @include('admin.settings.partials.pdf-contact-line')
    </div>

    <div class="details">
        <table>
            <tr>
                <td class="label">Paciente:</td>
                <td>
                    {{ $consulta->expediente->patient->person->name }}
                    {{ $consulta->expediente->patient->person->last_name_father }}
                    {{ $consulta->expediente->patient->person->last_name_mother }}
                </td>
            </tr>
            <tr>
                <td class="label">Atendido por:</td>
                <td>{{ $consulta->doctor->person->name ?? '—' }} {{ $consulta->doctor->person->last_name_father ?? '' }}</td>
            </tr>
            <tr>
                <td class="label">Fecha:</td>
                <td>{{ \Carbon\Carbon::parse($consulta->date)->format('d/m/Y') }}</td>
            </tr>
            @if ($consulta->description)
                <tr>
                    <td class="label">Descripción:</td>
                    <td>{{ $consulta->description }}</td>
                </tr>
            @endif
        </table>
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
                                    @php
                                        // Cada columna puede ser un texto simple (tablas viejas) o
                                        // un array ['label' => ..., 'type' => ...] (formato nuevo).
                                        // Acá solo interesa la etiqueta para el encabezado, pero hay
                                        // que normalizar igual o revienta con "Array to string
                                        // conversion" en las tablas nuevas.
                                        $columnasTabla = collect($field->options ?? [])->map(function ($col) {
                                            return is_array($col) ? ($col['label'] ?? '') : $col;
                                        });
                                    @endphp
                                    <table class="repetible">
                                        <thead>
                                            <tr>
                                                @foreach ($columnasTabla as $columna)
                                                    <th>{{ $columna }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($valor as $fila)
                                                <tr>
                                                    @foreach ($columnasTabla as $colIndex => $columna)
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
