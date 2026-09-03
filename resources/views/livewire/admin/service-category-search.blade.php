<div>
    {{-- Buscador en tiempo real: se filtra solo, sin botón ni recargar la página --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
        <div class="flex items-center gap-2 mb-4">
            <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
            <x-label class="text-black dark:text-white text-base font-semibold">Buscar en Categorías de Servicio</x-label>
        </div>

        <div class="relative w-full sm:w-96">
            <input type="text" wire:model.live.debounce.300ms="search"
                class="input-label rounded-lg w-full"
                placeholder="Buscar por nombre">

            <div wire:loading wire:target="search" class="absolute inset-y-0 right-3 flex items-center">
                <i class="fa-solid fa-spinner fa-spin text-gray-400"></i>
            </div>
        </div>

        @if (trim($search) !== '')
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-3">
                {{ $service_categories->total() }} resultado(s) para "{{ $search }}"
            </p>
        @endif
    </div>

    @if ($service_categories->count())

        <div class="relative overflow-x-auto">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-3 py-2">ID</th>
                        <th scope="col" class="px-3 py-2">Nombre</th>
                        <th scope="col" class="px-3 py-2">Fecha de registro</th>
                        <th scope="col" class="px-3 py-2">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($service_categories as $category)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                            <th scope="row" class="px-3 py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $category->id }}
                            </th>
                            <td class="px-3 py-2">
                                {{ $category->name }}
                            </td>
                            <td class="px-3 py-2">
                                {{ $category->created_at?->format('d/m/Y') }}
                            </td>
                            <td class="px-3 py-2">
                                <div class="flex space-x-2">
                                    @can('admin.service_categories.edit')
                                        <a href="{{ route('admin.service_categories.edit', $category) }}" class="btn btn-blue text-xs">Editar</a>
                                    @endcan

                                    @can('admin.service_categories.destroy')
                                        <form class="delete-form" action="{{ route('admin.service_categories.destroy', $category) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-red text-xs">Eliminar</button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4">
                {{ $service_categories->links() }}
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
                @if (trim($search) !== '')
                    No se encontraron categorías con esa búsqueda.
                @else
                    Todavia no hay categorías de servicio registradas.
                @endif
            </div>
        </div>

    @endif
</div>
