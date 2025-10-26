<x-admin-layout>
    <x-label class="text-black text-xl font-semibold mb-4">
        Dashboard Avanzado
    </x-label>

    <!-- Row 1: Nuevos Pacientes y Servicios -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <!-- Nuevos Pacientes este mes -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Nuevos Pacientes</p>
                    <p class="text-3xl font-bold text-purple-600">{{ $newPatientsThisMonth }}</p>
                    <p class="text-gray-500 text-xs mt-1">Este mes</p>
                </div>
                <div class="bg-purple-100 rounded-full p-4">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Servicios Hoy -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Servicios Hoy</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $servicesToday }}</p>
                    <p class="text-gray-500 text-xs mt-1">{{ now()->format('d M Y') }}</p>
                </div>
                <div class="bg-blue-100 rounded-full p-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Servicios Esta Semana -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Servicios Semana</p>
                    <p class="text-3xl font-bold text-green-600">{{ $servicesThisWeek }}</p>
                    <p class="text-gray-500 text-xs mt-1">Esta semana</p>
                </div>
                <div class="bg-green-100 rounded-full p-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Servicios Este Mes -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Servicios Mes</p>
                    <p class="text-3xl font-bold text-orange-600">{{ $servicesThisMonth }}</p>
                    <p class="text-gray-500 text-xs mt-1">Este mes</p>
                </div>
                <div class="bg-orange-100 rounded-full p-4">
                    <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Totales Globales -->
    <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-6">
        <div class="bg-gradient-to-br from-green-500 to-green-600 p-6 rounded-lg shadow-md text-white">
            <p class="text-sm opacity-90">Ventas Totales</p>
            <p class="text-2xl font-bold">{{ number_format($totalSales, 2) }} Bs.</p>
        </div>
        <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 p-6 rounded-lg shadow-md text-white">
            <p class="text-sm opacity-90">Compras Totales</p>
            <p class="text-2xl font-bold">{{ number_format($totalPurchases, 2) }} Bs.</p>
        </div>
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-6 rounded-lg shadow-md text-white">
            <p class="text-sm opacity-90">Pacientes</p>
            <p class="text-2xl font-bold">{{ $totalPatients }}</p>
        </div>
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 p-6 rounded-lg shadow-md text-white">
            <p class="text-sm opacity-90">Doctores</p>
            <p class="text-2xl font-bold">{{ $totalDoctors }}</p>
        </div>
        <div class="bg-gradient-to-br from-pink-500 to-pink-600 p-6 rounded-lg shadow-md text-white">
            <p class="text-sm opacity-90">Proveedores</p>
            <p class="text-2xl font-bold">{{ $totalSuppliers }}</p>
        </div>
        <div class="bg-gradient-to-br from-cyan-500 to-cyan-600 p-6 rounded-lg shadow-md text-white">
            <p class="text-sm opacity-90">Servicios</p>
            <p class="text-2xl font-bold">{{ $totalServices }}</p>
        </div>
        <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 p-6 rounded-lg shadow-md text-white">
            <p class="text-sm opacity-90">Productos</p>
            <p class="text-2xl font-bold">{{ $totalProducts }}</p>
        </div>
    </div>

    <!-- Row 3: Gráficos y Tablas -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Gráfico: Doctores por Especialidad -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Doctores por Especialidad</h3>
            <canvas id="doctorsBySpecialtyChart" class="w-full h-64"></canvas>
        </div>

        <!-- Gráfico: Servicios Últimos 7 Días -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Servicios Últimos 7 Días</h3>
            <canvas id="servicesDailyChart" class="w-full h-64"></canvas>
        </div>
    </div>

    <!-- Row 4: Ventas y Compras Trimestre -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Ventas y Compras - Últimos 3 Meses</h3>
        <canvas id="quarterlyChart" class="w-full h-64"></canvas>
    </div>

    <!-- Row 5: Tablas de Top -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Top Proveedores -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-4">
                <h3 class="text-lg font-semibold text-white">Top Proveedores</h3>
                <p class="text-sm text-blue-100">Principales proveedores de la clínica</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proveedor</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Órdenes</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($topSuppliers as $supplier)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $supplier['name'] }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ $supplier['total_orders'] }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-green-600">{{ number_format($supplier['total_purchases'], 2) }} Bs.</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-gray-500">No hay proveedores registrados</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Pacientes -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 p-4">
                <h3 class="text-lg font-semibold text-white">Top 10 Pacientes</h3>
                <p class="text-sm text-purple-100">Pacientes que más asisten a la clínica</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paciente</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visitas</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($topPatients as $patient)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $patient['name'] }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-purple-100 text-purple-800">
                                    {{ $patient['visits'] }} {{ $patient['visits'] === 1 ? 'visita' : 'visitas' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="px-4 py-8 text-center text-gray-500">No hay pacientes con visitas registradas</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Gráfico: Doctores por Especialidad
        const doctorsBySpecialtyCtx = document.getElementById('doctorsBySpecialtyChart').getContext('2d');
        const doctorsBySpecialtyChart = new Chart(doctorsBySpecialtyCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($doctorsBySpecialty->pluck('name')) !!},
                datasets: [{
                    label: 'Cantidad de Doctores',
                    data: {!! json_encode($doctorsBySpecialty->pluck('count')) !!},
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(251, 146, 60, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(234, 179, 8, 0.8)',
                    ],
                    borderColor: [
                        'rgba(59, 130, 246, 1)',
                        'rgba(139, 92, 246, 1)',
                        'rgba(236, 72, 153, 1)',
                        'rgba(251, 146, 60, 1)',
                        'rgba(34, 197, 94, 1)',
                        'rgba(234, 179, 8, 1)',
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Gráfico: Servicios Últimos 7 Días
        const servicesDailyCtx = document.getElementById('servicesDailyChart').getContext('2d');
        const servicesDailyChart = new Chart(servicesDailyCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($serviceLabels) !!},
                datasets: [{
                    label: 'Servicios',
                    data: {!! json_encode($servicesByDay) !!},
                    borderColor: 'rgba(59, 130, 246, 1)',
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 3,
                    pointRadius: 6,
                    pointBackgroundColor: 'rgba(59, 130, 246, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Gráfico: Ventas y Compras Últimos 3 Meses
        const quarterlyCtx = document.getElementById('quarterlyChart').getContext('2d');
        const quarterlyChart = new Chart(quarterlyCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($quarterlyMonths) !!},
                datasets: [
                    {
                        label: 'Ventas',
                        data: {!! json_encode($salesByMonthQ) !!},
                        backgroundColor: 'rgba(34, 197, 94, 0.8)',
                        borderColor: 'rgba(34, 197, 94, 1)',
                        borderWidth: 2
                    },
                    {
                        label: 'Compras',
                        data: {!! json_encode($purchasesByMonthQ) !!},
                        backgroundColor: 'rgba(234, 179, 8, 0.8)',
                        borderColor: 'rgba(234, 179, 8, 1)',
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString() + ' Bs.';
                            }
                        }
                    }
                }
            }
        });
    </script>
</x-admin-layout>

