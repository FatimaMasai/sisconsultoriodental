<!-- resources/views/admin/persons/pdf.blade.php -->
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

    <h1>Listado de Personas</h1>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre Completo/th>
                <th>Carnet</th>
                <th>Celular</th>
                <th>Sexo</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($persons as $index => $person)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $person->name }} {{$person->last_name_father}} {{$person->last_name_mother}}</td>
                     
                    <td> {{ $person->identity_card }}  </td>
                    <td>{{ $person->phone }} </td>
                    <td>{{ $person->gender }}</td>
                     
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
