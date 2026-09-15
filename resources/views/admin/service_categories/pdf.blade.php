<!-- resources/views/admin/service_categories/pdf.blade.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Categorías de Servicio</title>
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
        <h1>Listado de Categorías de Servicio</h1>
        <p class="subtitle">{{ $service_categories->count() }} registro{{ $service_categories->count() == 1 ? '' : 's' }}</p>
        @include('admin.settings.partials.pdf-contact-line')
    </div>

    <table class="listado">
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre de la Categoría</th>
                <th>Fecha de Registro</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($service_categories as $index => $category)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $category->name }}</td>
                    <td>{{ $category->created_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Fecha de impresión: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

</body>
</html>
