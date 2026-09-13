<x-admin-layout>

    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
        <x-label class="text-black dark:text-white text-xl font-semibold">
            <i class="fa-solid fa-folder-open text-gray-400 mr-1"></i>
            Expediente de {{ $expediente->speciality->name ?? 'especialidad' }}
        </x-label>

        <div class="flex flex-wrap items-center gap-2">
            @can('admin.histories.show')
                {{-- El odontograma es específico de Odontología: no aplica al
                     resto de las especialidades (Medicina Ortomolecular,
                     Nutrición, etc.), así que el botón solo se muestra ahí
                     (ver también authorizeOdontograma en ExpedienteController,
                     que bloquea el acceso por URL directa igual). --}}
                @if ($expediente->speciality?->name === 'Odontología General')
                    <a href="{{ route('admin.expedientes.odontograma.index', $expediente) }}" class="btn btn-blue rounded-lg text-sm">
                        <i class="fa-solid fa-tooth mr-1"></i> Odontograma
                    </a>
                @endif
                <a href="{{ route('admin.expedientes.recetas.index', $expediente) }}" class="btn btn-orange rounded-lg text-sm">
                    <i class="fa-solid fa-prescription mr-1"></i> Historial de recetas
                </a>
            @endcan

            <a href="{{ route('admin.expedientes.index') }}" class="btn btn-gray rounded-lg text-sm">
                <i class="fa-solid fa-arrow-left mr-1"></i> Volver
            </a>
        </div>
    </div>

    {{-- Datos del paciente: lo esencial siempre a la vista, sin tener que ir a otra pantalla --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
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
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Alergias</p>
                <p class="font-semibold text-gray-900 dark:text-white">{{ $expediente->patient->allergy ?: '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Antecedentes</p>
                <p class="font-semibold text-gray-900 dark:text-white">{{ $expediente->patient->medical_history ?: '—' }}</p>
            </div>
        </div>
    </div>

    {{-- Registrar una consulta nueva --}}
    @can('admin.histories.create')
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
            <x-label class="text-black dark:text-white text-base font-semibold mb-3 block">
                <i class="fa-solid fa-plus text-gray-400 mr-1"></i>
                Registrar consulta
            </x-label>

            <x-validation-errors class="mb-4" />

            <form action="{{ route('admin.expedientes.consultas.store', $expediente) }}" method="POST">
                @csrf

                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <x-label class="form-label">Atendido por <span class="text-red-500">*</span></x-label>

                        @if ($doctorActual)
                            {{-- Un doctor logueado siempre registra la consulta a su
                                 propio nombre: no hay nada que elegir. --}}
                            <input type="hidden" name="doctor_id" value="{{ $doctorActual->id }}">
                            <p class="input-label rounded-lg w-full bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 flex items-center">
                                {{ $doctorActual->person->name }} {{ $doctorActual->person->last_name_father }}
                            </p>
                        @else
                            <x-select name="doctor_id" class="input-label rounded-lg w-full">
                                <option value="">Seleccione un doctor</option>
                                @foreach ($doctoresDeEspecialidad as $doc)
                                    <option value="{{ $doc->id }}" @selected(old('doctor_id') == $doc->id)>
                                        {{ $doc->person->name }} {{ $doc->person->last_name_father }}
                                    </option>
                                @endforeach
                            </x-select>
                        @endif
                    </div>
                    <div>
                        <x-label class="form-label">Fecha <span class="text-red-500">*</span></x-label>
                        <x-input type="date" name="date" value="{{ old('date', now()->format('Y-m-d')) }}"
                            class="input-label rounded-lg w-full" max="{{ now()->format('Y-m-d') }}" />
                    </div>
                    <div class="sm:col-span-3">
                        <x-label class="form-label">Descripción</x-label>
                        <x-input name="description" value="{{ old('description') }}" class="input-label rounded-lg w-full"
                            placeholder="Ej: control, limpieza, primera consulta..." />
                    </div>
                </div>

                {{-- Campos propios de la especialidad, definidos desde
                     Especialidad → Plantilla. Si todavía no se armó ninguna
                     plantilla para esta especialidad, la consulta se
                     registra igual, solo con los datos fijos de arriba.
                     Si hay más de una plantilla activa (ej. Medicina
                     Ortomolecular: Historia Clínica Funcional + Perfil
                     Neurofuncional), se elige cuál completar; los campos de
                     las demás quedan ocultos y no se envían con valor. --}}
                @if ($activeTemplates->isNotEmpty())
                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                        @if ($activeTemplates->count() > 1)
                            <div class="mb-5">
                                <x-label class="form-label">Formulario a completar <span class="text-red-500">*</span></x-label>
                                <div class="grid gap-3 sm:grid-cols-2 max-w-xl mt-1">
                                    @foreach ($activeTemplates as $tpl)
                                        <label class="cursor-pointer">
                                            <input type="radio" name="form_template_id" value="{{ $tpl->id }}" class="peer sr-only form-template-radio"
                                                @checked((int) old('form_template_id', $activeTemplates->first()->id) === $tpl->id)>
                                            <div class="border-2 border-gray-200 dark:border-gray-600 rounded-lg p-3 text-center transition peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/10">
                                                <i class="fa-solid fa-file-medical text-gray-400 mb-1"></i>
                                                <p class="font-medium text-gray-900 dark:text-white text-sm">{{ $tpl->name }}</p>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @foreach ($activeTemplates as $tpl)
                            <div class="form-template-fields" data-form-template-id="{{ $tpl->id }}"
                                @if ($activeTemplates->count() > 1 && (int) old('form_template_id', $activeTemplates->first()->id) !== $tpl->id) hidden @endif>
                                <div class="flex justify-end mb-2">
                                    <button type="button" class="toggle-all-details text-xs text-blue-600 dark:text-blue-400 underline">
                                        Expandir todo
                                    </button>
                                </div>

                                <div class="space-y-4">
                                    @foreach ($tpl->sections as $section)
                                        <details class="border border-gray-100 dark:border-gray-700 rounded-lg p-3" data-grupo-visual="{{ $section->grupo_visual }}">
                                            <summary class="cursor-pointer text-sm font-semibold text-gray-800 dark:text-white select-none">
                                                {{ $section->title }}
                                            </summary>

                                            <div class="grid gap-4 sm:grid-cols-2 mt-3">
                                                @foreach ($section->fields as $field)
                                                    @php
                                                        // Convención para puntaje automático (ver Perfil
                                                        // Neurofuncional): dentro de una sección con
                                                        // grupo_visual, un campo "numero" cuya etiqueta
                                                        // empieza con "Subtotal" se autocompleta contando
                                                        // las respuestas "Sí" de esa misma sección; uno que
                                                        // empieza con "Total " se autocompleta sumando los
                                                        // subtotales de todas las secciones con el mismo
                                                        // grupo_visual. Aparte, sin relación con lo anterior:
                                                        // un campo "numero" llamado exactamente "Total" (ej.
                                                        // Historia Clínica Funcional > Procedimientos y
                                                        // Costos) se autocompleta sumando la columna "Costo"
                                                        // de la tabla repetible de esa misma sección.
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
                                                        @include('admin.expedientes.partials._campo_dinamico', ['field' => $field])
                                                    </div>
                                                @endforeach
                                            </div>
                                        </details>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="flex justify-end mt-4">
                    <button type="submit" class="btn btn-green rounded-lg">
                        <i class="fa-solid fa-check mr-1"></i> Registrar
                    </button>
                </div>
            </form>
        </div>
    @endcan

    {{-- Línea de tiempo de consultas --}}
    <x-label class="text-black dark:text-white text-base font-semibold mb-3 block">
        Consultas ({{ $expediente->consultas->count() }})
    </x-label>

    @if ($expediente->consultas->isEmpty())
        <div class="flex items-center p-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
            <span class="font-medium">Info alert!</span>&nbsp;Este expediente todavía no tiene consultas registradas.
        </div>
    @endif

    <div class="space-y-4">
        @foreach ($expediente->consultas as $consulta)
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-900 dark:text-white flex flex-wrap items-center gap-2">
                            {{ \Carbon\Carbon::parse($consulta->date)->format('d/m/Y') }}
                            {{-- Para especialidades con más de un formulario (ej. Medicina
                                 Ortomolecular: Historia Clínica Funcional + Perfil
                                 Neurofuncional), esta etiqueta deja claro de un vistazo cuál
                                 se completó en esta consulta, sin tener que abrir "Formulario
                                 completado". --}}
                            @if ($consulta->formTemplate)
                                <span class="text-xs font-normal bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 rounded-full px-2 py-0.5">
                                    <i class="fa-solid fa-file-medical mr-1"></i>{{ $consulta->formTemplate->name }}
                                </span>
                            @endif
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                            Atendido por {{ $consulta->doctor->person->name ?? '—' }} {{ $consulta->doctor->person->last_name_father ?? '' }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        @if ($consulta->sale_id)
                            <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-full px-2 py-1">
                                <i class="fa-solid fa-cash-register mr-1"></i> Venta #{{ $consulta->sale_id }}
                            </span>
                        @endif

                        @can('admin.histories.create')
                            <a href="{{ route('admin.consultas.edit', $consulta) }}" class="btn btn-blue text-xs" title="Editar esta consulta">
                                <i class="fa-solid fa-pen mr-1"></i> Editar
                            </a>
                        @endcan

                        <a href="{{ route('admin.consultas.pdf', $consulta) }}" target="_blank" class="btn btn-gray text-xs" title="Imprimir esta consulta">
                            <i class="fa-solid fa-print mr-1"></i> Imprimir
                        </a>
                    </div>
                </div>

                @if ($consulta->description)
                    <p class="text-gray-700 dark:text-gray-300 mt-2">{{ $consulta->description }}</p>
                @endif

                @if ($consulta->formTemplate && ! empty($consulta->data))
                    <details class="mt-3">
                        <summary class="cursor-pointer text-sm text-blue-600 dark:text-blue-400 select-none">
                            Formulario completado: {{ $consulta->formTemplate->name }}
                        </summary>

                        <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700 space-y-4">
                            @foreach ($consulta->formTemplate->sections as $section)
                                @php
                                    $camposConValor = $section->fields->filter(function ($field) use ($consulta) {
                                        $valor = $consulta->data[$field->id] ?? null;
                                        return $valor !== null && $valor !== '' && $valor !== [];
                                    });
                                @endphp

                                @if ($camposConValor->isNotEmpty())
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">{{ $section->title }}</p>

                                        <dl class="grid gap-2 sm:grid-cols-2">
                                            @foreach ($camposConValor as $field)
                                                @php $valor = $consulta->data[$field->id]; @endphp
                                                <div>
                                                    <dt class="text-xs text-gray-500 dark:text-gray-400">{{ $field->label }}</dt>
                                                    <dd class="text-sm text-gray-800 dark:text-white">
                                                        @if ($field->type === 'tabla_repetible')
                                                            <div class="overflow-x-auto">
                                                                <table class="text-xs w-full">
                                                                    <thead>
                                                                        <tr>
                                                                            @foreach ($field->options ?? [] as $columna)
                                                                                <th class="text-left pr-2">{{ $columna }}</th>
                                                                            @endforeach
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach ($valor as $fila)
                                                                            <tr>
                                                                                @foreach ($field->options ?? [] as $colIndex => $columna)
                                                                                    <td class="pr-2">{{ $fila[$colIndex] ?? '' }}</td>
                                                                                @endforeach
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        @elseif (is_array($valor))
                                                            {{ implode(', ', $valor) }}
                                                        @else
                                                            {{ $valor }}
                                                        @endif
                                                    </dd>
                                                </div>
                                            @endforeach
                                        </dl>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </details>
                @endif

                {{-- Notas y fotos quedan colapsadas por defecto: en el celular la
                     lista de consultas no se hace eterna, y se abre solo la que
                     interesa. --}}
                <details class="mt-3">
                    <summary class="cursor-pointer text-sm text-blue-600 dark:text-blue-400 select-none">
                        Notas, fotos y recetas ({{ $consulta->notes->count() }} nota(s), {{ $consulta->photos->count() }} foto(s), {{ $consulta->recetas->count() }} receta(s))
                    </summary>

                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 space-y-6">
                        {{-- Fotos --}}
                        <div>
                            <p class="text-sm font-semibold text-gray-800 dark:text-white mb-3">
                                <i class="fa-solid fa-camera text-gray-400 mr-1"></i> Fotos
                            </p>

                            <div class="grid gap-4 sm:grid-cols-2">
                                @foreach (['antes' => 'Antes', 'despues' => 'Después'] as $type => $label)
                                    <div>
                                        <div class="flex items-center justify-between mb-2">
                                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</h4>

                                            @can('admin.histories.photos.store')
                                                <label for="photo-input-{{ $type }}-{{ $consulta->id }}" class="btn btn-blue text-xs cursor-pointer">
                                                    <i class="fa-solid fa-upload mr-1"></i> Subir
                                                </label>
                                                <form action="{{ route('admin.consultas.photos.store', $consulta) }}" method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    <input type="hidden" name="type" value="{{ $type }}">
                                                    <input type="file" id="photo-input-{{ $type }}-{{ $consulta->id }}" name="photos[]"
                                                        accept="image/*" multiple class="hidden"
                                                        onchange="if (this.files.length) this.form.submit()">
                                                </form>
                                            @endcan
                                        </div>

                                        @php $photosOfType = $consulta->photos->where('type', $type); @endphp

                                        @if ($photosOfType->count())
                                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                                                @foreach ($photosOfType as $photo)
                                                    <div class="relative group">
                                                        <button type="button" onclick="openPhotoLightbox('{{ $photo->url }}')" class="block w-full">
                                                            <img src="{{ $photo->url }}" alt="Foto {{ $label }}"
                                                                class="w-full h-20 object-cover rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer hover:opacity-90">
                                                        </button>

                                                        @can('admin.histories.photos.destroy')
                                                            <form class="delete-form absolute top-1 right-1"
                                                                action="{{ route('admin.consultas.photos.destroy', $photo->id) }}" method="POST">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="w-6 h-6 flex items-center justify-center rounded-full bg-red-600 text-white text-xs hover:bg-red-700">
                                                                    <i class="fa-solid fa-xmark"></i>
                                                                </button>
                                                            </form>
                                                        @endcan
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Sin fotos de "{{ $label }}".</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Notas --}}
                        <div>
                            <p class="text-sm font-semibold text-gray-800 dark:text-white mb-3">
                                <i class="fa-solid fa-note-sticky text-gray-400 mr-1"></i> Notas
                            </p>

                            @can('admin.histories.addNote')
                                <form action="{{ route('admin.consultas.addNote', $consulta) }}" method="POST" class="mb-4">
                                    @csrf
                                    <textarea name="note" rows="2" required
                                        class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 shadow-sm w-full text-sm"
                                        placeholder="Agregar una nota a esta consulta..."></textarea>
                                    <div class="flex justify-end mt-2">
                                        <button type="submit" class="btn btn-green rounded-lg text-xs">
                                            <i class="fa-solid fa-check mr-1"></i> Guardar nota
                                        </button>
                                    </div>
                                </form>
                            @endcan

                            @if ($consulta->notes->isNotEmpty())
                                <div class="space-y-2">
                                    @foreach ($consulta->notes->sortByDesc('created_at') as $note)
                                        <div class="p-3 bg-gray-50 dark:bg-gray-900 border-l-4 border-green-500 rounded-lg">
                                            <p class="text-sm text-gray-800 dark:text-white">{{ $note->note }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                <i class="fa-regular fa-clock mr-1"></i>{{ $note->created_at->format('d/m/Y H:i') }}
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-xs text-gray-500 dark:text-gray-400">Todavía no hay notas en esta consulta.</p>
                            @endif
                        </div>

                        {{-- Recetas --}}
                        <div>
                            <p class="text-sm font-semibold text-gray-800 dark:text-white mb-3">
                                <i class="fa-solid fa-prescription text-gray-400 mr-1"></i> Recetas
                            </p>

                            @can('admin.histories.recetas.store')
                                <form action="{{ route('admin.consultas.recetas.store', $consulta) }}" method="POST" class="mb-4">
                                    @csrf
                                    <textarea name="contenido" rows="3" required
                                        class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 shadow-sm w-full text-sm"
                                        placeholder="Escriba la receta: medicamento, dosis, frecuencia, indicaciones..."></textarea>
                                    <div class="flex justify-end mt-2">
                                        <button type="submit" class="btn btn-green rounded-lg text-xs">
                                            <i class="fa-solid fa-check mr-1"></i> Guardar receta
                                        </button>
                                    </div>
                                </form>
                            @endcan

                            @if ($consulta->recetas->isNotEmpty())
                                <div class="space-y-2">
                                    @foreach ($consulta->recetas->sortByDesc('created_at') as $receta)
                                        <div class="p-3 bg-gray-50 dark:bg-gray-900 border-l-4 border-purple-500 rounded-lg" id="receta-view-{{ $receta->id }}">
                                            <p class="text-sm text-gray-800 dark:text-white whitespace-pre-line">{{ $receta->contenido }}</p>
                                            <div class="flex flex-wrap items-center justify-between gap-2 mt-2">
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    <i class="fa-regular fa-clock mr-1"></i>{{ $receta->created_at->format('d/m/Y H:i') }}
                                                </p>
                                                <div class="flex items-center gap-2">
                                                    <a href="{{ route('admin.recetas.pdf', $receta) }}" target="_blank" class="btn btn-gray text-xs">
                                                        <i class="fa-solid fa-print mr-1"></i> Imprimir
                                                    </a>
                                                    @can('admin.histories.recetas.store')
                                                        <button type="button" onclick="toggleRecetaEdit({{ $receta->id }})" class="btn btn-blue text-xs">
                                                            <i class="fa-solid fa-pen mr-1"></i> Editar
                                                        </button>
                                                    @endcan
                                                    @can('admin.histories.recetas.destroy')
                                                        <form class="delete-form" action="{{ route('admin.recetas.destroy', $receta) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-red text-xs">
                                                                <i class="fa-solid fa-trash-can mr-1"></i> Eliminar
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            </div>
                                        </div>

                                        @can('admin.histories.recetas.store')
                                            <form id="receta-edit-{{ $receta->id }}" action="{{ route('admin.recetas.update', $receta) }}" method="POST"
                                                class="hidden p-3 bg-gray-50 dark:bg-gray-900 border-l-4 border-purple-500 rounded-lg">
                                                @csrf
                                                @method('PUT')
                                                <textarea name="contenido" rows="3" required
                                                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 shadow-sm w-full text-sm">{{ $receta->contenido }}</textarea>
                                                <div class="flex justify-end gap-2 mt-2">
                                                    <button type="button" onclick="toggleRecetaEdit({{ $receta->id }})" class="btn btn-gray rounded-lg text-xs">
                                                        Cancelar
                                                    </button>
                                                    <button type="submit" class="btn btn-green rounded-lg text-xs">
                                                        Guardar cambios
                                                    </button>
                                                </div>
                                            </form>
                                        @endcan
                                    @endforeach
                                </div>
                            @else
                                <p class="text-xs text-gray-500 dark:text-gray-400">Todavía no hay recetas en esta consulta.</p>
                            @endif
                        </div>
                    </div>
                </details>
            </div>
        @endforeach
    </div>

    {{-- Visor de foto en grande --}}
    <div id="photo-lightbox" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 p-4" onclick="closePhotoLightbox()">
        <button type="button" onclick="closePhotoLightbox()" class="absolute top-4 right-4 w-10 h-10 flex items-center justify-center rounded-full bg-white/90 text-gray-800 text-xl hover:bg-white">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <img id="photo-lightbox-img" src="" alt="Foto ampliada" class="max-h-full max-w-full rounded-lg" onclick="event.stopPropagation()">
    </div>

    @push('js')
    <script>
        // Cuando la especialidad tiene más de una plantilla activa (ej.
        // Medicina Ortomolecular: Historia Clínica Funcional + Perfil
        // Neurofuncional), estas tarjetas muestran solo los campos del
        // formulario elegido; los demás quedan ocultos (y no se envían con
        // valor, salvo lo que ya tenían de un intento anterior fallido).
        document.querySelectorAll('.form-template-radio').forEach(function (radio) {
            radio.addEventListener('change', function () {
                document.querySelectorAll('.form-template-fields').forEach(function (div) {
                    div.hidden = div.dataset.formTemplateId !== radio.value;
                });
            });
        });

        // "Expandir todo" / "Colapsar todo": los formularios largos (más de
        // 80 campos en algunos casos) arrancan con las secciones cerradas
        // para que la pantalla no sea kilométrica; este botón las abre o
        // cierra todas juntas sin tener que ir sección por sección.
        document.querySelectorAll('.toggle-all-details').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const container = btn.closest('.form-template-fields');
                const shouldOpen = btn.textContent.trim() === 'Expandir todo';

                container.querySelectorAll('details').forEach(function (d) {
                    d.open = shouldOpen;
                });

                btn.textContent = shouldOpen ? 'Colapsar todo' : 'Expandir todo';
            });
        });

        // Puntaje automático (ej. Perfil Neurofuncional): cada sección con
        // grupo_visual cuenta cuántas respuestas "Sí" tiene y escribe ese
        // número en su propio campo "Subtotal"; el resultado final suma
        // esos subtotales entre todas las secciones de la misma categoría
        // (mismo grupo_visual) en el campo "Total {categoría}". Los campos
        // quedan editables por si el profesional necesita corregir un
        // valor a mano; esto solo asiste, no reemplaza el criterio clínico.
        function recalcularPuntajeSeccion(details) {
            const grupo = details.dataset.grupoVisual;
            if (!grupo) return;

            const siCount = Array.from(details.querySelectorAll('[data-role="si-no-item"] select'))
                .filter(function (select) { return select.value === 'Sí'; })
                .length;

            const subtotalInput = details.querySelector('[data-role="subtotal"] input');
            if (subtotalInput) subtotalInput.value = siCount;

            recalcularTotalCategoria(details.closest('.form-template-fields'), grupo);
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

        // Total de costos automático (ej. Historia Clínica Funcional >
        // Procedimientos y Costos): suma la columna "Costo" de la tabla
        // repetible de la sección y la escribe en su campo "Total". No
        // tiene relación con el puntaje de arriba (ese cuenta "Sí"; esto
        // suma dinero). Sigue editable por si hace falta corregirlo a mano.
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

        function openPhotoLightbox(url) {
            const lightbox = document.getElementById('photo-lightbox');
            document.getElementById('photo-lightbox-img').src = url;
            lightbox.classList.remove('hidden');
            lightbox.classList.add('flex');
        }

        function closePhotoLightbox() {
            const lightbox = document.getElementById('photo-lightbox');
            lightbox.classList.add('hidden');
            lightbox.classList.remove('flex');
            document.getElementById('photo-lightbox-img').src = '';
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closePhotoLightbox();
        });

        // Muestra/oculta el formulario para corregir una receta ya guardada,
        // sin necesidad de ir a otra pantalla.
        function toggleRecetaEdit(id) {
            const vista = document.getElementById('receta-view-' + id);
            const form = document.getElementById('receta-edit-' + id);
            if (!vista || !form) return;

            vista.classList.toggle('hidden');
            form.classList.toggle('hidden');
        }

        // Campos "tabla repetible" del formulario dinámico (ej: Recordatorio
        // de 24 horas): agregar/quitar filas sin recargar la página.
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
                    // No se borra la última fila, se limpia para no dejar la tabla vacía.
                    fila.querySelectorAll('input').forEach(function (input) { input.value = ''; });
                }

                // Si esta tabla tiene una columna "Costo" con total automático,
                // quitar/limpiar una fila también tiene que actualizar el Total.
                const details = wrapper.closest('details');
                if (details) recalcularTotalCostos(details);
            });
        });
    </script>
    @endpush

</x-admin-layout>
