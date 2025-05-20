<!-- resources/views/admin/product_categories/pdf.blade.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Categorías de Servicio</title>
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

    <h1>Listado de Categorias Productos</h1>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Fecha de Registro</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($product_categories as $index => $product_category)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $product_category->name }}</td>
                    <td> {{$product_category->created_at}} </td>
                    {{-- <td>{{ $product_category->status == 1 ? 'Activo' : 'Inactivo' }}</td> --}}
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
