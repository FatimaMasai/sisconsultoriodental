<x-admin-layout>
    <div class="flex justify-between items-center mb-6">
        <x-label class="text-black dark:text-white text-xl font-semibold">
            <i class="fa-solid fa-folder-plus text-gray-400 mr-1"></i>
            Nuevo Expediente
        </x-label>

        <a href="{{ route('admin.expedientes.index') }}" class="btn btn-gray rounded-lg text-sm">
            <i class="fa-solid fa-arrow-left mr-1"></i> Volver
        </a>
    </div>

    <x-validation-errors class="mb-4" />

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
            Usa esto para dejar listo el expediente de un paciente antes de su primera consulta o venta en una
            especialidad. Si el paciente ya tiene expediente ahí, simplemente te va a llevar al que ya existe.
        </p>

        <form action="{{ route('admin.expedientes.store') }}" method="POST">
            @csrf

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-label class="form-label">Paciente <span class="text-red-500">*</span></x-label>
                    <x-select name="patient_id" class="input-label rounded-lg w-full">
                        <option value="">Seleccione un paciente</option>
                        @foreach ($patients as $patient)
                            <option value="{{ $patient->id }}" @selected(old('patient_id') == $patient->id)>
                                {{ $patient->person->name }} {{ $patient->person->last_name_father }} {{ $patient->person->last_name_mother }}
                            </option>
                        @endforeach
                    </x-select>
                </div>
                <div>
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

            <div class="flex justify-end mt-6">
                <button type="submit" class="btn btn-green rounded-lg">
                    <i class="fa-solid fa-check mr-1"></i> Crear expediente
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
