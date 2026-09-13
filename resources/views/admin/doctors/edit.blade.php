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

        {{-- Acceso al sistema --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
            <x-label class="text-black dark:text-white text-base font-semibold mb-3 block">
                Acceso al sistema
            </x-label>

            <div class="md:w-1/2">
                <x-label class="form-label">Usuario vinculado</x-label>
                <x-select name="user_id" class="input-label rounded-lg w-full">
                    <option value="">Sin cuenta vinculada</option>
                    @foreach ($linkableUsers as $user)
                        <option value="{{ $user->id }}" @selected(old('user_id', $doctor->user_id) == $user->id)>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </x-select>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Solo el doctor vinculado aquí queda restringido por defecto a ver el historial clínico de su
                    propia especialidad. Sin una cuenta vinculada, este registro es solo informativo.
                    Las cuentas con rol Admin no aparecen en esta lista: ya tienen acceso a todas las
                    especialidades por defecto, sin necesidad de vincularse a ningún doctor.
                </p>
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('admin.doctors.index') }}" class="btn btn-gray rounded-lg">Cancelar</a>
            <button type="submit" class="btn btn-green rounded-lg">
                <i class="fa-solid fa-check mr-1"></i> Actualizar
            </button>
        </div>
    </form>

    {{-- Acceso a otras especialidades --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
        <x-label class="text-black dark:text-white text-base font-semibold mb-1 block">
            Acceso a otras especialidades
        </x-label>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
            Por defecto este doctor solo ve el historial clínico de pacientes de su propia especialidad
            ({{ $doctor->speciality->name ?? '—' }}). Autoriza aquí una excepción cuando necesite ver también
            el de otra especialidad.
        </p>

        @if ($doctor->specialityAccessGrants->isNotEmpty())
            <div class="space-y-2 mb-4">
                @foreach ($doctor->specialityAccessGrants as $grant)
                    <div class="flex flex-wrap items-center justify-between gap-2 border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                {{ $grant->speciality->name ?? '—' }}
                            </p>
                            @if ($grant->note)
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $grant->note }}</p>
                            @endif
                        </div>
                        <form action="{{ route('admin.doctors.speciality_access.destroy', [$doctor, $grant]) }}"
                            method="POST" onsubmit="return confirm('¿Quitar el acceso a esta especialidad?');" class="shrink-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-gray rounded-lg text-xs">
                                <i class="fa-solid fa-xmark mr-1"></i> Quitar
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-400 mb-4">Todavía no tiene ninguna especialidad adicional autorizada.</p>
        @endif

        @if ($grantableSpecialities->isNotEmpty())
            <form action="{{ route('admin.doctors.speciality_access.store', $doctor) }}" method="POST"
                class="flex flex-col sm:flex-row gap-3 sm:items-end">
                @csrf
                <div class="flex-1">
                    <x-label class="form-label">Especialidad</x-label>
                    <x-select name="speciality_id" class="input-label rounded-lg w-full">
                        @foreach ($grantableSpecialities as $speciality)
                            <option value="{{ $speciality->id }}">{{ $speciality->name }}</option>
                        @endforeach
                    </x-select>
                </div>
                <div class="flex-1">
                    <x-label class="form-label">Motivo (opcional)</x-label>
                    <x-input name="note" class="input-label rounded-lg w-full" placeholder="Ej: cubre licencia de la Dra. Pérez" />
                </div>
                <button type="submit" class="btn btn-green rounded-lg shrink-0 w-full sm:w-auto">
                    <i class="fa-solid fa-check mr-1"></i> Autorizar
                </button>
            </form>
        @endif
    </div>
</x-admin-layout>
