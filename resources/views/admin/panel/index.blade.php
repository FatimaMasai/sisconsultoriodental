<x-admin-layout>
    <div class="mb-6">
        <x-label class="text-black dark:text-white text-xl font-semibold">
            Panel de Control
        </x-label>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Resumen general de la clínica.
        </p>
    </div>

    {{-- Resumen principal: siempre en una sola fila horizontal en escritorio, apilado/2x2 en pantallas chicas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-5">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 font-medium">Ventas Totales</p>
                <div class="w-9 h-9 rounded-full bg-green-50 dark:bg-green-900/20 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-sack-dollar text-green-600 dark:text-green-400 text-sm"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalSales, 0, '', '.') }} Bs.</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Ventas activas, sin contar anuladas</p>
        </div>

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-5">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 font-medium">Compras Totales</p>
                <div class="w-9 h-9 rounded-full bg-orange-50 dark:bg-orange-900/20 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-cart-shopping text-orange-600 dark:text-orange-400 text-sm"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalPurchases, 0, '', '.') }} Bs.</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Compras activas, sin contar anuladas</p>
        </div>

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-5">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 font-medium">Cuotas Vencidas</p>
                <div class="w-9 h-9 rounded-full bg-red-50 dark:bg-red-900/20 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-triangle-exclamation text-red-500 dark:text-red-400 text-sm"></i>
                </div>
            </div>

            @if ($cuotasVencidasCount > 0)
                <p class="text-2xl font-bold text-red-600">Bs. {{ number_format($cuotasVencidasMonto, 0, '', '.') }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ $cuotasVencidasCount }} {{ $cuotasVencidasCount == 1 ? 'cuota vencida' : 'cuotas vencidas' }} sin pagar
                </p>
            @else
                <p class="text-2xl font-bold text-green-600">
                    <i class="fa-solid fa-circle-check text-lg"></i> Al día
                </p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">No hay cuotas vencidas</p>
            @endif
        </div>

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-5">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 font-medium">Cuotas por Cobrar Este Mes</p>
                <div class="w-9 h-9 rounded-full bg-yellow-50 dark:bg-yellow-900/20 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-calendar-days text-yellow-600 dark:text-yellow-400 text-sm"></i>
                </div>
            </div>

            @if ($cuotasMesCount > 0)
                <p class="text-2xl font-bold text-yellow-600">Bs. {{ number_format($cuotasMesMonto, 0, '', '.') }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ $cuotasMesCount }} {{ $cuotasMesCount == 1 ? 'cuota' : 'cuotas' }} con vencimiento este mes
                </p>
            @else
                <p class="text-2xl font-bold text-gray-400 dark:text-gray-500">—</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">No hay cuotas por cobrar este mes</p>
            @endif
        </div>
    </div>

    {{-- Resumen general: datos de referencia, más compactos --}}
    @php
        $resumen = [
            ['label' => 'Pacientes', 'value' => $totalPatients, 'icon' => 'fa-users', 'color' => 'blue'],
            ['label' => 'Doctores', 'value' => $totalDoctors, 'icon' => 'fa-user-doctor', 'color' => 'blue'],
            ['label' => 'Proveedores', 'value' => $totalSuppliers, 'icon' => 'fa-truck', 'color' => 'blue'],
            ['label' => 'Especialidades', 'value' => $totalSpecialities, 'icon' => 'fa-stethoscope', 'color' => 'blue'],
            ['label' => 'Servicios', 'value' => $totalServices, 'icon' => 'fa-tooth', 'color' => 'blue'],
            ['label' => 'Productos', 'value' => $totalProducts, 'icon' => 'fa-box', 'color' => 'blue'],
        ];
    @endphp

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        @foreach ($resumen as $item)
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3 text-center">
                <div class="w-8 h-8 mx-auto mb-2 rounded-full bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center">
                    <i class="fa-solid {{ $item['icon'] }} text-blue-500 dark:text-blue-400 text-xs"></i>
                </div>
                <p class="text-lg font-bold text-gray-900 dark:text-white leading-tight">{{ $item['value'] }}</p>
                <p class="text-[11px] text-gray-500 dark:text-gray-400">{{ $item['label'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Tendencia mensual --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-5 mb-6">
        <x-label class="text-black dark:text-white text-base font-semibold mb-4 block">
            Ventas (Contado / Crédito) y Compras &mdash; Últimos 12 Meses
        </x-label>
        <div class="relative h-72 sm:h-80">
            <canvas id="monthlyChart"></canvas>
        </div>
    </div>

    {{-- Distribución de la clínica --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-5 mb-6">
        <x-label class="text-black dark:text-white text-base font-semibold mb-4 block">
            Distribución de la Clínica
        </x-label>
        <div class="relative h-64 sm:h-72">
            <canvas id="totalsChart"></canvas>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Tendencia mensual: ventas (Contado / Crédito) y compras.
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($months) !!},
                datasets: [
                    {
                        label: 'Ventas Contado',
                        data: {!! json_encode($salesContadoByMonth) !!},
                        borderColor: '#4ade80',
                        backgroundColor: 'rgba(74, 222, 128, 0.2)',
                        tension: 0.4,
                        fill: true,
                    },
                    {
                        label: 'Ventas Crédito',
                        data: {!! json_encode($salesCreditoByMonth) !!},
                        borderColor: '#60a5fa',
                        backgroundColor: 'rgba(96, 165, 250, 0.2)',
                        tension: 0.4,
                        fill: true,
                    },
                    {
                        label: 'Compras',
                        data: {!! json_encode($purchasesByMonth) !!},
                        borderColor: '#fb923c',
                        backgroundColor: 'rgba(251, 146, 60, 0.2)',
                        tension: 0.4,
                        fill: true,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' },
                },
                scales: {
                    y: { beginAtZero: true },
                },
            },
        });

        // Distribución de la clínica: solo cantidades (unidades comparables entre sí).
        const totalsCtx = document.getElementById('totalsChart').getContext('2d');
        new Chart(totalsCtx, {
            type: 'bar',
            data: {
                labels: ['Pacientes', 'Doctores', 'Proveedores', 'Especialidades', 'Servicios', 'Productos'],
                datasets: [{
                    label: 'Cantidad',
                    data: [
                        {{ $totalPatients }},
                        {{ $totalDoctors }},
                        {{ $totalSuppliers }},
                        {{ $totalSpecialities }},
                        {{ $totalServices }},
                        {{ $totalProducts }},
                    ],
                    backgroundColor: '#60a5fa',
                    borderRadius: 6,
                    maxBarThickness: 48,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } },
                },
            },
        });
    </script>
</x-admin-layout>
