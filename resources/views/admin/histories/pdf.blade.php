<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial Médico</title>
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

        .header h1 {
            font-size: 18px;
            margin: 0;
        }

        .header p {
            font-size: 14px;
            margin: 5px 0;
        }
        .note {
            background-color: #f9f9f9;
            border-left: 5px solid #4A90E2;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .note .note-date {
            font-size: 12px;
            color: #888;
            margin-bottom: 5px;
        }

        .note .note-content {
            font-size: 14px;
            color: #444;
            line-height: 1.6;
        }
 

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
        }
    </style>
</head>
<body>

    <!-- Encabezado -->
    <div class="header">
        <h1>Historal Médico</h1> 
    </div>

    <br>

    <!-- Datos del paciente -->
    <div class="details">

        <!-- Datos del paciente -->
        <p style="margin: 0; padding: 0;"><strong>Paciente:</strong> {{ $history->patient->person->name }} {{ $history->patient->person->last_name_father }} {{ $history->patient->person->last_name_mother }}</p>       
        {{-- <p style="margin: 0; padding: 0;"><strong>Edad:</strong> {{ \Carbon\Carbon::parse($history->patient->person->birth_date)->age }} años</p> <!-- Calcular edad --> --}}
        <p style="margin: 0; padding: 0;"><strong>Doctor:</strong> {{ $history->doctor->person->name }} {{ $history->doctor->person->last_name_father }} {{ $history->doctor->person->last_name_mother }}</p> <!-- Teléfono -->
        <p style="margin: 0; padding: 0;"><strong>Servicio:</strong> {{ $history->service->name }}</p>
        <p style="margin: 0; padding: 0;"><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($history->date)->format('d/m/Y') }}</p>  
        
        <hr>  
    </div>

    <!-- Historial de Consultas -->
    <div class="section-title">Historial de Consultas:</div>
    @foreach($history->notes as $note)
        <div class="note">
            <p class="note-date"><strong>Consulta del {{ \Carbon\Carbon::parse($note->created_at)->format('d/m/Y') }}:</strong></p>
            <p class="note-content">{{ $note->note }}</p>
        </div>
    @endforeach
      
     


    <!-- Firma -->
   <br> 
    <div class="footer"> 
        <p style="margin: 0; padding: 0;"><strong>Fecha de Impresión:</strong> {{ now()->format('d/m/Y H:i:s') }} </p>

    </div>

</body>
</html>