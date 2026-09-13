<x-admin-layout>

    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
        <x-label class="text-black dark:text-white text-xl font-semibold">
            <i class="fa-solid fa-tooth text-gray-400 mr-1"></i>
            Odontograma
        </x-label>

        <div class="flex flex-wrap items-center gap-2">
            @can('admin.histories.odontograma.store')
                @if ($pendientes->isNotEmpty())
                    <a href="{{ route('admin.sales.create', ['patient_id' => $expediente->patient_id, 'tooth_treatments' => $pendientes->pluck('id')->implode(',')]) }}"
                        class="btn btn-green rounded-lg text-sm">
                        <i class="fa-solid fa-cash-register mr-1"></i> Cobrar pendientes ({{ $pendientes->count() }})
                    </a>
                @endif
            @endcan

            <a href="{{ route('admin.expedientes.show', $expediente) }}" class="btn btn-gray rounded-lg text-sm">
                <i class="fa-solid fa-arrow-left mr-1"></i> Volver al expediente
            </a>
        </div>
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

    {{-- Gráfico de piezas dentales: click en un diente para cargarle un
         tratamiento. El color de cada pieza refleja su último tratamiento
         registrado (gris = sin registrar, amarillo = pendiente, verde =
         realizado). --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
        <x-label class="text-black dark:text-white text-base font-semibold mb-1 block">Piezas dentales</x-label>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
            Hacé click en una pieza para cargarle un diagnóstico o tratamiento.
        </p>

        <div class="flex flex-wrap items-center gap-3 mb-4 text-xs text-gray-600 dark:text-gray-300">
            <span class="inline-flex items-center gap-1">
                <span class="w-3 h-3 rounded bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 inline-block"></span>
                Sin registrar
            </span>
            <span class="inline-flex items-center gap-1">
                <span class="w-3 h-3 rounded bg-yellow-200 dark:bg-yellow-800 border border-yellow-500 inline-block"></span>
                Pendiente
            </span>
            <span class="inline-flex items-center gap-1">
                <span class="w-3 h-3 rounded bg-green-200 dark:bg-green-800 border border-green-500 inline-block"></span>
                Realizado
            </span>
        </div>

        @php
            $piezasPermanentesSup = ['18', '17', '16', '15', '14', '13', '12', '11', '21', '22', '23', '24', '25', '26', '27', '28'];
            $piezasPermanentesInf = ['48', '47', '46', '45', '44', '43', '42', '41', '31', '32', '33', '34', '35', '36', '37', '38'];
            $piezasTemporalesSup = ['55', '54', '53', '52', '51', '61', '62', '63', '64', '65'];
            $piezasTemporalesInf = ['85', '84', '83', '82', '81', '71', '72', '73', '74', '75'];

            $colorPieza = function (string $pieza) use ($ultimaPorDiente) {
                $ultima = $ultimaPorDiente->get($pieza);

                if (! $ultima) {
                    return 'bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300';
                }

                return $ultima->completed
                    ? 'bg-green-200 dark:bg-green-800 border-green-500 text-green-900 dark:text-green-100'
                    : 'bg-yellow-200 dark:bg-yellow-800 border-yellow-500 text-yellow-900 dark:text-yellow-100';
            };

            // Determina el tipo de diente según su número FDI (cuadrante +
            // posición, 2 dígitos) para poder dibujarle una forma distinta en
            // el gráfico: incisivo, canino, premolar o molar. Las piezas
            // temporales (cuadrantes 5-8, dientes de leche) no tienen
            // premolares, así que ahí las posiciones 4 y 5 son molares.
            $toothType = function (string $pieza): string {
                $posicion = (int) substr($pieza, 1, 1);
                $esTemporal = ((int) substr($pieza, 0, 1)) >= 5;

                if ($posicion <= 2) {
                    return 'incisivo';
                }
                if ($posicion === 3) {
                    return 'canino';
                }

                return (! $esTemporal && $posicion <= 5) ? 'premolar' : 'molar';
            };

            // Silueta de cada tipo de diente (vista frontal simplificada:
            // corona arriba, raíz abajo), como SVG en línea para no depender
            // de ningún archivo de imagen. Usa "currentColor" (fill-current)
            // para heredar el mismo color de texto que ya define
            // $colorPieza según el estado, así no se duplica esa lógica.
            $toothIcon = function (string $tipo): string {
                return match ($tipo) {
                    'incisivo' => '<svg viewBox="0 0 24 32" class="w-4 h-5 fill-current mx-auto" aria-hidden="true">'
                        . '<rect x="8" y="2" width="8" height="10" rx="2"/>'
                        . '<polygon points="9,12 15,12 12,28"/>'
                        . '</svg>',
                    'canino' => '<svg viewBox="0 0 24 32" class="w-4 h-5 fill-current mx-auto" aria-hidden="true">'
                        . '<polygon points="7,13 9,6 12,2 15,6 17,13"/>'
                        . '<polygon points="9,13 15,13 12,30"/>'
                        . '</svg>',
                    'premolar' => '<svg viewBox="0 0 24 32" class="w-4 h-5 fill-current mx-auto" aria-hidden="true">'
                        . '<circle cx="9" cy="7" r="2.2"/>'
                        . '<circle cx="15" cy="7" r="2.2"/>'
                        . '<rect x="6" y="8" width="12" height="8" rx="2"/>'
                        . '<polygon points="8,16 16,16 12,27"/>'
                        . '</svg>',
                    default => '<svg viewBox="0 0 24 32" class="w-4 h-5 fill-current mx-auto" aria-hidden="true">'
                        . '<circle cx="7" cy="7" r="1.8"/>'
                        . '<circle cx="12" cy="6" r="1.8"/>'
                        . '<circle cx="17" cy="7" r="1.8"/>'
                        . '<rect x="4" y="8" width="16" height="9" rx="3"/>'
                        . '<polygon points="6,17 11,17 8,28"/>'
                        . '<polygon points="13,17 18,17 16,28"/>'
                        . '</svg>',
                };
            };
        @endphp

        <div class="space-y-4">
            <div class="overflow-x-auto">
                <p class="text-xs text-gray-400 mb-1">Permanentes · superior</p>
                <div class="flex flex-wrap gap-1">
                    @foreach ($piezasPermanentesSup as $pieza)
                        <button type="button" onclick="seleccionarPieza('{{ $pieza }}')" title="Pieza {{ $pieza }}"
                            class="flex flex-col items-center justify-center gap-0.5 w-10 h-12 shrink-0 rounded border text-[10px] leading-none font-semibold {{ $colorPieza($pieza) }} hover:opacity-80">
                            {!! $toothIcon($toothType($pieza)) !!}
                            <span>{{ $pieza }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
            <div class="overflow-x-auto">
                <p class="text-xs text-gray-400 mb-1">Permanentes · inferior</p>
                <div class="flex flex-wrap gap-1">
                    @foreach ($piezasPermanentesInf as $pieza)
                        <button type="button" onclick="seleccionarPieza('{{ $pieza }}')" title="Pieza {{ $pieza }}"
                            class="flex flex-col items-center justify-center gap-0.5 w-10 h-12 shrink-0 rounded border text-[10px] leading-none font-semibold {{ $colorPieza($pieza) }} hover:opacity-80">
                            {!! $toothIcon($toothType($pieza)) !!}
                            <span>{{ $pieza }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
            <div class="overflow-x-auto">
                <p class="text-xs text-gray-400 mb-1">Temporales (niños) · superior</p>
                <div class="flex flex-wrap gap-1">
                    @foreach ($piezasTemporalesSup as $pieza)
                        <button type="button" onclick="seleccionarPieza('{{ $pieza }}')" title="Pieza {{ $pieza }}"
                            class="flex flex-col items-center justify-center gap-0.5 w-10 h-12 shrink-0 rounded border text-[10px] leading-none font-semibold {{ $colorPieza($pieza) }} hover:opacity-80">
                            {!! $toothIcon($toothType($pieza)) !!}
                            <span>{{ $pieza }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
            <div class="overflow-x-auto">
                <p class="text-xs text-gray-400 mb-1">Temporales (niños) · inferior</p>
                <div class="flex flex-wrap gap-1">
                    @foreach ($piezasTemporalesInf as $pieza)
                        <button type="button" onclick="seleccionarPieza('{{ $pieza }}')" title="Pieza {{ $pieza }}"
                            class="flex flex-col items-center justify-center gap-0.5 w-10 h-12 shrink-0 rounded border text-[10px] leading-none font-semibold {{ $colorPieza($pieza) }} hover:opacity-80">
                            {!! $toothIcon($toothType($pieza)) !!}
                            <span>{{ $pieza }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Formulario para agregar un tratamiento a la pieza seleccionada --}}
        @can('admin.histories.odontograma.store')
            <form action="{{ route('admin.expedientes.tooth-treatments.store', $expediente) }}" method="POST"
                class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700">
                @csrf

                <x-validation-errors class="mb-4" />

                <p class="text-sm mb-3">
                    Pieza seleccionada:
                    <span id="pieza-seleccionada-label" class="font-semibold text-gray-900 dark:text-white">Ninguna, hacé click arriba</span>
                </p>

                <input type="hidden" name="tooth_number" id="tooth_number_input" required>

                <div class="grid gap-4 sm:grid-cols-4">
                    <div class="sm:col-span-2">
                        <x-label class="form-label">Diagnóstico / Tratamiento <span class="text-red-500">*</span></x-label>
                        <x-input name="treatment" value="{{ old('treatment') }}" class="input-label rounded-lg w-full treatment-input"
                            placeholder="Ej: Resina, Exodoncia, Limpieza..." />
                    </div>
                    <div>
                        <x-label class="form-label">Precio</x-label>
                        <x-input type="number" step="0.01" min="0" name="price" value="{{ old('price') }}" class="input-label rounded-lg w-full price-input" />
                    </div>
                    <div>
                        <x-label class="form-label">Fecha</x-label>
                        <x-input type="date" name="date" value="{{ old('date', now()->format('Y-m-d')) }}"
                            class="input-label rounded-lg w-full" max="{{ now()->format('Y-m-d') }}" />
                    </div>
                </div>

                <label class="inline-flex items-center gap-2 mt-3 text-sm text-gray-700 dark:text-gray-300">
                    <input type="checkbox" name="completed" value="1" class="rounded border-gray-300">
                    Realizado
                </label>

                <div class="flex justify-end mt-3">
                    <button type="submit" class="btn btn-green rounded-lg text-sm">
                        <i class="fa-solid fa-check mr-1"></i> Guardar
                    </button>
                </div>
            </form>
        @endcan
    </div>

    {{-- Historial de tratamientos --}}
    <x-label class="text-black dark:text-white text-base font-semibold mb-3 block">
        Tratamientos registrados ({{ $treatments->count() }})
    </x-label>

    @if ($treatments->isNotEmpty())
        @php
            // Suma de la columna Precio, separada entre lo ya cobrado (tiene
            // sale_id) y lo pendiente (mismo criterio que $pendientes /
            // "Cobrar pendientes" arriba), para tener a la vista cuánto suma
            // el tratamiento completo sin tener que sumar fila por fila.
            $totalPendienteCobro = $pendientes->sum(fn ($t) => (float) ($t->price ?? 0));
            $totalRegistrado = $treatments->sum(fn ($t) => (float) ($t->price ?? 0));
            $totalCobrado = $totalRegistrado - $totalPendienteCobro;
        @endphp
        <div class="flex flex-wrap gap-3 mb-3 text-sm">
            <span class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full px-3 py-1">
                Total: Bs {{ number_format($totalRegistrado, 2) }}
            </span>
            <span class="bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 rounded-full px-3 py-1">
                Cobrado: Bs {{ number_format($totalCobrado, 2) }}
            </span>
            <span class="bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 rounded-full px-3 py-1">
                Sin cobrar: Bs {{ number_format($totalPendienteCobro, 2) }}
            </span>
        </div>
    @endif

    @if ($treatments->isEmpty())
        <div class="flex items-center p-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
            <span class="font-medium">Info alert!</span>&nbsp;Todavía no se registró ningún tratamiento en el odontograma de este paciente.
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="table-stack text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400 w-full">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th class="px-4 py-3">Pieza</th>
                    <th class="px-4 py-3">Diagnóstico / Tratamiento</th>
                    <th class="px-4 py-3">Precio</th>
                    <th class="px-4 py-3">Fecha</th>
                    <th class="px-4 py-3">Realizado</th>
                    <th class="px-4 py-3">Cobro</th>
                    <th class="px-4 py-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($treatments as $treatment)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700" id="tooth-treatment-view-{{ $treatment->id }}">
                        <td data-label="Pieza" class="px-4 py-3 font-semibold">{{ $treatment->tooth_number }}</td>
                        <td data-label="Diagnóstico / Tratamiento" class="px-4 py-3">{{ $treatment->treatment }}</td>
                        <td data-label="Precio" class="px-4 py-3">{{ $treatment->price !== null ? number_format((float) $treatment->price, 2) : '—' }}</td>
                        <td data-label="Fecha" class="px-4 py-3">{{ $treatment->date ? $treatment->date->format('d/m/Y') : '—' }}</td>
                        <td data-label="Realizado" class="px-4 py-3">
                            @if ($treatment->completed)
                                <span class="text-xs bg-green-100 text-green-800 rounded-full px-2 py-1">Sí</span>
                            @else
                                <span class="text-xs bg-yellow-100 text-yellow-800 rounded-full px-2 py-1">Pendiente</span>
                            @endif
                        </td>
                        <td data-label="Cobro" class="px-4 py-3">
                            @if ($treatment->sale)
                                <a href="{{ route('admin.sales.show', $treatment->sale) }}" target="_blank"
                                    class="text-xs bg-blue-100 text-blue-800 rounded-full px-2 py-1 hover:bg-blue-200">
                                    <i class="fa-solid fa-check mr-1"></i> Cobrado · {{ $treatment->sale->numero }}
                                </a>
                            @else
                                <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-full px-2 py-1">
                                    Sin cobrar
                                </span>
                            @endif
                        </td>
                        <td data-label="Acciones" class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                @can('admin.histories.odontograma.store')
                                    <button type="button" onclick="toggleToothTreatmentEdit({{ $treatment->id }})" class="btn btn-blue text-xs">
                                        <i class="fa-solid fa-pen mr-1"></i> Editar
                                    </button>
                                @endcan
                                @can('admin.histories.odontograma.destroy')
                                    <form class="delete-form" action="{{ route('admin.tooth-treatments.destroy', $treatment) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-red text-xs">
                                            <i class="fa-solid fa-trash-can mr-1"></i> Eliminar
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>

                    @can('admin.histories.odontograma.store')
                        <tr id="tooth-treatment-edit-{{ $treatment->id }}" class="hidden bg-gray-50 dark:bg-gray-900">
                            <td colspan="7" class="px-4 py-3">
                                <form action="{{ route('admin.tooth-treatments.update', $treatment) }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="grid gap-4 sm:grid-cols-4">
                                        <div>
                                            <x-label class="form-label">Pieza</x-label>
                                            <x-input name="tooth_number" value="{{ $treatment->tooth_number }}" class="input-label rounded-lg w-full" />
                                        </div>
                                        <div class="sm:col-span-2">
                                            <x-label class="form-label">Diagnóstico / Tratamiento</x-label>
                                            <x-input name="treatment" value="{{ $treatment->treatment }}" class="input-label rounded-lg w-full treatment-input" />
                                        </div>
                                        <div>
                                            <x-label class="form-label">Precio</x-label>
                                            <x-input type="number" step="0.01" min="0" name="price" value="{{ $treatment->price }}" class="input-label rounded-lg w-full price-input" />
                                        </div>
                                        <div>
                                            <x-label class="form-label">Fecha</x-label>
                                            <x-input type="date" name="date" value="{{ $treatment->date ? $treatment->date->format('Y-m-d') : '' }}"
                                                class="input-label rounded-lg w-full" max="{{ now()->format('Y-m-d') }}" />
                                        </div>
                                        <div class="flex items-end">
                                            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                                <input type="checkbox" name="completed" value="1" class="rounded border-gray-300" @checked($treatment->completed)>
                                                Realizado
                                            </label>
                                        </div>
                                    </div>

                                    <div class="flex justify-end gap-2 mt-3">
                                        <button type="button" onclick="toggleToothTreatmentEdit({{ $treatment->id }})" class="btn btn-gray rounded-lg text-xs">
                                            Cancelar
                                        </button>
                                        <button type="submit" class="btn btn-green rounded-lg text-xs">
                                            Guardar cambios
                                        </button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @endcan
                @endforeach
            </tbody>
        </table>
    </div>

    @push('js')
    <script>
        document.addEventListener('submit', function (e) {
            const form = e.target.closest('.delete-form');
            if (!form) return;

            e.preventDefault();

            Swal.fire({
                title: "¿Está seguro?",
                text: "¡No podrás revertir esto!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sí, ¡eliminalo!",
                cancelButtonText: "Cancelar",
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        // Marca en el formulario de arriba qué pieza se va a cargar cuando
        // el doctor hace click en un diente del gráfico.
        function seleccionarPieza(pieza) {
            const input = document.getElementById('tooth_number_input');
            const label = document.getElementById('pieza-seleccionada-label');
            if (!input || !label) return;

            input.value = pieza;
            label.textContent = pieza;
        }

        // Muestra/oculta el formulario para corregir un tratamiento ya
        // guardado, sin necesidad de ir a otra pantalla.
        function toggleToothTreatmentEdit(id) {
            const vista = document.getElementById('tooth-treatment-view-' + id);
            const form = document.getElementById('tooth-treatment-edit-' + id);
            if (!vista || !form) return;

            vista.classList.toggle('hidden');
            form.classList.toggle('hidden');
        }
    </script>
    @endpush

</x-admin-layout>
