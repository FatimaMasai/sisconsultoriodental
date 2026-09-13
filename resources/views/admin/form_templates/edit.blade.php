<x-admin-layout>

    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
        <x-label class="text-black dark:text-white text-xl font-semibold">
            <i class="fa-solid fa-file-pen text-gray-400 mr-1"></i>
            Plantilla de historial — {{ $speciality->name }}
        </x-label>

        <a href="{{ route('admin.specialities.index') }}" class="btn btn-gray rounded-lg text-sm">
            <i class="fa-solid fa-arrow-left mr-1"></i> Volver
        </a>
    </div>

    <x-validation-errors class="mb-4" />

    @if (! $template)
        {{-- Todavía no existe ninguna plantilla para esta especialidad: se
             crea una sola vez, después solo se le agregan secciones y campos. --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <p class="text-gray-600 dark:text-gray-300 mb-4">
                Esta especialidad todavía no tiene una plantilla de historial. Creala para
                empezar a definir qué secciones y campos se llenan al registrar una consulta.
            </p>

            <form action="{{ route('admin.specialities.plantilla.store', $speciality) }}" method="POST" class="flex flex-wrap gap-3 items-end">
                @csrf
                <div class="flex-1 min-w-[220px]">
                    <x-label class="form-label">Nombre de la plantilla</x-label>
                    <x-input name="name" value="{{ old('name', 'Historial de ' . $speciality->name) }}" class="input-label rounded-lg w-full" />
                </div>
                <button type="submit" class="btn btn-green rounded-lg">
                    <i class="fa-solid fa-plus mr-1"></i> Crear plantilla
                </button>
            </form>
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
            <p class="font-semibold text-gray-900 dark:text-white">{{ $template->name }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Versión {{ $template->version }} · Activa</p>
        </div>

        {{-- Secciones, cada una con sus campos adentro. Se puede reordenar
             con las flechas (intercambia el "order" con el vecino) en vez de
             un drag&drop, para no sumar una librería nueva solo para esto. --}}
        <div class="space-y-4 mb-6">
            @forelse ($template->sections as $section)
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <div class="flex flex-wrap items-start justify-between gap-2 mb-3">
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $section->title }}</p>
                            @if ($section->grupo_visual)
                                <p class="text-xs text-gray-500 dark:text-gray-400">Grupo visual: {{ $section->grupo_visual }}</p>
                            @endif
                        </div>

                        <div class="flex items-center gap-1 shrink-0">
                            <form action="{{ route('admin.form_sections.move', $section) }}" method="POST">
                                @csrf
                                <input type="hidden" name="direction" value="up">
                                <button type="submit" class="btn btn-gray text-xs" title="Subir sección"><i class="fa-solid fa-arrow-up"></i></button>
                            </form>
                            <form action="{{ route('admin.form_sections.move', $section) }}" method="POST">
                                @csrf
                                <input type="hidden" name="direction" value="down">
                                <button type="submit" class="btn btn-gray text-xs" title="Bajar sección"><i class="fa-solid fa-arrow-down"></i></button>
                            </form>
                            <form class="delete-form" action="{{ route('admin.form_sections.destroy', $section) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-red text-xs" title="Eliminar sección"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </div>

                    <details class="mb-4">
                        <summary class="cursor-pointer text-sm text-blue-600 dark:text-blue-400 select-none">Editar sección</summary>

                        <form action="{{ route('admin.form_sections.update', $section) }}" method="POST" class="grid gap-3 sm:grid-cols-2 mt-3">
                            @csrf
                            @method('PUT')
                            <div>
                                <x-label class="form-label">Título</x-label>
                                <x-input name="title" value="{{ $section->title }}" class="input-label rounded-lg w-full" />
                            </div>
                            <div>
                                <x-label class="form-label">Grupo visual (opcional)</x-label>
                                <x-input name="grupo_visual" value="{{ $section->grupo_visual }}" class="input-label rounded-lg w-full" />
                            </div>
                            <div class="sm:col-span-2 flex justify-end">
                                <button type="submit" class="btn btn-green rounded-lg text-sm">Guardar</button>
                            </div>
                        </form>
                    </details>

                    <div class="space-y-2">
                        @forelse ($section->fields as $field)
                            <div class="border border-gray-100 dark:border-gray-700 rounded-lg p-3">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-800 dark:text-white">
                                            {{ $field->label }}
                                            @if ($field->required)
                                                <span class="text-red-500">*</span>
                                            @endif
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ \App\Models\FormField::TYPES[$field->type] ?? $field->type }}
                                            @if ($field->help_text)
                                                · {{ $field->help_text }}
                                            @endif
                                        </p>
                                    </div>

                                    <div class="flex items-center gap-1 shrink-0">
                                        <form action="{{ route('admin.form_fields.move', $field) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="direction" value="up">
                                            <button type="submit" class="btn btn-gray text-xs" title="Subir campo"><i class="fa-solid fa-arrow-up"></i></button>
                                        </form>
                                        <form action="{{ route('admin.form_fields.move', $field) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="direction" value="down">
                                            <button type="submit" class="btn btn-gray text-xs" title="Bajar campo"><i class="fa-solid fa-arrow-down"></i></button>
                                        </form>
                                        <form class="delete-form" action="{{ route('admin.form_fields.destroy', $field) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-red text-xs" title="Eliminar campo"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </div>
                                </div>

                                <details class="mt-2">
                                    <summary class="cursor-pointer text-xs text-blue-600 dark:text-blue-400 select-none">Editar campo</summary>

                                    <form action="{{ route('admin.form_fields.update', $field) }}" method="POST" class="grid gap-3 sm:grid-cols-2 mt-3">
                                        @csrf
                                        @method('PUT')

                                        <div>
                                            <x-label class="form-label">Etiqueta</x-label>
                                            <x-input name="label" value="{{ $field->label }}" class="input-label rounded-lg w-full" />
                                        </div>

                                        <div>
                                            <x-label class="form-label">Tipo</x-label>
                                            <x-select name="type" class="input-label rounded-lg w-full" data-field-type-select>
                                                @foreach (\App\Models\FormField::TYPES as $value => $labelTipo)
                                                    <option value="{{ $value }}" @selected($field->type === $value)>{{ $labelTipo }}</option>
                                                @endforeach
                                            </x-select>
                                        </div>

                                        <div data-options-wrapper class="sm:col-span-2 {{ in_array($field->type, \App\Models\FormField::TYPES_CON_OPCIONES, true) ? '' : 'hidden' }}">
                                            <x-label class="form-label">Opciones (una por línea)</x-label>
                                            <textarea name="options" rows="3"
                                                class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm w-full text-sm">{{ implode("\n", $field->options ?? []) }}</textarea>
                                        </div>

                                        <div>
                                            <x-label class="form-label">Ayuda (opcional)</x-label>
                                            <x-input name="help_text" value="{{ $field->help_text }}" class="input-label rounded-lg w-full" />
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <input type="checkbox" name="required" value="1" id="required-{{ $field->id }}" @checked($field->required) class="rounded border-gray-300">
                                            <label for="required-{{ $field->id }}" class="text-sm text-gray-700 dark:text-gray-300">Obligatorio</label>
                                        </div>

                                        <div class="sm:col-span-2 flex justify-end">
                                            <button type="submit" class="btn btn-green rounded-lg text-sm">Guardar</button>
                                        </div>
                                    </form>
                                </details>
                            </div>
                        @empty
                            <p class="text-xs text-gray-500 dark:text-gray-400">Esta sección todavía no tiene campos.</p>
                        @endforelse
                    </div>

                    <details class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <summary class="cursor-pointer text-sm text-blue-600 dark:text-blue-400 select-none">
                            <i class="fa-solid fa-plus mr-1"></i> Agregar campo
                        </summary>

                        <form action="{{ route('admin.form_sections.fields.store', $section) }}" method="POST" class="grid gap-3 sm:grid-cols-2 mt-3">
                            @csrf

                            <div>
                                <x-label class="form-label">Etiqueta</x-label>
                                <x-input name="label" class="input-label rounded-lg w-full" placeholder="Ej: Peso (kg)" />
                            </div>

                            <div>
                                <x-label class="form-label">Tipo</x-label>
                                <x-select name="type" class="input-label rounded-lg w-full" data-field-type-select>
                                    <option value="">Seleccione un tipo</option>
                                    @foreach (\App\Models\FormField::TYPES as $value => $labelTipo)
                                        <option value="{{ $value }}">{{ $labelTipo }}</option>
                                    @endforeach
                                </x-select>
                            </div>

                            <div data-options-wrapper class="sm:col-span-2 hidden">
                                <x-label class="form-label">Opciones (una por línea)</x-label>
                                <textarea name="options" rows="3"
                                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm w-full text-sm"
                                    placeholder="Bajo&#10;Normal&#10;Alto"></textarea>
                            </div>

                            <div>
                                <x-label class="form-label">Ayuda (opcional)</x-label>
                                <x-input name="help_text" class="input-label rounded-lg w-full" />
                            </div>

                            <div class="flex items-center gap-2">
                                <input type="checkbox" name="required" value="1" id="required-new-{{ $section->id }}" class="rounded border-gray-300">
                                <label for="required-new-{{ $section->id }}" class="text-sm text-gray-700 dark:text-gray-300">Obligatorio</label>
                            </div>

                            <div class="sm:col-span-2 flex justify-end">
                                <button type="submit" class="btn btn-green rounded-lg text-sm">
                                    <i class="fa-solid fa-check mr-1"></i> Agregar campo
                                </button>
                            </div>
                        </form>
                    </details>
                </div>
            @empty
                <div class="flex items-center p-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
                    <span class="font-medium">Info alert!</span>&nbsp;Esta plantilla todavía no tiene secciones.
                </div>
            @endforelse
        </div>

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
            <x-label class="text-black dark:text-white text-base font-semibold mb-3 block">
                <i class="fa-solid fa-plus text-gray-400 mr-1"></i> Agregar sección
            </x-label>

            <form action="{{ route('admin.form_templates.sections.store', $template) }}" method="POST" class="grid gap-3 sm:grid-cols-2">
                @csrf
                <div>
                    <x-label class="form-label">Título</x-label>
                    <x-input name="title" class="input-label rounded-lg w-full" placeholder="Ej: Datos antropométricos" />
                </div>
                <div>
                    <x-label class="form-label">Grupo visual (opcional)</x-label>
                    <x-input name="grupo_visual" class="input-label rounded-lg w-full" />
                </div>
                <div class="sm:col-span-2 flex justify-end">
                    <button type="submit" class="btn btn-green rounded-lg">
                        <i class="fa-solid fa-check mr-1"></i> Agregar sección
                    </button>
                </div>
            </form>
        </div>
    @endif

    @push('js')
    <script>
        document.querySelectorAll('.delete-form').forEach(function (form) {
            form.addEventListener('submit', function (e) {
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
        });

        // Solo se pide la lista de "opciones" para los tipos que la
        // necesitan; la lista de tipos sale del propio backend para no
        // duplicarla acá.
        const TIPOS_CON_OPCIONES = @json(\App\Models\FormField::TYPES_CON_OPCIONES);

        function toggleOptionsWrapper(select) {
            const wrapper = select.closest('form')?.querySelector('[data-options-wrapper]');
            if (!wrapper) return;
            wrapper.classList.toggle('hidden', !TIPOS_CON_OPCIONES.includes(select.value));
        }

        document.querySelectorAll('[data-field-type-select]').forEach(function (select) {
            toggleOptionsWrapper(select);
            select.addEventListener('change', function () { toggleOptionsWrapper(select); });
        });
    </script>
    @endpush

</x-admin-layout>
