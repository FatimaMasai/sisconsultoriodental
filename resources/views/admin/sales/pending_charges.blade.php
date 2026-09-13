<x-admin-layout>

    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
        <x-label class="text-black dark:text-white text-xl font-semibold">
            <i class="fa-solid fa-money-bill-transfer text-gray-400 mr-1"></i>
            Cobros pendientes
        </x-label>

        <a href="{{ route('admin.sales.index') }}" class="btn btn-gray rounded-lg text-sm">
            <i class="fa-solid fa-arrow-left mr-1"></i> Volver a Ventas
        </a>
    </div>

    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
        Pacientes con tratamientos cargados en el Odontograma que todavía no se cobraron, sin importar quién los cargó ni en qué computadora.
    </p>

    @if ($pendientesPorPaciente->isEmpty())
        <div class="flex items-center p-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
            <span class="font-medium">Info alert!</span>&nbsp;No hay ningún cobro pendiente del Odontograma por ahora.
        </div>
    @endif

    <div class="space-y-3">
        @foreach ($pendientesPorPaciente as $grupo)
            @php $patient = $grupo['patient']; @endphp

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-900 dark:text-white">
                            {{ $patient->person->name }} {{ $patient->person->last_name_father }} {{ $patient->person->last_name_mother }}
                        </p>

                        <ul class="text-sm text-gray-600 dark:text-gray-300 mt-1 space-y-0.5">
                            @foreach ($grupo['treatments'] as $treatment)
                                <li>
                                    Pieza {{ $treatment->tooth_number }} — {{ $treatment->treatment }}
                                    @if ($treatment->price !== null)
                                        <span class="text-gray-500 dark:text-gray-400">(Bs {{ number_format((float) $treatment->price, 2) }})</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>

                        <p class="text-sm font-semibold text-gray-900 dark:text-white mt-2">
                            Total sugerido: Bs {{ number_format($grupo['total'], 2) }}
                        </p>
                    </div>

                    <a href="{{ route('admin.sales.create', ['patient_id' => $patient->id, 'tooth_treatments' => $grupo['treatments']->pluck('id')->implode(',')]) }}"
                        class="btn btn-green rounded-lg text-sm shrink-0">
                        <i class="fa-solid fa-cash-register mr-1"></i> Cobrar
                    </a>
                </div>
            </div>
        @endforeach
    </div>

</x-admin-layout>
