<x-admin-layout>
    <div class="flex justify-between items-center mb-6">
        <x-label class="text-black text-xl font-semibold">
            <i class="fa-solid fa-file-invoice-dollar text-gray-400 mr-1"></i>
            Detalle de Venta {{ $sale->numero }}
        </x-label>

        <a href="{{ route('admin.sales.index') }}" class="btn btn-gray text-sm">
            <i class="fa-solid fa-arrow-left mr-1"></i> Volver
        </a>
    </div>

    <x-validation-errors class="mb-4" />

    @if (session('info'))
        <div class="flex items-center p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
            {{ session('info') }}
        </div>
    @endif

    {{-- Tarjetas resumen: lo primero que se necesita ver de un vistazo --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">Total de la venta</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white">Bs. {{ number_format($sale->total, 0, '', '.') }}</p>
        </div>

        @if ($sale->isCredito())
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">Cuota inicial</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white">Bs. {{ number_format($sale->initial_amount, 0, '', '.') }}</p>
            </div>

            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">Saldo pendiente</p>
                <p class="text-xl font-bold {{ $sale->saldo_pendiente > 0 ? 'text-red-500' : 'text-green-600' }}">
                    Bs. {{ number_format($sale->saldo_pendiente, 0, '', '.') }}
                </p>
            </div>

            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Estado del crédito</p>
                @php $estadoCredito = $sale->estado_credito; @endphp
                @if ($estadoCredito === 'Completado')
                    <x-badge color="green"><i class="fa-solid fa-check mr-1"></i> Completado</x-badge>
                @elseif ($estadoCredito === 'Con mora')
                    <x-badge color="red"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Con mora</x-badge>
                @elseif ($estadoCredito === 'Anulado')
                    <x-badge color="gray">Anulado</x-badge>
                @else
                    <x-badge color="yellow">Al día</x-badge>
                @endif
            </div>
        @else
            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">Método de pago</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ optional($sale->payments->first())->payment_method ?? '—' }}
                </p>
            </div>

            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Estado de la venta</p>
                @if ($sale->status == 1)
                    <x-badge color="green"><i class="fa-solid fa-check mr-1"></i> Activa</x-badge>
                @else
                    <x-badge color="red">Anulada</x-badge>
                @endif
            </div>
        @endif
    </div>

    {{-- Información general --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Paciente</p>
                <p class="font-semibold text-gray-900 dark:text-white">
                    {{ $sale->patient->person->name }} {{ $sale->patient->person->last_name_father }} {{ $sale->patient->person->last_name_mother }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Doctor</p>
                <p class="font-semibold text-gray-900 dark:text-white">
                    {{ $sale->doctor->person->name }} {{ $sale->doctor->person->last_name_father }} {{ $sale->doctor->person->last_name_mother }}
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Fecha de venta</p>
                <p class="font-semibold text-gray-900 dark:text-white">{{ $sale->sale_date }}</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-x-2 gap-y-2 mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
            <span class="text-sm text-gray-500 dark:text-gray-400">Tipo de venta:</span>
            @if ($sale->isCredito())
                <x-badge color="blue">Crédito (a cuotas)</x-badge>
            @else
                <x-badge color="gray">Contado</x-badge>
            @endif

            @if ($sale->isCredito())
                <span class="text-sm text-gray-500 dark:text-gray-400 ml-4">Estado de la venta:</span>
                @if ($sale->status == 1)
                    <x-badge color="green">Activa</x-badge>
                @else
                    <x-badge color="red">Anulada</x-badge>
                @endif
            @endif
        </div>
    </div>

    {{-- Servicios de la venta --}}
    <x-label class="text-black text-lg font-semibold mb-4">
        Servicios
    </x-label>

    <div class="relative overflow-x-auto mb-6">
        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">Servicio</th>
                    <th scope="col" class="px-6 py-3">Cantidad</th>
                    <th scope="col" class="px-6 py-3">Precio</th>
                    <th scope="col" class="px-6 py-3">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->saleDetails as $detail)
                    <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700">
                        <td class="px-6 py-4">{{ $detail->service->name }}</td>
                        <td class="px-6 py-4">{{ $detail->quantity }}</td>
                        <td class="px-6 py-4">Bs. {{ number_format($detail->price, 0, '', '.') }}</td>
                        <td class="px-6 py-4">Bs. {{ number_format($detail->subtotal, 0, '', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($sale->isCredito())
        @php
            $cuotasOrdenadas = $sale->installments->sortBy('number');
            $cuotasActivas = $cuotasOrdenadas->where('status', '!=', 'Anulada');
            $cuotasPagadas = $cuotasActivas->where('status', 'Pagada')->count();
            $totalCuotasActivas = $cuotasActivas->count();
            $progreso = $totalCuotasActivas > 0 ? (int) round(($cuotasPagadas / $totalCuotasActivas) * 100) : 0;
        @endphp

        <div class="flex items-center justify-between mb-2">
            <x-label class="text-black text-lg font-semibold">
                Plan de Cuotas
            </x-label>
            @if ($totalCuotasActivas > 0)
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $cuotasPagadas }} de {{ $totalCuotasActivas }} cuotas pagadas ({{ $progreso }}%)
                </span>
            @endif
        </div>

        @if ($totalCuotasActivas > 0)
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 mb-4">
                <div class="bg-green-500 h-2.5 rounded-full" style="width: {{ $progreso }}%"></div>
            </div>
        @endif

        <div class="relative overflow-x-auto mb-6">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">#</th>
                        <th scope="col" class="px-6 py-3">Fecha de vencimiento</th>
                        <th scope="col" class="px-6 py-3">Monto</th>
                        <th scope="col" class="px-6 py-3">Estado</th>
                        <th scope="col" class="px-6 py-3">Fecha de pago</th>
                        <th scope="col" class="px-6 py-3">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cuotasOrdenadas as $installment)
                        @php $estado = $installment->estado_actual; @endphp
                        <tr class="border-b dark:border-gray-700 {{ $estado === 'Vencida' ? 'bg-red-50 dark:bg-red-900/10' : 'bg-white dark:bg-gray-800' }}">
                            <td class="px-6 py-4">{{ $installment->number }}</td>
                            <td class="px-6 py-4">{{ $installment->due_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">Bs. {{ number_format($installment->amount, 0, '', '.') }}</td>
                            <td class="px-6 py-4">
                                @if ($estado === 'Pagada')
                                    <x-badge color="green">Pagada</x-badge>
                                @elseif ($estado === 'Vencida')
                                    <x-badge color="red">Vencida</x-badge>
                                @elseif ($estado === 'Anulada')
                                    <x-badge color="gray">Anulada</x-badge>
                                @else
                                    <x-badge color="yellow">Pendiente</x-badge>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                {{ $installment->paid_at ? $installment->paid_at->format('d/m/Y') : '—' }}
                            </td>
                            <td class="px-6 py-4">
                                @if ($estado === 'Pendiente' || $estado === 'Vencida')
                                    @can('admin.sales.payInstallment')
                                        @if ($sale->status == 1)
                                            <form action="{{ route('admin.sales.installments.pay', [$sale, $installment]) }}" method="POST" class="flex items-center gap-2">
                                                @csrf
                                                <select name="payment_method" class="text-xs rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:border-blue-500 focus:ring-blue-500" required>
                                                    <option value="Efectivo">Efectivo</option>
                                                    <option value="Transferencia">Transferencia</option>
                                                    <option value="QR">QR</option>
                                                </select>
                                                <button type="submit" class="btn btn-green text-xs whitespace-nowrap">
                                                    <i class="fa-solid fa-check mr-1"></i> Registrar pago
                                                </button>
                                            </form>
                                        @endif
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <x-label class="text-black text-lg font-semibold mb-4">
            Historial de Pagos
        </x-label>

        <div class="relative overflow-x-auto mb-6">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">Concepto</th>
                        <th scope="col" class="px-6 py-3">Monto</th>
                        <th scope="col" class="px-6 py-3">Método</th>
                        <th scope="col" class="px-6 py-3">Fecha</th>
                        <th scope="col" class="px-6 py-3">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sale->payments->sortByDesc('created_at') as $payment)
                        <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700">
                            <td class="px-6 py-4">
                                @if ($payment->payment_status === 'Cuota Inicial')
                                    Cuota inicial
                                @elseif ($payment->installment)
                                    Cuota #{{ $payment->installment->number }}
                                @else
                                    {{ $payment->payment_status }}
                                @endif
                            </td>
                            <td class="px-6 py-4">Bs. {{ number_format($payment->amount, 0, '', '.') }}</td>
                            <td class="px-6 py-4">{{ $payment->payment_method }}</td>
                            <td class="px-6 py-4">{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4">
                                @if ($payment->payment_status === 'Anulado')
                                    <x-badge color="gray">Anulado</x-badge>
                                @else
                                    <x-badge color="green">{{ $payment->payment_status }}</x-badge>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700">
                            <td class="px-6 py-4 text-gray-400" colspan="5">Todavía no se registró ningún pago.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        <x-label class="text-black text-lg font-semibold mb-4">
            Pago
        </x-label>

        <div class="relative overflow-x-auto mb-6">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">Monto</th>
                        <th scope="col" class="px-6 py-3">Método</th>
                        <th scope="col" class="px-6 py-3">Fecha</th>
                        <th scope="col" class="px-6 py-3">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sale->payments as $payment)
                        <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700">
                            <td class="px-6 py-4">Bs. {{ number_format($payment->amount, 0, '', '.') }}</td>
                            <td class="px-6 py-4">{{ $payment->payment_method }}</td>
                            <td class="px-6 py-4">{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4">
                                @if ($payment->payment_status === 'Anulado')
                                    <x-badge color="gray">Anulado</x-badge>
                                @else
                                    <x-badge color="green">{{ $payment->payment_status }}</x-badge>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-admin-layout>
