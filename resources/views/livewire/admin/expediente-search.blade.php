<div>
    {{-- Buscador en tiempo real: se filtra solo, sin botón ni recargar la página --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
        <div class="flex items-center gap-2 mb-4">
            <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
            <x-label class="text-black dark:text-white text-base font-semibold">Buscar expediente</x-label>
        </div>

        <div class="relative w-full sm:w-96">
            <input type="text" wire:model.live.debounce.300ms="search"
                class="input-label rounded-lg w-full"
                placeholder="Buscar por paciente o especialidad">

            <div wire:loading wire:target="search" class="absolute inset-y-0 right-3 flex items-center">
                <i class="fa-solid fa-spinner fa-spin text-gray-400"></i>
            </div>
        </div>

        @if (trim($search) !== '')
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-3">
                {{ $expedientes->total() }} resultado(s) para "{{ $search }}"
            </p>
        @endif
    </div>

    @if ($expedientes->count())

        {{-- Tarjetas en vez de tabla: nada de scroll lateral, ni en celular ni en pantalla grande --}}
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($expedientes as $expediente)
                @php
                    $nombreCompleto = trim($expediente->patient->person->name . ' ' . $expediente->patient->person->last_name_father . ' ' . $expediente->patient->person->last_name_mother);
                    $iniciales = strtoupper(mb_substr($expediente->patient->person->name, 0, 1) . mb_substr($expediente->patient->person->last_name_father, 0, 1));
                @endphp

                <div class="relative bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-blue-400 dark:hover:border-blue-500 hover:shadow-md transition">
                    @can('admin.histories.show')
                        <a href="{{ route('admin.expedientes.show', $expediente) }}" class="absolute inset-0 rounded-lg"
                            aria-label="Ver expediente de {{ $nombreCompleto }}"></a>
                    @endcan

                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 flex items-center justify-center font-semibold text-sm shrink-0">
                            {{ $iniciales }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-gray-900 dark:text-white truncate">
                                {{ $nombreCompleto }}
                            </p>
                            <span class="inline-block mt-1 text-xs font-medium px-2 py-0.5 rounded-full bg-teal-50 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300">
                                {{ $expediente->speciality->name ?? '—' }}
                            </span>
                        </div>
                        <i class="fa-solid fa-chevron-right text-gray-300 dark:text-gray-600 mt-2"></i>
                    </div>

                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mt-4 pt-3 border-t border-gray-100 dark:border-gray-700">
                        <span><i class="fa-solid fa-notes-medical mr-1"></i>{{ $expediente->consultas_count }} consulta(s)</span>
                        <span><i class="fa-regular fa-calendar mr-1"></i>{{ $expediente->consultas_max_date ? \Carbon\Carbon::parse($expediente->consultas_max_date)->format('d/m/Y') : '—' }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $expedientes->links() }}
        </div>

    @else

        <div class="flex items-center p-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
            <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
            </svg>

            <span class="sr-only">Info</span>
            <div>
                <span class="font-medium">Info alert!</span>
                @if (trim($search) !== '')
                    No se encontraron expedientes con esa búsqueda.
                @else
                    Todavía no hay expedientes registrados. Se crean automáticamente al registrar una venta o una consulta para un paciente.
                @endif
            </div>
        </div>

    @endif
</div>
