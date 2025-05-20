<!-- resources/views/admin/specialities/pdf.blade.php -->
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

    <h1>Listado de Especialidad</h1>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Fecha de Registro</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($specialities as $index => $speciality)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $speciality->name }}</td>
                    <td> {{$speciality->created_at}} </td>
                    {{-- <td>{{ $speciality->status == 1 ? 'Activo' : 'Inactivo' }}</td> --}}
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
