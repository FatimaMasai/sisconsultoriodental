<x-admin-layout>
    <div class="flex justify-between items-center mb-6">
        <x-label class="text-black dark:text-white text-xl font-semibold">
            <i class="fa-solid fa-user-doctor text-gray-400 mr-1"></i>
            Editar Doctor
        </x-label>

        <a href="{{ route('admin.doctors.index') }}" class="btn btn-gray rounded-lg text-sm">
            <i class="fa-solid fa-arrow-left mr-1"></i> Volver
        </a>
    </div>

    <x-validation-errors class="mb-4" />

    <form action="{{ route('admin.doctors.update', $doctor) }}" method="POST">
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
                    <x-input value="{{ old('name', $doctor->person->name) }}" name="name" class="input-label rounded-lg w-full" placeholder="Lupita" />
                </div>
                <div>
                    <x-label class="form-label">Apellido Paterno <span class="text-red-500">*</span></x-label>
                    <x-input value="{{ old('last_name_father', $doctor->person->last_name_father) }}" name="last_name_father" class="input-label rounded-lg w-full" placeholder="Cuellar" />
                </div>
                <div>
                    <x-label class="form-label">Apellido Materno</x-label>
                    <x-input value="{{ old('last_name_mother', $doctor->person->last_name_mother) }}" name="last_name_mother" class="input-label rounded-lg w-full" placeholder="Paz" />
                </div>

                <div>
                    <x-label class="form-label">Carnet de Identidad <span class="text-red-500">*</span></x-label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 text-sm">
                            <i class="fa-solid fa-id-badge"></i>
                        </span>
                        <x-input value="{{ old('identity_card', $doctor->person->identity_card) }}" name="identity_card" type="text" inputmode="numeric"
                            class="input-label rounded-lg pl-9 w-full" placeholder="12566956" />
                    </div>
                </div>
                <div>
                    <x-label class="form-label">Fecha de Nacimiento <span class="text-red-500">*</span></x-label>
                    <x-input value="{{ old('birth_date', $doctor->person->birth_date) }}" type="date" name="birth_date"
                        class="input-label rounded-lg w-full" max="{{ now()->format('Y-m-d') }}" />
                </div>
                <div>
                    <x-label class="form-label">Sexo <span class="text-red-500">*</span></x-label>
                    <x-select name="gender" class="input-label rounded-lg w-full">
                        <option value="Femenino" @selected(old('gender', $doctor->person->gender) == 'Femenino')>Femenino</option>
                        <option value="Masculino" @selected(old('gender', $doctor->person->gender) == 'Masculino')>Masculino</option>
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
                        <x-input value="{{ old('phone', $doctor->person->phone) }}" name="phone" type="tel" class="input-label rounded-lg pl-9 w-full" placeholder="75304552" />
                    </div>
                </div>
                <div>
                    <x-label class="form-label">Email <span class="text-red-500">*</span></x-label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 text-sm">
                            <i class="fa-solid fa-envelope"></i>
                        </span>
                        <x-input value="{{ old('email', $doctor->person->email) }}" name="email" type="email" class="input-label rounded-lg pl-9 w-full" placeholder="lupita@gmail.com" />
                    </div>
                </div>
                <div>
                    <x-label class="form-label">Dirección <span class="text-red-500">*</span></x-label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 text-sm">
                            <i class="fa-solid fa-location-dot"></i>
                        </span>
                        <x-input value="{{ old('address', $doctor->person->address) }}" name="address" class="input-label rounded-lg pl-9 w-full" placeholder="Km 6 doble vía, calle 6 #18" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Datos profesionales --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
            <x-label class="text-black dark:text-white text-base font-semibold mb-3 block">
                Datos profesionales
            </x-label>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <x-label class="form-label">Especialidad <span class="text-red-500">*</span></x-label>
                    <x-select name="speciality_id" class="input-label rounded-lg w-full">
                        @foreach ($specialities as $speciality)
                            <option value="{{ $speciality->id }}" @selected(old('speciality_id', $doctor->speciality_id) == $speciality->id)>
                                {{ $speciality->name }}
                            </option>
                        @endforeach
                    </x-select>
                </div>
                <div>
                    <x-label class="form-label">Estado <span class="text-red-500">*</span></x-label>
                    <x-select name="status" class="input-label rounded-lg w-full">
                        <option value="1" @selected(old('status', $doctor->status) == 1)>Alta</option>
                        <option value="0" @selected(old('status', $doctor->status) == 0)>Baja</option>
                    </x-select>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('admin.doctors.index') }}" class="btn btn-gray rounded-lg">Cancelar</a>
            <button type="submit" class="btn btn-green rounded-lg">
                <i class="fa-solid fa-check mr-1"></i> Actualizar
            </button>
        </div>
    </form>
</x-admin-layout>
