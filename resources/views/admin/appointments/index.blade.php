<x-admin-layout>
    <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
        <x-label class="text-black dark:text-white text-xl font-semibold">
            <i class="fa-solid fa-calendar-days text-gray-400 mr-1"></i>
            Agenda de Citas
        </x-label>

        <div class="flex items-center gap-2">
            @can('admin.appointments.create')
                <a href="{{ route('admin.appointments.create') }}" class="btn btn-green rounded-lg text-sm">
                    <i class="fa-solid fa-plus mr-1"></i> Nueva Cita
                </a>
            @endcan
        </div>
    </div>

    @if (session('info'))
        <div class="flex items-center p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
            {{ session('info') }}
        </div>
    @endif

    {{-- Filtro por doctor + leyenda de colores --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
        <div class="flex flex-wrap items-center gap-4">
            <div class="w-full md:w-64">
                <label class="form-label">Filtrar por doctor</label>
                <x-select id="doctor-filter" class="input-label rounded-lg w-full">
                    <option value="">Todos los doctores</option>
                    @foreach ($doctors as $doctor)
                        <option value="{{ $doctor->id }}">
                            {{ $doctor->person->name }} {{ $doctor->person->last_name_father }}
                        </option>
                    @endforeach
                </x-select>
            </div>

            <div class="flex flex-wrap items-center gap-3 text-xs text-gray-600 dark:text-gray-300">
                <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-full" style="background:#d97706"></span> Programada</span>
                <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-full" style="background:#2563eb"></span> Confirmada</span>
                <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-full" style="background:#16a34a"></span> Completada</span>
                <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-full" style="background:#dc2626"></span> No asistió</span>
                <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-full" style="background:#9ca3af"></span> Cancelada</span>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
        <div id="calendar"></div>
    </div>

    @push('js')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6/index.global.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6/locales/es.global.min.js"></script>

    {{-- FullCalendar trae sus propios colores fijos (pensados para fondo
         blanco) y no sabe nada del modo oscuro del sistema, por eso en
         oscuro el nombre de los días (dom, lun, mar...), los números y los
         botones quedaban con texto oscuro sobre fondo oscuro = invisibles.
         Se pisan acá sus variables CSS y algunos textos puntuales solo
         cuando está activo .dark. --}}
    <style>
        .dark #calendar {
            --fc-border-color: #374151;
            --fc-page-bg-color: transparent;
            --fc-neutral-bg-color: #1f2937;
            --fc-neutral-text-color: #d1d5db;
            --fc-list-event-hover-bg-color: #374151;
            --fc-today-bg-color: rgba(45, 212, 191, 0.12);
        }

        .dark #calendar .fc-col-header-cell-cushion,
        .dark #calendar .fc-daygrid-day-number,
        .dark #calendar .fc-toolbar-title,
        .dark #calendar .fc-list-day-text,
        .dark #calendar .fc-list-day-side-text,
        .dark #calendar .fc-list-event-title a,
        .dark #calendar .fc-list-event-time,
        .dark #calendar .fc-timegrid-slot-label-cushion,
        .dark #calendar .fc-timegrid-axis-cushion {
            color: #e5e7eb;
        }

        .dark #calendar .fc-list-empty {
            background-color: #1f2937;
            color: #9ca3af;
        }

        .dark #calendar .fc-button {
            background-color: #374151;
            border-color: #4b5563;
            color: #e5e7eb;
        }

        .dark #calendar .fc-button:hover {
            background-color: #4b5563;
        }

        .dark #calendar .fc-button-primary:not(:disabled).fc-button-active {
            background-color: var(--brand-primary, #0d9488);
            border-color: var(--brand-primary, #0d9488);
            color: #fff;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');
            const doctorFilter = document.getElementById('doctor-filter');
            const canCreate = @json(auth()->user()->can('admin.appointments.create'));

            const isSmallScreen = window.innerWidth < 640;

            const calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'es',
                height: 'auto',
                headerToolbar: isSmallScreen
                    ? { left: 'prev,next today', center: '', right: 'title' }
                    : {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
                    },
                footerToolbar: isSmallScreen
                    ? { center: 'dayGridMonth,listWeek' }
                    : false,
                initialView: isSmallScreen ? 'listWeek' : 'dayGridMonth',
                windowResize: function (view) {
                    const nowSmall = window.innerWidth < 640;
                    calendar.setOption('headerToolbar', nowSmall
                        ? { left: 'prev,next today', center: '', right: 'title' }
                        : {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
                        });
                    calendar.setOption('footerToolbar', nowSmall
                        ? { center: 'dayGridMonth,listWeek' }
                        : false);
                },
                buttonText: {
                    today: 'Hoy',
                    month: 'Mes',
                    week: 'Semana',
                    day: 'Día',
                    list: 'Lista',
                },
                events: function (info, successCallback, failureCallback) {
                    const params = new URLSearchParams();
                    if (doctorFilter.value) params.set('doctor_id', doctorFilter.value);

                    fetch(`{{ route('admin.appointments.events') }}?${params.toString()}`)
                        .then(res => res.json())
                        .then(successCallback)
                        .catch(failureCallback);
                },
                dateClick: function (info) {
                    if (!canCreate) return;
                    window.location.href = `{{ route('admin.appointments.create') }}?date=${info.dateStr}`;
                },
            });

            calendar.render();

            doctorFilter.addEventListener('change', () => calendar.refetchEvents());
        });
    </script>
    @endpush
</x-admin-layout>
