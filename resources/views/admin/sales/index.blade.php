<x-admin-layout>
    <div class="flex justify-between items-center mb-6">
        <div class="">
            <x-label class="text-black text-xl font-semibold">
                Comprobante de servicios
            </x-label>
        </div>
        <div class="">
            @can('admin.sales.create')
                <a href="{{route('admin.sales.create')}}" class="btn btn-green">
                    <i class="fa-solid fa-plus mr-1"></i> Nuevo
                </a>
            @endcan
        </div>
    </div>

    {{-- Filtro de búsqueda --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3 mb-6">
        <form method="GET" action="{{ route('admin.sales.index') }}">
            <div class="flex flex-wrap items-center gap-2">
                <div class="relative flex-1 min-w-[160px]">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 text-gray-400 text-sm">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <x-input type="text" name="search" value="{{ request('search') }}"
                        class="rounded-lg pl-8 w-full" placeholder="Paciente o N° de venta" />
                </div>

                <x-select name="payment_type" class="rounded-lg shrink-0 w-28">
                    <option value="">Todos</option>
                    <option value="Contado" @selected(request('payment_type') == 'Contado')>Contado</option>
                    <option value="Credito" @selected(request('payment_type') == 'Credito')>Crédito</option>
                </x-select>

                <x-input type="date" name="date_from" value="{{ request('date_from') }}"
                    title="Desde" class="rounded-lg shrink-0 w-36" />

                <x-input type="date" name="date_to" value="{{ request('date_to') }}"
                    title="Hasta" class="rounded-lg shrink-0 w-36" />

                <div class="flex items-center gap-2 shrink-0">
                    <button type="submit" class="btn btn-blue rounded-lg text-sm py-1.5">
                        <i class="fa-solid fa-magnifying-glass mr-1"></i> Buscar
                    </button>
                    @if ($hasFilters)
                        <a href="{{ route('admin.sales.index') }}" class="btn btn-gray rounded-lg text-sm py-1.5">
                            <i class="fa-solid fa-xmark mr-1"></i> Limpiar
                        </a>
                    @endif
                </div>
            </div>

            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                @if ($hasFilters)
                    {{ $sales->total() }} resultado(s) con estos filtros
                @else
                    {{ $sales->total() }} venta(s) registrada(s) en total
                @endif
            </p>
        </form>
    </div>

    @if ($sales->count())

        <div class="relative overflow-x-auto">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-3 py-2">
                            N° Venta
                        </th>
                        <th scope="col" class="px-3 py-2">
                            Paciente
                        </th>
                        <th scope="col" class="px-3 py-2">
                            Doctor
                        </th>
                        <th scope="col" class="px-3 py-2">
                            Fecha
                        </th>
                        <th scope="col" class="px-3 py-2">
                            Pago
                        </th>
                        <th scope="col" class="px-3 py-2">
                            Tipo
                        </th>
                        <th scope="col" class="px-3 py-2">
                            Cuotas
                        </th>
                        <th scope="col" class="px-3 py-2">
                            Estado
                        </th>

                        <th scope="col" class="px-3 py-2">
                            Acciones
                        </th>

                    </tr>
                </thead>
                <tbody>
                    @foreach ($sales as $sale)

                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/40">
                            <th scope="row" class="px-3 py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $sale->numero }}
                            </th>
                            <td class="px-3 py-2">
                                {{$sale->patient->person->name}} {{$sale->patient->person->last_name_father}} {{$sale->patient->person->last_name_mother}}
                            </td>
                            <td class="px-3 py-2">
                                {{ $sale->doctor->person->name }}  {{$sale->doctor->person->last_name_father}} {{$sale->doctor->person->last_name_mother}}
                            </td>

                            <td class="px-3 py-2">
                                {{ $sale->sale_date }}
                            </td>
                            <td class="px-3 py-2">
                                {{ number_format($sale->total, 0, '', '.') }} Bs.
                            </td>

                            <td class="px-3 py-2">
                                @if ($sale->payment_type === 'Credito')
                                    <x-badge color="blue">Crédito</x-badge>
                                @else
                                    <x-badge color="gray">Contado</x-badge>
                                @endif
                            </td>

                            <td class="px-3 py-2">
                                @if ($sale->payment_type === 'Credito')
                                    @php $estadoCredito = $sale->estado_credito; @endphp
                                    @if ($estadoCredito === 'Completado')
                                        <x-badge color="green">Completado</x-badge>
                                    @elseif ($estadoCredito === 'Con mora')
                                        <x-badge color="red">Con mora</x-badge>
                                    @elseif ($estadoCredito === 'Anulado')
                                        <span class="text-gray-400">—</span>
                                    @else
                                        <x-badge color="yellow">Al día</x-badge>
                                    @endif
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>

                            <td class="px-3 py-2">
                                @if ($sale->status == 1)
                                    <x-badge color="green">Activa</x-badge>
                                @else
                                    <x-badge color="red">Anulada</x-badge>
                                @endif
                            </td>

                            <td class="px-3 py-2" >
                                <div class="flex space-x-2">
                                    @can('admin.sales.index')
                                        <a href="{{ route('admin.sales.show', $sale) }}" class="btn btn-blue text-xs">
                                            <i class="fa-solid fa-eye mr-1"></i> Ver
                                        </a>
                                    @endcan

                                    @can('admin.sales.print')
                                        <a href="{{ route('admin.sales.print', $sale->id) }}" class="btn btn-orange text-xs" target="_blank">
                                            <i class="fa-solid fa-file-pdf mr-1"></i> PDF
                                        </a>
                                    @endcan

                                    @can('admin.sales.cancel')
                                        @if ($sale->status == 1)
                                        <form action="{{ route('admin.sales.cancel', $sale) }}" method="POST" class="delete-form">
                                            @csrf
                                            <button type="submit" class="btn btn-red text-xs">
                                                <i class="fa-solid fa-ban mr-1"></i> Anular
                                            </button>
                                        </form>
                                        @endif
                                    @endcan
                                </div>

                            </td>

                        </tr>
                    @endforeach

                </tbody>
            </table>

            <div class="mt-4">
                {{$sales->links()}}
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
                    No se encontraron ventas con esos filtros.
                @else
                    Todavia no hay ventas registrada.
                @endif
            </div>
        </div>

    @endif



    {{-- agregando el script de la libreria de sweetalert2 PASO 3--}}

    @push('js')
    <script>
        forms=document.querySelectorAll('.delete-form');
        forms.forEach(form=>{
            form.addEventListener('submit', (e) => {
                e.preventDefault();

                    Swal.fire({
                    title: "¿Está seguro?",
                    text: "¡No podrás revertir esto!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Sí, ¡eliminalo!",
                    cancelButtonText: "Cancelar",
                    }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                    });
            })
        })

    </script>

@endpush


</x-admin-layout>
