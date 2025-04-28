<x-admin-layout>

    <div class="mb-6">
        <h1 class="text-2xl font-bold">Detalle del Historial Médico</h1>
    </div>

    @if($history)
        <div class="bg-white p-6 rounded shadow mb-6">
            <p><strong>Paciente:</strong> {{ $history->patient->person->name }} {{ $history->patient->person->last_name_father }}</p>
            <p><strong>Doctor:</strong> {{ $history->doctor->person->name }} {{ $history->doctor->person->last_name_father }}</p>
            <p><strong>Servicio:</strong> {{ $history->service->name }}</p>
            <p><strong>Fecha inicial:</strong> {{ \Carbon\Carbon::parse($history->date)->format('d/m/Y') }}</p>
            <p><strong>Descripción inicial:</strong> {{ $history->description }}</p>
        </div> 

        <!-- Formulario para agregar nueva nota -->
        <div class="bg-white p-6 rounded shadow mb-6">
            <h2 class="text-xl font-bold mb-4">Agregar Consulta</h2>
            <form action="{{ route('admin.histories.addNote', $history->id) }}" method="POST">
                @csrf
                <div class="mb-4">  
                    <textarea name="note" id="note" rows="4" class="block w-full border rounded px-4 py-2" required></textarea>
                </div>
                {{-- <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-6 rounded">
                    Guardar Nota
                </button> --}}

                <div class="flex justify-end">
                    <x-button>
                        Guardar Nota
                    </x-button>
                </div>
            </form>
        </div>

        <!-- Lista de notas -->
        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-xl font-bold mb-4">Historial de Consultas</h2>

            @if($history->notes->count() > 0)
            <div class="space-y-4">
                    @foreach($history->notes as $note)
                        <div class="p-4 bg-gray-50 border-l-4 border-green-500 shadow-md rounded">
                            <p class="text-gray-800 text-lg">{{ $note->note }}</p>
                            <small class="text-gray-500">Agregado el {{ $note->created_at->format('d/m/Y H:i') }}</small>
                        </div>
  

                    @endforeach
                    </div>
            @else

            <div class="flex items-center p-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
                <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                </svg>
    
                <span class="sr-only">Info</span>
                <div>
                    <span class="font-medium">Info alert!</span> Todavia no hay consulta registrada.
                </div>
            </div> 
 
            @endif
        </div>
 
        
    @endif

</x-admin-layout>
