<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Pacientes</title>
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

    <h1>Listado de Doctor</h1>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th> 
                <th>Especialidad</th>
                <th>Celular</th>
                <th>Fecha de Registro</th> 
            </tr>
        </thead>
        <tbody>
            @foreach ($doctors as $index => $doctor)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{$doctor->person->name}} {{$doctor->person->last_name_father}} {{$doctor->person->last_name_mother}}</td>
                     

                    <td>{{ $doctor->person->gender }} </td>
                    <td>{{ $doctor->person->phone }} </td>
                    <td> {{$doctor->person->created_at}} </td>

                    {{-- <td>{{ $service->status == 1 ? 'Activo' : 'Inactivo' }}</td> --}}
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
