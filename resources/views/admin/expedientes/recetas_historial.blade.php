<x-admin-layout>

    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
        <x-label class="text-black dark:text-white text-xl font-semibold">
            <i class="fa-solid fa-prescription text-gray-400 mr-1"></i>
            Historial de recetas
        </x-label>

        <a href="{{ route('admin.expedientes.show', $expediente) }}" class="btn btn-gray rounded-lg text-sm">
            <i class="fa-solid fa-arrow-left mr-1"></i> Volver al expediente
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Paciente</p>
                <p class="font-semibold text-gray-900 dark:text-white">
                    {{ $expediente->patient->person->name }} {{ $expediente->patient->person->last_name_father }} {{ $expediente->patient->person->last_name_mother }}
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Especialidad</p>
                <p class="font-semibold text-gray-900 dark:text-white">{{ $expediente->speciality->name ?? '—' }}</p>
            </div>
        </div>
    </div>

    <x-label class="text-black dark:text-white text-base font-semibold mb-3 block">
        Recetas ({{ $recetas->count() }})
    </x-label>

    @if ($recetas->isEmpty())
        <div class="flex items-center p-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
            <span class="font-medium">Info alert!</span>&nbsp;Todavía no se registró ninguna receta para este paciente en esta especialidad.
        </div>
    @endif

    <div class="space-y-3">
        @foreach ($recetas as $receta)
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="flex flex-wrap items-start justify-between gap-2 mb-2">
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-900 dark:text-white">
                            {{ $receta->created_at->format('d/m/Y H:i') }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                            {{ $receta->consulta->doctor->person->name ?? '—' }} {{ $receta->consulta->doctor->person->last_name_father ?? '' }}
                        </p>
                    </div>

                    <a href="{{ route('admin.recetas.pdf', $receta) }}" target="_blank" class="btn btn-gray text-xs shrink-0">
                        <i class="fa-solid fa-print mr-1"></i> Imprimir
                    </a>
                </div>

                <p class="text-sm text-gray-800 dark:text-white whitespace-pre-line">{{ $receta->contenido }}</p>
            </div>
        @endforeach
    </div>

</x-admin-layout>
