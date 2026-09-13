<x-admin-layout>

    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
        <x-label class="text-black dark:text-white text-xl font-semibold">
            <i class="fa-solid fa-pen-to-square text-gray-400 mr-1"></i>
            Editar consulta del {{ \Carbon\Carbon::parse($consulta->date)->format('d/m/Y') }}
        </x-label>

        <a href="{{ route('admin.expedientes.show', $consulta->expediente_id) }}" class="btn btn-gray rounded-lg text-sm">
            <i class="fa-solid fa-arrow-left mr-1"></i> Volver
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
        <x-validation-errors class="mb-4" />

        <form action="{{ route('admin.consultas.update', $consulta) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <x-label class="form-label">Atendido por <span class="text-red-500">*</span></x-label>

                    @if ($doctorActual)
                        <input type="hidden" name="doctor_id" value="{{ $doctorActual->id }}">
                        <p class="input-label rounded-lg w-full bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 flex items-center">
                            {{ $doctorActual->person->name }} {{ $doctorActual->person->last_name_father }}
                        </p>
                    @else
                        <x-select name="doctor_id" class="input-label rounded-lg w-full">
                            <option value="">Seleccione un doctor</option>
                            @foreach ($doctoresDeEspecialidad as $doc)
                                <option value="{{ $doc->id }}" @selected(old('doctor_id', $consulta->doctor_id) == $doc->id)>
                                    {{ $doc->person->name }} {{ $doc->person->last_name_father }}
                                </option>
                            @endforeach
                        </x-select>
                    @endif
                </div>

                <div>
                    <x-label class="form-label">Fecha <span class="text-red-500">*</span></x-label>
                    <x-input type="date" name="date"
                        value="{{ old('date', \Carbon\Carbon::parse($consulta->date)->format('Y-m-d')) }}"
                        class="input-label rounded-lg w-full" max="{{ now()->format('Y-m-d') }}" />
                </div>

                <div class="sm:col-span-3">
                    <x-label class="form-label">Descripción</x-label>
                    <x-input name="description" value="{{ old('description', $consulta->description) }}"
                        class="input-label rounded-lg w-full" placeholder="Ej: control, limpieza, primera consulta..." />
                </div>
            </div>

            {{-- Se edita con la plantilla que la consulta tiene guardada, no
                 necesariamente la que esté activa hoy para la especialidad. --}}
            @if ($template)
                <div class="space-y-4 mt-4 pt-4 border-t border-gray-100 dark:border-gray-700" id="plantilla-fields">
                    <div class="flex justify-end mb-2">
                        <button type="button" class="toggle-all-details text-xs text-blue-600 dark:text-blue-400 underline">
                            Colapsar todo
                        </button>
                    </div>

                    @foreach ($template->sections as $section)
                        <details open class="border border-gray-100 dark:border-gray-700 rounded-lg p-3" data-grupo-visual="{{ $section->grupo_visual }}">
                            <summary class="cursor-pointer text-sm font-semibold text-gray-800 dark:text-white select-none">
                                {{ $section->title }}
                            </summary>

                            <div class="grid gap-4 sm:grid-cols-2 mt-3">
                                @foreach ($section->fields as $field)
                                    @php
                                        // Misma convención de puntaje automático que en "Registrar
                                        // consulta" (ver show.blade.php): Subtotal/Total por grupo_visual.
                                        $dataRole = null;
                                        $dataCategoria = null;
                                        if ($field->type === 'si_no') {
                                            $dataRole = 'si-no-item';
                                        } elseif ($field->type === 'numero' && str_starts_with($field->label, 'Subtotal')) {
                                            $dataRole = 'subtotal';
                                        } elseif ($field->type === 'numero' && str_starts_with($field->label, 'Total ')) {
                                            $dataRole = 'total-categoria';
                                            $dataCategoria = trim(substr($field->label, 6));
                                        } elseif ($field->type === 'numero' && trim($field->label) === 'Total') {
                                            $dataRole = 'total-costos';
                                        }
                                    @endphp
                                    <div class="{{ in_array($field->type, ['seleccion_multiple', 'tabla_repetible'], true) ? 'sm:col-span-2' : '' }}"
                                        @if ($dataRole) data-role="{{ $dataRole }}" @endif
                                        @if ($dataCategoria) data-categoria="{{ $dataCategoria }}" @endif>
                                        @include('admin.expedientes.partials._campo_dinamico', [
                                            'field' => $field,
                                            'value' => $consulta->data[$field->id] ?? null,
                                        ])
                                    </div>
                                @endforeach
                            </div>
                        </details>
                    @endforeach
                </div>
            @endif

            <div class="flex justify-end mt-4">
                <button type="submit" class="btn btn-green rounded-lg">
                    <i class="fa-solid fa-check mr-1"></i> Guardar cambios
                </button>
            </div>
        </form>
    </div>

    @push('js')
    <script>
        // "Expandir todo" / "Colapsar todo" (igual que en "Registrar consulta").
        document.querySelectorAll('.toggle-all-details').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const container = document.getElementById('plantilla-fields');
                const shouldOpen = btn.textContent.trim() === 'Expandir todo';

                container.querySelectorAll('details').forEach(function (d) {
                    d.open = shouldOpen;
                });

                btn.textContent = shouldOpen ? 'Colapsar todo' : 'Expandir todo';
            });
        });

        // Puntaje automático (ver show.blade.php para la explicación
        // completa de la convención Subtotal/Total + grupo_visual).
        function recalcularPuntajeSeccion(details) {
            const grupo = details.dataset.grupoVisual;
            if (!grupo) return;

            const siCount = Array.from(details.querySelectorAll('[data-role="si-no-item"] select'))
                .filter(function (select) { return select.value === 'Sí'; })
                .length;

            const subtotalInput = details.querySelector('[data-role="subtotal"] input');
            if (subtotalInput) subtotalInput.value = siCount;

            recalcularTotalCategoria(document.getElementById('plantilla-fields'), grupo);
        }

        function recalcularTotalCategoria(contenedor, grupo) {
            if (!contenedor || !grupo) return;

            let suma = 0;
            contenedor.querySelectorAll('details[data-grupo-visual="' + grupo + '"] [data-role="subtotal"] input').forEach(function (input) {
                suma += parseFloat(input.value) || 0;
            });

            contenedor.querySelectorAll('[data-role="total-categoria"]').forEach(function (wrapperDiv) {
                if ((wrapperDiv.dataset.categoria || '').toLowerCase() === grupo.toLowerCase()) {
                    const input = wrapperDiv.querySelector('input');
                    if (input) input.value = suma;
                }
            });
        }

        document.querySelectorAll('[data-role="si-no-item"] select').forEach(function (select) {
            select.addEventListener('change', function () {
                const details = select.closest('details[data-grupo-visual]');
                if (details) recalcularPuntajeSeccion(details);
            });
        });

        // Total de costos automático (ver show.blade.php para la
        // explicación completa: suma la columna "Costo" de la tabla
        // repetible y la escribe en el campo "Total" de esa sección).
        function recalcularTotalCostos(details) {
            const totalInput = details.querySelector('[data-role="total-costos"] input');
            if (!totalInput) return;

            let suma = 0;
            details.querySelectorAll('[data-columna-costo]').forEach(function (input) {
                suma += parseFloat(input.value) || 0;
            });

            totalInput.value = Math.round(suma * 100) / 100;
        }

        document.addEventListener('input', function (e) {
            if (! e.target.matches('[data-columna-costo]')) return;

            const details = e.target.closest('details');
            if (details) recalcularTotalCostos(details);
        });

        // Mismo manejo de filas repetibles que en "Registrar consulta".
        document.querySelectorAll('[data-repeatable-table]').forEach(function (wrapper) {
            const tbody = wrapper.querySelector('[data-rows-container]');
            const template = wrapper.querySelector('template[data-row-template]');
            let contadorFila = tbody.querySelectorAll('tr').length;

            wrapper.querySelector('[data-add-row]').addEventListener('click', function () {
                const html = template.innerHTML.replaceAll('__ROW__', contadorFila);
                tbody.insertAdjacentHTML('beforeend', html);
                contadorFila++;
            });

            wrapper.addEventListener('click', function (e) {
                const boton = e.target.closest('[data-remove-row]');
                if (! boton) return;

                const fila = boton.closest('tr');

                if (tbody.querySelectorAll('tr').length > 1) {
                    fila.remove();
                } else {
                    fila.querySelectorAll('input').forEach(function (input) { input.value = ''; });
                }

                const details = wrapper.closest('details');
                if (details) recalcularTotalCostos(details);
            });
        });
    </script>
    @endpush

</x-admin-layout>
