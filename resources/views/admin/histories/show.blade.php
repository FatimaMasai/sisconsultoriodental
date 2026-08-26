<x-admin-layout>

    <div class="flex justify-between items-center mb-6">
        <x-label class="text-black dark:text-white text-xl font-semibold">
            <i class="fa-solid fa-notes-medical text-gray-400 mr-1"></i>
            Detalle del Historial Médico
        </x-label>

        <a href="{{ route('admin.histories.index') }}" class="btn btn-gray rounded-lg text-sm">
            <i class="fa-solid fa-arrow-left mr-1"></i> Volver
        </a>
    </div>

    @if($history)
        {{-- Información general --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Paciente</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        {{ $history->patient->person->name }} {{ $history->patient->person->last_name_father }} {{ $history->patient->person->last_name_mother }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Doctor</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        {{ $history->doctor->person->name }} {{ $history->doctor->person->last_name_father }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Servicio</p>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $history->service->name }}</p>
                </div>

                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Fecha inicial</p>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($history->date)->format('d/m/Y') }}</p>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-400">Descripción inicial</p>
                <p class="text-gray-900 dark:text-white">{{ $history->description }}</p>
            </div>
        </div>

        {{-- Formulario para agregar nueva nota --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
            <x-label class="text-black dark:text-white text-base font-semibold mb-3 block">
                Agregar Consulta
            </x-label>

            <form action="{{ route('admin.histories.addNote', $history->id) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <textarea name="note" id="note" rows="4"
                        class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 shadow-sm w-full"
                        placeholder="Escribí las observaciones de esta consulta..." required></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn btn-green rounded-lg">
                        <i class="fa-solid fa-check mr-1"></i> Guardar Nota
                    </button>
                </div>
            </form>
        </div>

        {{-- Lista de notas --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <x-label class="text-black dark:text-white text-base font-semibold mb-3 block">
                Historial de Consultas
            </x-label>

            @if($history->notes->count() > 0)
                <div class="space-y-4">
                    @foreach($history->notes->sortByDesc('created_at') as $note)
                        <div class="p-4 bg-gray-50 dark:bg-gray-800 border-l-4 border-green-500 shadow-md rounded-lg">
                            <p class="text-gray-800 dark:text-white">{{ $note->note }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                <i class="fa-regular fa-clock mr-1"></i>
                                Agregado el {{ $note->created_at->format('d/m/Y H:i') }}
                            </p>
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
