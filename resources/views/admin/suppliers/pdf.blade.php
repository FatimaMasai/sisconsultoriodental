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

    @if (function_exists('imagecreatefrompng') && file_exists(public_path('images/logo.png')))
        <div style="text-align: center; margin-bottom: 10px;">
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/logo.png'))) }}" alt="Mi Consulta" style="height: 42px;">
        </div>
    @endif

    <h1>Listado de Proveedor</h1>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>  
                <th>Celular</th>
                <th>Nit</th>
                <th>Empresa</th>
                <th>Fecha de Registro</th> 
            </tr>
        </thead>
        <tbody>
            @foreach ($suppliers as $index => $supplier)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{$supplier->person->name}} {{$supplier->person->last_name_father}} {{$supplier->person->last_name_mother}}</td>
                     
 
                    <td>{{ $supplier->person->phone }} </td>
                    <td>{{ $supplier->nit }} </td>
                    <td>{{ $supplier->company }} </td> 
                    <td> {{$supplier->person->created_at}} </td>

                    {{-- <td>{{ $service->status == 1 ? 'Activo' : 'Inactivo' }}</td> --}}
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
