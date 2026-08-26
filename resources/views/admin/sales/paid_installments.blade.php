<x-admin-layout>
    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between mb-6">
        <x-label class="text-black text-lg sm:text-xl font-semibold">
            <i class="fa-solid fa-money-check-dollar text-gray-400 mr-1"></i>
            Cuotas Pagadas
        </x-label>
        <div class="flex flex-wrap items-center gap-2">
            @can('admin.sales.index')
                <a href="{{ route('admin.installments.paid.pdf', request()->query()) }}" class="btn btn-orange" target="_blank">
                    <i class="fa-solid fa-file-pdf mr-1"></i> PDF
                </a>
                <a href="{{ route('admin.installments.paid.excel', request()->query()) }}" class="btn btn-green">
                    <i class="fa-solid fa-file-excel mr-1"></i> Excel
                </a>
            @endcan
        </div>
    </div>

    {{-- Tarjetas resumen del reporte --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">Total cobrado en cuotas</p>
            <p class="text-xl font-bold text-green-600">Bs. {{ number_format($totalCobrado, 0, '', '.') }}</p>
        </div>

        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">Cuotas pagadas</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $totalCuotas }}</p>
        </div>
    </div>

    {{-- Filtro de búsqueda --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
        <div class="flex items-center gap-2 mb-4">
            <i class="fa-solid fa-filter text-gray-400"></i>
            <x-label class="text-black dark:text-white text-base font-semibold">Filtrar Cuotas Pagadas</x-label>
        </div>

        <form method="GET" action="{{ route('admin.installments.paid') }}">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:flex-wrap">
                <div class="relative flex-1 sm:min-w-[220px]">
                    <label class="sr-only">Paciente o # de venta</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="input-label rounded-lg w-full" placeholder="Paciente o N° de venta">
                </div>

                <div class="shrink-0">
                    <label class="sr-only">Método de pago</label>
                    <x-select name="payment_method" class="input-label rounded-lg w-full sm:w-36">
                        <option value="">Todos los métodos</option>
                        <option value="Efectivo" @selected(request('payment_method') == 'Efectivo')>Efectivo</option>
                        <option value="QR" @selected(request('payment_method') == 'QR')>QR</option>
                        <option value="Transferencia" @selected(request('payment_method') == 'Transferencia')>Transferencia</option>
                    </x-select>
                </div>

                <div class="shrink-0">
                    <label class="sr-only">Pagadas desde</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="input-label rounded-lg w-full sm:w-40">
                </div>

                <div class="shrink-0">
                    <label class="sr-only">Pagadas hasta</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="input-label rounded-lg w-full sm:w-40">
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <button type="submit" class="btn btn-blue rounded-lg text-sm whitespace-nowrap">
                        <i class="fa-solid fa-magnifying-glass mr-1"></i> Buscar
                    </button>
                    @if ($hasFilters)
                        <a href="{{ route('admin.installments.paid') }}" class="btn btn-gray rounded-lg text-sm whitespace-nowrap">
                            <i class="fa-solid fa-xmark mr-1"></i> Limpiar
                        </a>
                    @endif
                </div>
            </div>

            <p class="text-sm text-gray-500 dark:text-gray-400 mt-3">
                @if ($hasFilters)
                    {{ $installments->total() }} resultado(s) con estos filtros
                @else
                    {{ $installments->total() }} cuota(s) pagada(s) en total
                @endif
            </p>
        </form>
    </div>

    @if ($installments->count())

        <div class="relative overflow-x-auto">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-3 py-2">Comprobante</th>
                        <th scope="col" class="px-3 py-2">Paciente</th>
                        <th scope="col" class="px-3 py-2">Doctor</th>
                        <th scope="col" class="px-3 py-2">Cuota</th>
                        <th scope="col" class="px-3 py-2">Monto</th>
                        <th scope="col" class="px-3 py-2">Método</th>
                        <th scope="col" class="px-3 py-2">Fecha de pago</th>
                        <th scope="col" class="px-3 py-2">Compartir</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($installments as $installment)
                        @php
                            $sale = $installment->sale;
                            $pacientePersona = $sale->patient->person;
                            $nombrePaciente = trim($pacientePersona->name . ' ' . $pacientePersona->last_name_father . ' ' . $pacientePersona->last_name_mother);
                            $metodoPago = optional($installment->payments->first())->payment_method ?? '—';

                            $waReceiptPayload = [
                                'telefono' => $pacientePersona->whatsapp_phone,
                                'monto' => 'Bs. ' . number_format($installment->amount, 0, '', '.'),
                                'concepto' => 'Cuota #' . $installment->number,
                                'paciente' => $nombrePaciente,
                                'comprobante' => $sale->numero,
                                'metodo' => $metodoPago,
                                'fecha' => $installment->paid_at->format('d/m/Y H:i'),
                                'saldo' => 'Bs. ' . number_format($sale->saldo_pendiente, 0, '', '.'),
                                'mensaje' => "Hola {$nombrePaciente}, aquí está el comprobante de tu pago en Mi Consulta.",
                            ];
                        @endphp
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/40">
                            <td class="px-3 py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                <a href="{{ route('admin.sales.show', $sale) }}" class="text-blue-600 hover:underline">
                                    {{ $sale->numero }}
                                </a>
                            </td>
                            <td class="px-3 py-2">{{ $nombrePaciente }}</td>
                            <td class="px-3 py-2">
                                {{ $sale->doctor->person->name }} {{ $sale->doctor->person->last_name_father }}
                            </td>
                            <td class="px-3 py-2">#{{ $installment->number }}</td>
                            <td class="px-3 py-2">Bs. {{ number_format($installment->amount, 0, '', '.') }}</td>
                            <td class="px-3 py-2">{{ $metodoPago }}</td>
                            <td class="px-3 py-2">{{ $installment->paid_at->format('d/m/Y H:i') }}</td>
                            <td class="px-3 py-2">
                                <div class="flex items-center gap-2">
                                    @if ($pacientePersona->whatsapp_phone)
                                        <button type="button" onclick='shareReceiptAsImage(@json($waReceiptPayload))'
                                           class="btn btn-green text-xs whitespace-nowrap inline-flex items-center gap-1">
                                            <i class="fa-brands fa-whatsapp"></i> Compartir
                                        </button>
                                    @else
                                        <span class="text-xs text-gray-400" title="El paciente no tiene teléfono registrado">
                                            <i class="fa-brands fa-whatsapp"></i> Sin teléfono
                                        </span>
                                    @endif
                                    <button type="button" onclick='downloadReceiptAsImage(@json($waReceiptPayload))'
                                       class="btn btn-gray text-xs whitespace-nowrap inline-flex items-center gap-1">
                                        <i class="fa-solid fa-download"></i> Descargar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4">
                {{ $installments->links() }}
            </div>
        </div>

    @else

        <div class="flex items-center p-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
            <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
            </svg>
            <span class="sr-only">Info</span>
            <div>
                <span class="font-medium">Info alert!</span>
                @if ($hasFilters)
                    No se encontraron cuotas pagadas con esos filtros.
                @else
                    Todavía no se registró ninguna cuota pagada.
                @endif
            </div>
        </div>

    @endif

    @include('admin.sales.partials.whatsapp-receipt')
</x-admin-layout>
