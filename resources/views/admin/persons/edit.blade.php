<x-admin-layout>
    <div class="flex justify-between items-center mb-6">
        <x-label class="text-black dark:text-white text-xl font-semibold">
            <i class="fa-solid fa-id-card text-gray-400 mr-1"></i>
            Editar Persona
        </x-label>

        <a href="{{ route('admin.persons.index') }}" class="btn btn-gray rounded-lg text-sm">
            <i class="fa-solid fa-arrow-left mr-1"></i> Volver
        </a>
    </div>

    <x-validation-errors class="mb-4" />

    <form action="{{ route('admin.persons.update', $person) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Datos personales --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
            <x-label class="text-black dark:text-white text-base font-semibold mb-3 block">
                Datos personales
            </x-label>

            <div class="grid gap-4 md:grid-cols-3">
                <div>
                    <x-label class="form-label">Nombre <span class="text-red-500">*</span></x-label>
                    <x-input value="{{ old('name', $person->name) }}" name="name" class="input-label rounded-lg w-full"
                        placeholder="Lupita" required />
                </div>
                <div>
                    <x-label class="form-label">Apellido Paterno <span class="text-red-500">*</span></x-label>
                    <x-input value="{{ old('last_name_father', $person->last_name_father) }}" name="last_name_father"
                        class="input-label rounded-lg w-full" placeholder="Cuellar" required />
                </div>
                <div>
                    <x-label class="form-label">Apellido Materno</x-label>
                    <x-input value="{{ old('last_name_mother', $person->last_name_mother) }}" name="last_name_mother"
                        class="input-label rounded-lg w-full" placeholder="Paz" />
                </div>

                <div>
                    <x-label class="form-label">Carnet de Identidad <span class="text-red-500">*</span></x-label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 text-sm">
                            <i class="fa-solid fa-id-badge"></i>
                        </span>
                        <x-input value="{{ old('identity_card', $person->identity_card) }}" name="identity_card" type="text" inputmode="numeric"
                            class="input-label rounded-lg pl-9 w-full" placeholder="12566956" required />
                    </div>
                </div>
                <div>
                    <x-label class="form-label">Fecha de Nacimiento <span class="text-red-500">*</span></x-label>
                    <x-input value="{{ old('birth_date', $person->birth_date) }}" type="date" name="birth_date"
                        class="input-label rounded-lg w-full" max="{{ now()->format('Y-m-d') }}" required />
                </div>
                <div>
                    <x-label class="form-label">Sexo <span class="text-red-500">*</span></x-label>
                    <x-select name="gender" class="input-label rounded-lg w-full" required>
                        <option value="Femenino" @selected(old('gender', $person->gender) == 'Femenino')>Femenino</option>
                        <option value="Masculino" @selected(old('gender', $person->gender) == 'Masculino')>Masculino</option>
                    </x-select>
                </div>
            </div>
        </div>

        {{-- Contacto --}}
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
                        <x-input value="{{ old('phone', $person->phone) }}" name="phone" type="tel"
                            class="input-label rounded-lg pl-9 w-full" placeholder="75304552" required />
                    </div>
                </div>
                <div>
                    <x-label class="form-label">Email <span class="text-red-500">*</span></x-label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 text-sm">
                            <i class="fa-solid fa-envelope"></i>
                        </span>
                        <x-input value="{{ old('email', $person->email) }}" name="email" type="email"
                            class="input-label rounded-lg pl-9 w-full" placeholder="lupita@gmail.com" required />
                    </div>
                </div>
                <div>
                    <x-label class="form-label">Dirección <span class="text-red-500">*</span></x-label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 text-sm">
                            <i class="fa-solid fa-location-dot"></i>
                        </span>
                        <x-input value="{{ old('address', $person->address) }}" name="address"
                            class="input-label rounded-lg pl-9 w-full" placeholder="Km 6 doble vía, calle 6 #18" required />
                    </div>
                </div>
            </div>
        </div>

        {{-- Estado --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
            <x-label class="text-black dark:text-white text-base font-semibold mb-3 block">
                Estado
            </x-label>

            <div class="md:w-1/3">
                <x-select name="status" class="input-label rounded-lg w-full">
                    <option value="1" @selected(old('status', $person->status) == 1)>Alta</option>
                    <option value="0" @selected(old('status', $person->status) == 0)>Baja</option>
                </x-select>
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('admin.persons.index') }}" class="btn btn-gray rounded-lg">Cancelar</a>
            <button type="submit" class="btn btn-green rounded-lg">
                <i class="fa-solid fa-check mr-1"></i> Actualizar
            </button>
        </div>
    </form>
</x-admin-layout>
