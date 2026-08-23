<x-admin-layout>
    <div class="flex justify-between items-center mb-6">
        <x-label class="text-black dark:text-white text-xl font-semibold">
            <i class="fa-solid fa-user-doctor text-gray-400 mr-1"></i>
            Nuevo Doctor
        </x-label>

        <a href="{{ route('admin.doctors.index') }}" class="btn btn-gray rounded-lg text-sm">
            <i class="fa-solid fa-arrow-left mr-1"></i> Volver
        </a>
    </div>

    <x-validation-errors class="mb-4" />

    <form action="{{ route('admin.doctors.store') }}" method="POST" id="doctor-form">
        @csrf

        {{-- ¿Persona nueva o ya registrada? --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
            <div class="grid grid-cols-2 gap-3 max-w-md">
                <label class="cursor-pointer">
                    <input type="radio" name="person_mode" value="new" class="peer sr-only"
                        @checked(old('person_mode', 'new') == 'new')>
                    <div class="border-2 border-gray-200 dark:border-gray-600 rounded-lg p-3 text-center transition peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/10 peer-checked:[&_.mode-icon]:text-blue-600 dark:peer-checked:[&_.mode-icon]:text-blue-400 peer-checked:[&_.mode-title]:text-blue-700 dark:peer-checked:[&_.mode-title]:text-blue-300">
                        <i class="fa-solid fa-user-plus mode-icon text-gray-400 mb-1"></i>
                        <p class="mode-title font-medium text-gray-900 dark:text-white text-sm">Persona nueva</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Registrar sus datos aquí</p>
                    </div>
                </label>

                <label class="cursor-pointer">
                    <input type="radio" name="person_mode" value="existing" class="peer sr-only"
                        @checked(old('person_mode') == 'existing')>
                    <div class="border-2 border-gray-200 dark:border-gray-600 rounded-lg p-3 text-center transition peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/10 peer-checked:[&_.mode-icon]:text-blue-600 dark:peer-checked:[&_.mode-icon]:text-blue-400 peer-checked:[&_.mode-title]:text-blue-700 dark:peer-checked:[&_.mode-title]:text-blue-300">
                        <i class="fa-solid fa-magnifying-glass mode-icon text-gray-400 mb-1"></i>
                        <p class="mode-title font-medium text-gray-900 dark:text-white text-sm">Ya existe en el sistema</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Elegir una persona registrada</p>
                    </div>
                </label>
            </div>
        </div>

        {{-- Persona nueva: datos personales --}}
        <div id="person-new-fields">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
                <x-label class="text-black dark:text-white text-base font-semibold mb-3 block">
                    Datos personales
                </x-label>

                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <x-label class="form-label">Nombre <span class="text-red-500">*</span></x-label>
                        <x-input value="{{ old('name') }}" name="name" class="input-label rounded-lg w-full" placeholder="Lupita" />
                    </div>
                    <div>
                        <x-label class="form-label">Apellido Paterno <span class="text-red-500">*</span></x-label>
                        <x-input value="{{ old('last_name_father') }}" name="last_name_father" class="input-label rounded-lg w-full" placeholder="Cuellar" />
                    </div>
                    <div>
                        <x-label class="form-label">Apellido Materno <span class="text-red-500">*</span></x-label>
                        <x-input value="{{ old('last_name_mother') }}" name="last_name_mother" class="input-label rounded-lg w-full" placeholder="Paz" />
                    </div>

                    <div>
                        <x-label class="form-label">Carnet de Identidad <span class="text-red-500">*</span></x-label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 text-sm">
                                <i class="fa-solid fa-id-badge"></i>
                            </span>
                            <x-input value="{{ old('identity_card') }}" name="identity_card" type="text" inputmode="numeric"
                                class="input-label rounded-lg pl-9 w-full" placeholder="12566956" />
                        </div>
                    </div>
                    <div>
                        <x-label class="form-label">Fecha de Nacimiento <span class="text-red-500">*</span></x-label>
                        <x-input value="{{ old('birth_date') }}" type="date" name="birth_date"
                            class="input-label rounded-lg w-full" max="{{ now()->format('Y-m-d') }}" />
                    </div>
                    <div>
                        <x-label class="form-label">Sexo <span class="text-red-500">*</span></x-label>
                        <x-select name="gender" class="input-label rounded-lg w-full">
                            <option value="Femenino" @selected(old('gender') == 'Femenino')>Femenino</option>
                            <option value="Masculino" @selected(old('gender') == 'Masculino')>Masculino</option>
                        </x-select>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
                <x-label class="text-black dark:text-white text-base font-semibold mb-3 block">
                    Contacto
                </x-label>

                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <x-label class="form-label">Celular <span class="text-red-500">*</span></x-label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 text-sm">
                                <i class="fa-solid fa-phone"></i>
                            </span>
                            <x-input value="{{ old('phone') }}" name="phone" type="tel" class="input-label rounded-lg pl-9 w-full" placeholder="75304552" />
                        </div>
                    </div>
                    <div>
                        <x-label class="form-label">Email <span class="text-red-500">*</span></x-label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 text-sm">
                                <i class="fa-solid fa-envelope"></i>
                            </span>
                            <x-input value="{{ old('email') }}" name="email" type="email" class="input-label rounded-lg pl-9 w-full" placeholder="lupita@gmail.com" />
                        </div>
                    </div>
                    <div>
                        <x-label class="form-label">Dirección <span class="text-red-500">*</span></x-label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 text-sm">
                                <i class="fa-solid fa-location-dot"></i>
                            </span>
                            <x-input value="{{ old('address') }}" name="address" class="input-label rounded-lg pl-9 w-full" placeholder="Km 6 doble vía, calle 6 #18" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Persona ya registrada --}}
        <div id="person-existing-fields" class="hidden">
            @php $selectedPerson = old('person_id') ? $persons->firstWhere('id', (int) old('person_id')) : null; @endphp
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
                <x-label class="text-black dark:text-white text-base font-semibold mb-3 block">
                    Seleccionar persona
                </x-label>

                <div class="md:w-1/2 relative" data-person-combobox>
                    <input type="hidden" name="person_id" class="person-search-hidden" value="{{ old('person_id') }}">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 text-sm">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" autocomplete="off" class="person-search-input input-label rounded-lg pl-9 w-full"
                            placeholder="Buscar por nombre o apellido..."
                            value="{{ $selectedPerson ? $selectedPerson->name . ' ' . $selectedPerson->last_name_father . ' ' . $selectedPerson->last_name_mother : '' }}">
                    </div>
                    <div class="person-search-results hidden absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg">
                        @foreach ($persons as $person)
                            <button type="button" class="person-option w-full text-left px-3 py-2 text-sm hover:bg-blue-50 dark:hover:bg-blue-900/20 text-gray-700 dark:text-gray-200"
                                data-id="{{ $person->id }}"
                                data-name="{{ $person->name }} {{ $person->last_name_father }} {{ $person->last_name_mother }}">
                                {{ $person->name }} {{ $person->last_name_father }} {{ $person->last_name_mother }}
                            </button>
                        @endforeach
                        <p class="person-empty hidden px-3 py-2 text-sm text-gray-400">Sin resultados</p>
                        <p class="person-more hidden px-3 py-2 text-xs text-gray-400 border-t border-gray-100 dark:border-gray-700"></p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Datos del doctor --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
            <x-label class="text-black dark:text-white text-base font-semibold mb-3 block">
                Datos profesionales
            </x-label>

            <div class="md:w-1/2">
                <x-label class="form-label">Especialidad <span class="text-red-500">*</span></x-label>
                <x-select name="speciality_id" class="input-label rounded-lg w-full">
                    <option value="">Seleccione una especialidad</option>
                    @foreach ($specialities as $speciality)
                        <option value="{{ $speciality->id }}" @selected(old('speciality_id') == $speciality->id)>
                            {{ $speciality->name }}
                        </option>
                    @endforeach
                </x-select>
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('admin.doctors.index') }}" class="btn btn-gray rounded-lg">Cancelar</a>
            <button type="submit" class="btn btn-green rounded-lg">
                <i class="fa-solid fa-check mr-1"></i> Guardar
            </button>
        </div>
    </form>

    @push('js')
    <script>
        const newFields = document.getElementById('person-new-fields');
        const existingFields = document.getElementById('person-existing-fields');

        function togglePersonMode() {
            const isExisting = document.querySelector('input[name="person_mode"]:checked')?.value === 'existing';

            newFields.classList.toggle('hidden', isExisting);
            existingFields.classList.toggle('hidden', !isExisting);

            newFields.querySelectorAll('input, select').forEach(el => el.disabled = isExisting);
            existingFields.querySelectorAll('input, select').forEach(el => el.disabled = !isExisting);
        }

        document.querySelectorAll('input[name="person_mode"]').forEach(radio => {
            radio.addEventListener('change', togglePersonMode);
        });

        togglePersonMode();

        // Buscador de "Seleccionar persona": filtra la lista mientras se escribe.
        document.querySelectorAll('[data-person-combobox]').forEach(function (wrapper) {
            const input = wrapper.querySelector('.person-search-input');
            const hidden = wrapper.querySelector('.person-search-hidden');
            const results = wrapper.querySelector('.person-search-results');
            const options = Array.from(wrapper.querySelectorAll('.person-option'));
            const empty = wrapper.querySelector('.person-empty');
            const more = wrapper.querySelector('.person-more');
            const MAX_VISIBLE = 4;

            function showResults() {
                const term = input.value.trim().toLowerCase();
                let matches = 0;
                let shown = 0;
                options.forEach(function (opt) {
                    const match = opt.dataset.name.toLowerCase().includes(term);
                    if (match) matches++;
                    const visible = match && shown < MAX_VISIBLE;
                    if (visible) shown++;
                    opt.classList.toggle('hidden', !visible);
                });

                empty.classList.toggle('hidden', matches > 0);

                const remaining = matches - shown;
                if (remaining > 0) {
                    more.textContent = `+${remaining} más, sigue escribiendo para ver otras`;
                    more.classList.remove('hidden');
                } else {
                    more.classList.add('hidden');
                }

                results.classList.remove('hidden');
            }

            input.addEventListener('focus', showResults);
            input.addEventListener('input', function () {
                hidden.value = '';
                showResults();
            });

            options.forEach(function (opt) {
                opt.addEventListener('click', function () {
                    hidden.value = opt.dataset.id;
                    input.value = opt.dataset.name;
                    results.classList.add('hidden');
                });
            });

            document.addEventListener('click', function (e) {
                if (!wrapper.contains(e.target)) {
                    results.classList.add('hidden');
                }
            });
        });
    </script>
    @endpush
</x-admin-layout>
