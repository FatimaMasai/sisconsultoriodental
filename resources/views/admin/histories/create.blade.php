<x-admin-layout>
    <x-label class="text-black text-xl font-semibold mb-4">
        Crear Historial Médico
    </x-label>

    <form action="{{ route('admin.histories.store') }}" method="POST">
        @csrf

        <x-validation-errors class="mb-4" />

        <div class="grid gap-6 mb-6 md:grid-cols-2">
            <div>
                <x-label class="form-label">Paciente</x-label>
                <x-select name="patient_id" class="w-full">
                    <option value="">Seleccione un paciente</option>
                    @foreach ($patients as $patient)
                        <option value="{{ $patient->id }}" @selected(old('patient_id') == $patient->id)>
                            {{ $patient->person->name }} {{ $patient->person->last_name_father }} {{ $patient->person->last_name_mother }}
                        </option>
                    @endforeach
                </x-select>
            </div>

            <div>
                <x-label class="form-label">Doctor</x-label>
                <x-select name="doctor_id" class="w-full">
                    <option value="">Seleccione un doctor</option>
                    @foreach ($doctors as $doctor)
                        <option value="{{ $doctor->id }}" @selected(old('doctor_id') == $doctor->id)>
                            {{ $doctor->person->name }} {{ $doctor->person->last_name_father }} {{ $doctor->person->last_name_mother }}
                        </option>
                    @endforeach
                </x-select>
            </div>
            <div>
                <x-label class="form-label">service</x-label>
                <x-select name="service_id" class="w-full">
                    <option value="">Seleccione un service</option>
                    @foreach ($services as $service)
                        <option value="{{ $service->id }}" @selected(old('service_id') == $service->id)>
                            {{ $service->name }}  
                        </option>
                    @endforeach
                </x-select>
            </div>
        </div>


        <div class="flex justify-end">
            <x-button>
                Registrar Venta
            </x-button>
        </div>

    </form>

</x-admin-layout>
