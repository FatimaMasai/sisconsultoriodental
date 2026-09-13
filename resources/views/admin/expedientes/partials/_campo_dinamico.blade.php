{{-- Un campo del formulario dinámico de una especialidad (ver
     FormField::TYPES). Recibe $field y, opcionalmente, $value (la respuesta
     ya guardada, al editar una consulta existente). old("data.{id}") tiene
     prioridad sobre $value: así, si el formulario vuelve por un error de
     validación, no se pierde lo que la persona ya había tipeado. --}}
@php
    $fieldName = "data[{$field->id}]";
    $oldValue = old("data.{$field->id}", $value ?? null);
    $columnas = $field->options ?? [];
@endphp

<div>
    <x-label class="form-label">
        {{ $field->label }}
        @if ($field->required)
            <span class="text-red-500">*</span>
        @endif
    </x-label>

    @switch($field->type)
        @case('texto')
            <x-input name="{{ $fieldName }}" value="{{ $oldValue }}" class="input-label rounded-lg w-full" />
            @break

        @case('numero')
            <x-input type="number" step="any" name="{{ $fieldName }}" value="{{ $oldValue }}" class="input-label rounded-lg w-full" />
            @break

        @case('fecha')
            <x-input type="date" name="{{ $fieldName }}" value="{{ $oldValue }}" class="input-label rounded-lg w-full" />
            @break

        @case('si_no')
            <x-select name="{{ $fieldName }}" class="input-label rounded-lg w-full">
                <option value="">—</option>
                <option value="Sí" @selected($oldValue === 'Sí')>Sí</option>
                <option value="No" @selected($oldValue === 'No')>No</option>
            </x-select>
            @break

        @case('seleccion_unica')
            <x-select name="{{ $fieldName }}" class="input-label rounded-lg w-full">
                <option value="">Seleccione una opción</option>
                @foreach ($columnas as $opcion)
                    <option value="{{ $opcion }}" @selected($oldValue === $opcion)>{{ $opcion }}</option>
                @endforeach
            </x-select>
            @break

        @case('seleccion_multiple')
            @php $oldValues = (array) $oldValue; @endphp
            <div class="flex flex-wrap gap-3 mt-1">
                @foreach ($columnas as $opcion)
                    <label class="inline-flex items-center gap-1 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="{{ $fieldName }}[]" value="{{ $opcion }}"
                            @checked(in_array($opcion, $oldValues, true)) class="rounded border-gray-300">
                        {{ $opcion }}
                    </label>
                @endforeach
            </div>
            @break

        @case('tabla_repetible')
            @php
                // Cada columna puede ser un texto simple (compatibilidad con
                // tablas viejas: se muestra como texto) o un array
                // ['label' => ..., 'type' => 'texto'|'numero'|'fecha'] para
                // que el campo tenga el input correcto (ej. una columna
                // "Fecha" con selector de fecha en vez de texto libre).
                $columnasTabla = collect($columnas)->map(function ($col) {
                    return is_array($col) ? $col : ['label' => $col, 'type' => 'texto'];
                });
                $tiposInputHtml = ['texto' => 'text', 'numero' => 'number', 'fecha' => 'date'];
                $oldRows = is_array($oldValue) && ! empty($oldValue) ? $oldValue : [array_fill(0, $columnasTabla->count(), '')];
            @endphp
            <div data-repeatable-table class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                @foreach ($columnasTabla as $columna)
                                    <th class="px-2 py-1 text-left text-xs text-gray-600 dark:text-gray-300">{{ $columna['label'] }}</th>
                                @endforeach
                                <th class="w-8"></th>
                            </tr>
                        </thead>
                        <tbody data-rows-container>
                            @foreach ($oldRows as $rowIndex => $fila)
                                <tr>
                                    @foreach ($columnasTabla as $colIndex => $columna)
                                        <td class="px-2 py-1">
                                            <input type="{{ $tiposInputHtml[$columna['type']] ?? 'text' }}"
                                                @if (($columna['type'] ?? 'texto') === 'numero') step="any" @endif
                                                @if (strtolower(trim($columna['label'])) === 'costo') data-columna-costo @endif
                                                name="data[{{ $field->id }}][{{ $rowIndex }}][{{ $colIndex }}]"
                                                value="{{ $fila[$colIndex] ?? '' }}"
                                                class="w-full text-sm rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                        </td>
                                    @endforeach
                                    <td class="px-1">
                                        <button type="button" data-remove-row class="text-red-500 text-xs" title="Quitar fila">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button type="button" data-add-row class="btn btn-gray text-xs m-2">
                    <i class="fa-solid fa-plus mr-1"></i> Agregar fila
                </button>

                <template data-row-template>
                    <tr>
                        @foreach ($columnasTabla as $colIndex => $columna)
                            <td class="px-2 py-1">
                                <input type="{{ $tiposInputHtml[$columna['type']] ?? 'text' }}"
                                    @if (($columna['type'] ?? 'texto') === 'numero') step="any" @endif
                                    @if (strtolower(trim($columna['label'])) === 'costo') data-columna-costo @endif
                                    name="data[{{ $field->id }}][__ROW__][{{ $colIndex }}]"
                                    class="w-full text-sm rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            </td>
                        @endforeach
                        <td class="px-1">
                            <button type="button" data-remove-row class="text-red-500 text-xs" title="Quitar fila">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </td>
                    </tr>
                </template>
            </div>
            @break

        @default
            <p class="text-xs text-red-500">Tipo de campo no soportado todavía ({{ $field->type }}).</p>
    @endswitch

    @error("data.{$field->id}")
        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
    @enderror

    @if ($field->help_text)
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $field->help_text }}</p>
    @endif
</div>
