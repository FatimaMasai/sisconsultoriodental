<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Compras</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    @if (function_exists('imagecreatefrompng') && file_exists(public_path('images/logo.png')))
        <div style="text-align: center; margin-bottom: 10px;">
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/logo.png'))) }}" alt="Mi Consulta" style="height: 42px;">
        </div>
    @endif

    <h1>Listado de Compras</h1>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Proveedor</th>
                <th>NIT</th>
                <th>Fecha</th>
                <th>Total</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($purchases as $index => $purchase)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $purchase->supplier->company }}</td>
                    <td>{{ $purchase->supplier->nit }}</td>
                    <td>{{ $purchase->date }}</td>
                    <td>{{ number_format($purchase->total, 2) }}</td>
                    <td>{{ $purchase->status == 1 ? 'Activa' : 'Anulada' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
