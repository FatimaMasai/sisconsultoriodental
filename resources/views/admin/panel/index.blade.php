<x-admin-layout>
    <x-label class="text-black text-xl font-semibold mb-4">
        Dashboard
    </x-label>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <!-- Total de Ventas -->
        <div class="bg-white p-6 rounded-lg shadow-md"> 
            <x-label class="text-black text-xl font-semibold mb-4">
                Ventas Totales
            </x-label>

            @if ($totalSales > 0)
                <p class="text-2xl font-bold text-green-500">{{ $totalSales }} Bs.</p>
            @else
                <div class="flex items-center p-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400"
                    role="alert">
                    <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                    </svg>

                    <span class="sr-only">Info</span>
                    <div>
                        <span class="font-medium">Bs. 0</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Total de Compras -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <x-label class="text-black text-xl font-semibold mb-4">
                Compras Totales
            </x-label> 

            @if ($totalPurchases > 0)
                <p class="text-2xl font-bold text-yellow-500">{{ $totalPurchases }} Bs.</p>
            @else
                <div class="flex items-center p-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400"
                    role="alert">
                    <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                    </svg>

                    <span class="sr-only">Info</span>
                    <div>
                        <span class="font-medium">Bs. 0</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Total de Productos -->
        <div class="bg-white p-6 rounded-lg shadow-md"> 
            <x-label class="text-black text-xl font-semibold mb-4">
                Productos 
            </x-label>
            @if ($totalProducts > 0)
                <p class="text-3xl font-bold text-blue-500">{{ $totalProducts }}</p>
            @else
                <div class="flex items-center p-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400"
                    role="alert">
                    <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                    </svg>

                    <span class="sr-only">Info</span>
                    <div>
                        <span class="font-medium">No hay productos</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Total de Pacientes -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <x-label class="text-black text-xl font-semibold mb-4">
                Pacientes 
            </x-label> 
            @if ($totalPatients > 0)
                <p class="text-3xl font-bold text-blue-500">{{ $totalPatients }}</p>
            @else
                <div class="flex items-center p-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400"
                    role="alert">
                    <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                    </svg>

                    <span class="sr-only">Info</span>
                    <div>
                        <span class="font-medium">No hay pacientes</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <!-- Total de Proveedores -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <x-label class="text-black text-xl font-semibold mb-4">
                Proveedores 
            </x-label> 
 
            @if ($totalSuppliers > 0)
                <p class="text-3xl font-bold text-blue-500">{{ $totalSuppliers }}</p>
            @else
                <div class="flex items-center p-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400"
                    role="alert">
                    <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                    </svg>

                    <span class="sr-only">Info</span>
                    <div>
                        <span class="font-medium">No hay proveedores</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Total de Doctores -->
        <div class="bg-white p-6 rounded-lg shadow-md"> 
            <x-label class="text-black text-xl font-semibold mb-4">
                Doctores 
            </x-label>

            @if ($totalDoctors > 0)
                <p class="text-3xl font-bold text-blue-500">{{ $totalDoctors }}</p>
            @else
                <div class="flex items-center p-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400"
                    role="alert">
                    <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                    </svg>

                    <span class="sr-only">Info</span>
                    <div>
                        <span class="font-medium">No hay doctores</span>
                    </div>
                </div>
            @endif
        </div>
        <!-- Total de Doctores -->
        <div class="bg-white p-6 rounded-lg shadow-md"> 
            <x-label class="text-black text-xl font-semibold mb-4">
                Especialidades 
            </x-label>
            @if ($totalSpecialities > 0)
                <p class="text-3xl font-bold text-blue-500">{{ $totalSpecialities }}</p>
            @else
                <div class="flex items-center p-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400"
                    role="alert">
                    <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                    </svg>

                    <span class="sr-only">Info</span>
                    <div>
                        <span class="font-medium">No hay especialidades</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Total de Servicios -->
        <div class="bg-white p-6 rounded-lg shadow-md"> 
            <x-label class="text-black text-xl font-semibold mb-4">
                Servicios 
            </x-label>
            @if ($totalServices > 0)
                <p class="text-3xl font-bold text-blue-500">{{ $totalServices }}</p>
            @else
                <div class="flex items-center p-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400"
                    role="alert">
                    <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                    </svg>

                    <span class="sr-only">Info</span>
                    <div>
                        <span class="font-medium">No hay servicios</span>
                    </div>
                </div>
            @endif
        </div>
    </div>


    <!-- Totales globales -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-6 ">
        <x-label class="text-black text-xl font-semibold mb-4">
            Totales Globales
        </x-label>
        <canvas id="totalsChart" class="w-96 h-96 mx-auto" ></canvas>
    </div>

    <!-- Tendencia mensual -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-6 ">
        <x-label class="text-black text-xl font-semibold mb-4">
            Ventas y Compras Últimos 12 Meses
        </x-label>
        <canvas id="monthlyChart" class="w-96 h-96 mx-auto"></canvas>
</div>



 
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // 1️⃣ Totales globales
    const totalsCtx = document.getElementById('totalsChart').getContext('2d');
    const totalsChart = new Chart(totalsCtx, {
        type: 'doughnut',
        data: {
            labels: ['Ventas', 'Compras', 'Productos', 'Pacientes', 'Proveedores', 'Doctores', 'Especialidades', 'Servicios'],
            datasets: [{
                label: 'Totales',
                data: [
                    {{ $totalSales }},
                    {{ $totalPurchases }},
                    {{ $totalProducts }},
                    {{ $totalPatients }},
                    {{ $totalSuppliers }},
                    {{ $totalDoctors }},
                    {{ $totalSpecialities }},
                    {{ $totalServices }}
                ],
                backgroundColor: [
                    '#4ade80', '#facc15', '#3b82f6', '#60a5fa', '#a78bfa', '#f472b6', '#f87171', '#34d399'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // 2️⃣ Tendencia mensual
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyChart = new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($months) !!},
            datasets: [
                {
                    label: 'Ventas',
                    data: {!! json_encode($salesByMonth) !!},
                    borderColor: '#4ade80',
                    backgroundColor: 'rgba(74, 222, 128, 0.2)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Compras',
                    data: {!! json_encode($purchasesByMonth) !!},
                    borderColor: '#facc15',
                    backgroundColor: 'rgba(250, 204, 21, 0.2)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>


 


 
</x-admin-layout>