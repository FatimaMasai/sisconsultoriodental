<x-admin-layout>
    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
        <x-label class="text-black dark:text-white text-xl font-semibold">
            <i class="fa-solid fa-palette text-gray-400 mr-1"></i>
            Apariencia
        </x-label>

        <a href="{{ route('admin.dashboard') }}" class="btn btn-gray rounded-lg text-sm">
            <i class="fa-solid fa-arrow-left mr-1"></i> Volver
        </a>
    </div>

    <x-validation-errors class="mb-4" />

    @if (session('logo_warning'))
        <div class="flex items-start gap-3 p-4 rounded-lg bg-yellow-50 dark:bg-gray-700 text-yellow-800 dark:text-yellow-300 text-sm mb-4">
            <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
            <div>{{ session('logo_warning') }}</div>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
        <p class="text-gray-700 dark:text-gray-300 mb-4">
            Todo lo de acá se usa en el menú del sistema y en todos los PDF e impresiones (recetas, historias
            clínicas, comprobantes, listados). No hace falta tocar código para cambiarlo.
        </p>

        <form action="{{ route('admin.settings.appearance.update') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <x-label class="form-label" id="logo-preview-label">Logo actual</x-label>
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 flex items-center justify-center bg-gray-50 dark:bg-gray-900 mb-2" style="min-height: 100px;">
                        <img id="logo-preview" src="{{ $setting->logoUrl() }}" alt="Logo actual" style="max-height: 80px; max-width: 100%;">
                    </div>

                    <x-input id="logo-input" type="file" name="logo" accept=".png,.jpg,.jpeg,.webp" class="input-label rounded-lg w-full" />
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        PNG, JPG o WEBP, máximo 2MB. Si no elegís un archivo nuevo, se mantiene el logo actual.
                        El sistema le recorta el margen en blanco sobrante y lo ajusta automáticamente para que
                        ocupe bien el espacio en el menú, el login y las PDF; si la imagen es de baja resolución
                        te avisamos acá arriba. La vista previa de acá arriba es tal cual subiste el archivo,
                        sin el recorte automático todavía: eso se ve recién después de "Guardar cambios".
                    </p>
                </div>

                <div>
                    <x-label class="form-label">Color de marca</x-label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="primary_color_picker"
                            value="{{ old('primary_color', $setting->brandColor()) }}"
                            class="h-11 w-16 rounded-lg border border-gray-300 dark:border-gray-600 cursor-pointer"
                            oninput="document.getElementById('primary_color_text').value = this.value">
                        <x-input type="text" id="primary_color_text" name="primary_color"
                            value="{{ old('primary_color', $setting->brandColor()) }}"
                            class="input-label rounded-lg w-full font-mono"
                            oninput="document.querySelector('input[name=primary_color_picker]').value = this.value"
                            maxlength="7" placeholder="#0d9488" />
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Se usa en el menú lateral, encabezados y el botón principal ("Guardar", "Confirmar") de cada
                        pantalla. Los botones de eliminar y cancelar no cambian, para que se sigan distinguiendo.
                    </p>
                </div>

                <div>
                    <x-label class="form-label">Tamaño de letra</x-label>
                    <x-select name="font_size" class="input-label rounded-lg w-full">
                        <option value="small" @selected(old('font_size', $setting->font_size) == 'small')>Chico</option>
                        <option value="normal" @selected(old('font_size', $setting->font_size ?: 'normal') == 'normal')>Normal</option>
                        <option value="large" @selected(old('font_size', $setting->font_size) == 'large')>Grande</option>
                    </x-select>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Achica o agranda el texto de todo el sistema (menús, tablas, formularios). Útil si a algún
                        usuario le cuesta leer letra chica.
                    </p>
                </div>

                <div>
                    <x-label class="form-label">Nombre del sistema</x-label>
                    <x-input type="text" name="system_name"
                        value="{{ old('system_name', $setting->system_name) }}"
                        class="input-label rounded-lg w-full" maxlength="60"
                        placeholder="{{ config('app.name', 'Laravel') }}" />
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Aparece en el título de la pestaña del navegador. Si lo dejás vacío, se usa
                        "{{ config('app.name', 'Laravel') }}".
                    </p>
                </div>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                <x-label class="form-label mb-1">Datos de contacto de la clínica</x-label>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                    Se imprimen solos en las recetas y comprobantes. Dejá en blanco los que no quieras mostrar.
                </p>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <x-label class="text-xs">Dirección</x-label>
                        <x-input type="text" name="contact_address"
                            value="{{ old('contact_address', $setting->contact_address) }}"
                            class="input-label rounded-lg w-full" maxlength="150"
                            placeholder="Av. Siempre Viva 123, La Paz" />
                    </div>
                    <div>
                        <x-label class="text-xs">Teléfono</x-label>
                        <x-input type="text" name="contact_phone"
                            value="{{ old('contact_phone', $setting->contact_phone) }}"
                            class="input-label rounded-lg w-full" maxlength="40"
                            placeholder="+591 700 00000" />
                    </div>
                    <div>
                        <x-label class="text-xs">Redes / WhatsApp</x-label>
                        <x-input type="text" name="contact_social"
                            value="{{ old('contact_social', $setting->contact_social) }}"
                            class="input-label rounded-lg w-full" maxlength="150"
                            placeholder="@wellness.centro | wa.me/59170000000" />
                    </div>
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit" class="btn btn-green rounded-lg">
                    <i class="fa-solid fa-check mr-1"></i> Guardar cambios
                </button>
            </div>
        </form>

        @if ($setting->logo_path)
            <form action="{{ route('admin.settings.appearance.update') }}" method="POST" class="mt-2 pt-4 border-t border-gray-100 dark:border-gray-700">
                @csrf
                <input type="hidden" name="remove_logo" value="1">
                <input type="hidden" name="primary_color" value="{{ $setting->brandColor() }}">
                <input type="hidden" name="font_size" value="{{ $setting->font_size ?: 'normal' }}">
                <input type="hidden" name="system_name" value="{{ $setting->system_name }}">
                <input type="hidden" name="contact_address" value="{{ $setting->contact_address }}">
                <input type="hidden" name="contact_phone" value="{{ $setting->contact_phone }}">
                <input type="hidden" name="contact_social" value="{{ $setting->contact_social }}">
                <button type="submit" class="btn btn-gray rounded-lg text-xs"
                    onclick="return confirm('¿Volver al logo por defecto del sistema?');">
                    <i class="fa-solid fa-rotate-left mr-1"></i> Quitar logo personalizado
                </button>
            </form>
        @endif
    </div>

    @if (! file_exists(public_path('storage')))
        <div class="flex items-start gap-3 p-4 rounded-lg bg-yellow-50 dark:bg-gray-700 text-yellow-800 dark:text-yellow-300 text-sm">
            <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
            <div>
                Si al subir el logo no se ve en el sistema, corré esto una vez en la terminal, en la carpeta del
                proyecto: <code class="block mt-2 bg-white dark:bg-gray-800 rounded px-2 py-1 text-xs">php artisan storage:link</code>
            </div>
        </div>
    @endif

    @push('js')
    <script>
        // Vista previa instantánea al elegir un archivo: antes de esto, el
        // cuadro "Logo actual" se quedaba con el logo viejo hasta que se
        // guardaba el formulario y la página recargaba, así que parecía que
        // el archivo elegido no se había subido. Con esto se ve al toque,
        // aclarando que todavía no está guardado (ver el texto que aparece
        // al lado del título).
        (function () {
            const input = document.getElementById('logo-input');
            const preview = document.getElementById('logo-preview');
            const label = document.getElementById('logo-preview-label');
            if (!input || !preview || !label) return;

            const defaultLabel = label.textContent;
            let objectUrl = null;

            input.addEventListener('change', function () {
                const file = input.files && input.files[0];

                // Se libera la vista previa anterior (si había una) para no
                // ir acumulando URLs de objeto sin usar en memoria.
                if (objectUrl) {
                    URL.revokeObjectURL(objectUrl);
                    objectUrl = null;
                }

                if (!file) {
                    label.textContent = defaultLabel;
                    return;
                }

                objectUrl = URL.createObjectURL(file);
                preview.src = objectUrl;
                label.textContent = defaultLabel + ' (nuevo, sin guardar todavía)';
            });
        })();
    </script>
    @endpush
</x-admin-layout>
