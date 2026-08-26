<x-admin-layout>
    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
        <x-label class="text-black dark:text-white text-xl font-semibold">
            <i class="fa-solid fa-calendar-pen text-gray-400 mr-1"></i>
            Editar Cita
        </x-label>

        <a href="{{ route('admin.appointments.index') }}" class="btn btn-gray rounded-lg text-sm">
            <i class="fa-solid fa-arrow-left mr-1"></i> Volver
        </a>
    </div>

    <x-validation-errors class="mb-4" />

    @if (session('info'))
        <div class="flex items-center p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
            {{ session('info') }}
        </div>
    @endif

    {{-- Estado actual y acciones rápidas --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500 dark:text-gray-400">Estado:</span>
                @if ($appointment->status === 'Completada')
                    <x-badge color="green">Completada</x-badge>
                @elseif ($appointment->status === 'Confirmada')
                    <x-badge color="blue">Confirmada</x-badge>
                @elseif ($appointment->status === 'Cancelada')
                    <x-badge color="gray">Cancelada</x-badge>
                @elseif ($appointment->status === 'No asistio')
                    <x-badge color="red">No asistió</x-badge>
                @else
                    <x-badge color="yellow">Programada</x-badge>
                @endif
            </div>

            @if (! in_array($appointment->status, ['Cancelada', 'Completada']))
                <div class="flex items-center gap-2">
                    @can('admin.appointments.edit')
                        @if ($appointment->status !== 'Confirmada')
                            <form action="{{ route('admin.appointments.confirm', $appointment) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-blue text-xs rounded-lg">
                                    <i class="fa-solid fa-check mr-1"></i> Confirmar
                                </button>
                            </form>
                        @endif

                        <form action="{{ route('admin.appointments.complete', $appointment) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-green text-xs rounded-lg">
                                <i class="fa-solid fa-check-double mr-1"></i> Completada
                            </button>
                        </form>
                    @endcan

                    @can('admin.appointments.cancel')
                        <form action="{{ route('admin.appointments.cancel', $appointment) }}" method="POST" class="delete-form">
                            @csrf
                            <button type="submit" class="btn btn-red text-xs rounded-lg">
                                <i class="fa-solid fa-ban mr-1"></i> Cancelar cita
                            </button>
                        </form>
                    @endcan
                </div>
            @endif
        </div>
    </div>

    <form action="{{ route('admin.appointments.update', $appointment) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
            <x-label class="text-black dark:text-white text-base font-semibold mb-3 block">
                Datos de la cita
            </x-label>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <x-label class="form-label">Paciente</x-label>
                    <x-select name="patient_id" class="rounded-lg w-full" required>
                        @foreach ($patients as $patient)
                            <option value="{{ $patient->id }}" @selected(old('patient_id', $appointment->patient_id) == $patient->id)>
                                {{ $patient->person->name }} {{ $patient->person->last_name_father }} {{ $patient->person->last_name_mother }}
                            </option>
                        @endforeach
                    </x-select>
                </div>

                <div>
                    <x-label class="form-label">Doctor</x-label>
                    <x-select name="doctor_id" class="rounded-lg w-full" required>
                        @foreach ($doctors as $doctor)
                            <option value="{{ $doctor->id }}" @selected(old('doctor_id', $appointment->doctor_id) == $doctor->id)>
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
                            <option value="{{ $service->id }}" @selected(old('service_id', $appointment->service_id) == $service->id)>
                                {{ $service->name }}
                            </option>
                        @endforeach
                    </x-select>
                </div>

                <div></div>

                <div>
                    <x-label class="form-label">Fecha</x-label>
                    <input type="date" name="date" value="{{ old('date', $appointment->starts_at->format('Y-m-d')) }}"
                        class="input-label rounded-lg w-full" required>
                </div>

                <div>
                    <x-label class="form-label">Hora</x-label>
                    <input type="time" name="start_time" value="{{ old('start_time', $appointment->starts_at->format('H:i')) }}"
                        class="input-label rounded-lg w-full" required>
                </div>

                <div>
                    <x-label class="form-label">Duración</x-label>
                    @php $currentDuration = $appointment->starts_at->diffInMinutes($appointment->ends_at); @endphp
                    <x-select name="duration_minutes" class="rounded-lg w-full">
                        <option value="15" @selected(old('duration_minutes', $currentDuration) == 15)>15 minutos</option>
                        <option value="30" @selected(old('duration_minutes', $currentDuration) == 30)>30 minutos</option>
                        <option value="45" @selected(old('duration_minutes', $currentDuration) == 45)>45 minutos</option>
                        <option value="60" @selected(old('duration_minutes', $currentDuration) == 60)>1 hora</option>
                        <option value="90" @selected(old('duration_minutes', $currentDuration) == 90)>1 hora 30</option>
                        <option value="120" @selected(old('duration_minutes', $currentDuration) == 120)>2 horas</option>
                    </x-select>
                </div>
            </div>

            <div class="mt-4">
                <x-label class="form-label">Notas (opcional)</x-label>
                <textarea name="notes" rows="3"
                    class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 shadow-sm w-full"
                    placeholder="Motivo de la consulta, indicaciones, etc.">{{ old('notes', $appointment->notes) }}</textarea>
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('admin.appointments.index') }}" class="btn btn-gray rounded-lg">Cancelar</a>
            <button type="submit" class="btn btn-green rounded-lg">
                <i class="fa-solid fa-check mr-1"></i> Guardar Cambios
            </button>
        </div>
    </form>

    @push('js')
    <script>
        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();

                Swal.fire({
                    title: "¿Cancelar esta cita?",
                    text: "El paciente ya no va a aparecer agendado en ese horario.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Sí, cancelar",
                    cancelButtonText: "Volver",
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
    @endpush
</x-admin-layout>
