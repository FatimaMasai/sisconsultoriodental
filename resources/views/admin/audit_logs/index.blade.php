<x-admin-layout>
    <div class="flex justify-between items-center mb-6">
        <x-label class="text-black dark:text-white text-xl font-semibold">
            <i class="fa-solid fa-clipboard-list text-gray-400 mr-1"></i>
            Auditoría de anulaciones
        </x-label>
    </div>

    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
        Registro de quién anuló qué venta o compra y cuándo. Por ahora solo se registran anulaciones.
    </p>

    @if ($logs->count())
        <div class="relative overflow-x-auto">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-3 py-2">Fecha</th>
                        <th scope="col" class="px-3 py-2">Usuario</th>
                        <th scope="col" class="px-3 py-2">Acción</th>
                        <th scope="col" class="px-3 py-2">Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($logs as $log)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                            <td class="px-3 py-2 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-3 py-2">{{ $log->user->name ?? 'Usuario eliminado' }}</td>
                            <td class="px-3 py-2">
                                @if ($log->action === 'sale.cancelled')
                                    <span class="text-red-600 font-semibold">Anuló venta</span>
                                @elseif ($log->action === 'purchase.cancelled')
                                    <span class="text-red-600 font-semibold">Anuló compra</span>
                                @else
                                    {{ $log->action }}
                                @endif
                            </td>
                            <td class="px-3 py-2">{{ $log->description }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4">
                {{ $logs->links() }}
            </div>
        </div>
    @else
        <div class="flex items-center p-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
            <span class="font-medium">Info:</span>&nbsp;todavía no hay ninguna anulación registrada.
        </div>
    @endif
</x-admin-layout>
