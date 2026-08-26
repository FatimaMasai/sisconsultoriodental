<x-admin-layout>
    <div class="flex justify-between items-center mb-6">
        <x-label class="text-black dark:text-white text-xl font-semibold">
            <i class="fa-solid fa-calendar-plus text-gray-400 mr-1"></i>
            Nueva Cita
        </x-label>

        <a href="{{ route('admin.appointments.index') }}" class="btn btn-gray rounded-lg text-sm">
            <i class="fa-solid fa-arrow-left mr-1"></i> Volver
        </a>
    </div>

    <x-validation-errors class="mb-4" />

    <form action="{{ route('admin.appointments.store') }}" method="POST">
        @csrf

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
            <x-label class="text-black dark:text-white text-base font-semibold mb-3 block">
                Datos de la cita
            </x-label>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <x-label class="form-label">Paciente</x-label>
                    <x-select name="patient_id" class="rounded-lg w-full" required>
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
                    <x-select name="doctor_id" class="rounded-lg w-full" required>
                        <option value="">Seleccione un doctor</option>
                        @foreach ($doctors as $doctor)
                            <option value="{{ $doctor->id }}" @selected(old('doctor_id') == $doctor->id)>
                                {{ $doctor->person->name }} {{ $doctor->person->last_name_father }} {{ $doctor->person->last_name_mother }}
                            </option>
                        @endforeach
                    </x-select>
                </div>

                <div>
                    <x-label class="form-label">Servicio (opcional)</x-label>
                    <x-select name="service_id" class="rounded-lg w-full">
                        <option value="">Sin especificar</option>
                        @foreach ($services as $service)
                            <option value="{{ $service->id }}" @selected(old('service_id') == $service->id)>
                                {{ $service->name }}
                            </option>
                        @endforeach
                    </x-select>
                </div>

                <div></div>

                <div>
                    <x-label class="form-label">Fecha</x-label>
                    <input type="date" name="date" value="{{ old('date', $prefillDate) }}"
                        class="input-label rounded-lg w-full" required>
                </div>

                <div>
                    <x-label class="form-label">Hora</x-label>
                    <input type="time" name="start_time" value="{{ old('start_time', '09:00') }}"
                        class="input-label rounded-lg w-full" required>
                </div>

                <div>
                    <x-label class="form-label">Duración</x-label>
                    <x-select name="duration_minutes" class="rounded-lg w-full">
                        <option value="15" @selected(old('duration_minutes') == 15)>15 minutos</option>
                        <option value="30" @selected(old('duration_minutes', 30) == 30)>30 minutos</option>
                        <option value="45" @selected(old('duration_minutes') == 45)>45 minutos</option>
                        <option value="60" @selected(old('duration_minutes') == 60)>1 hora</option>
                        <option value="90" @selected(old('duration_minutes') == 90)>1 hora 30</option>
                        <option value="120" @selected(old('duration_minutes') == 120)>2 horas</option>
                    </x-select>
                </div>
            </div>

            <div class="mt-4">
                <x-label class="form-label">Notas (opcional)</x-label>
                <textarea name="notes" rows="3"
                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 shadow-sm w-full"
                    placeholder="Motivo de la consulta, indicaciones, etc.">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('admin.appointments.index') }}" class="btn btn-gray rounded-lg">Cancelar</a>
            <button type="submit" class="btn btn-green rounded-lg">
                <i class="fa-solid fa-check mr-1"></i> Agendar Cita
            </button>
        </div>
    </form>
</x-admin-layout>
