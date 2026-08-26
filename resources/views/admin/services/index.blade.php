<x-admin-layout>
    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between mb-6">
        <x-label class="text-black text-lg sm:text-xl font-semibold">
            Listado de Servicios
        </x-label>
        <div class="flex flex-wrap items-center gap-2">
            @can('admin.services.pdf')
                <a href="{{ route('admin.services.pdf') }}" class="btn btn-orange" target="_blank">
                    <i class="fa-solid fa-file-pdf mr-1"></i> PDF
                </a>
                <a href="{{ route('admin.services.excel') }}" class="btn btn-green">
                    <i class="fa-solid fa-file-excel mr-1"></i> Excel
                </a>
            @endcan

            @can('admin.services.create')
                <a href="{{route('admin.services.create')}}" class="btn btn-green">Nuevo</a>
            @endcan
        </div>
    </div>

    {{-- Filtro de búsqueda --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
        <form method="GET" action="{{ route('admin.services.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative flex-1 sm:min-w-[220px]">
                <label class="sr-only">Buscar</label>
                <input type="text" name="search" value="{{ request('search') }}"
                    class="input-label rounded-lg w-full" placeholder="Buscar por servicio o categoría">
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button type="submit" class="btn btn-blue rounded-lg text-sm whitespace-nowrap">
                    <i class="fa-solid fa-magnifying-glass mr-1"></i> Buscar
                </button>
                @if (request('search'))
                    <a href="{{ route('admin.services.index') }}" class="btn btn-gray rounded-lg text-sm whitespace-nowrap">
                        <i class="fa-solid fa-xmark mr-1"></i> Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>

    @if ($services->count())

        <div class="relative overflow-x-auto">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-3 py-2">ID</th>
                        <th scope="col" class="px-3 py-2">Categoria</th>
                        <th scope="col" class="px-3 py-2">Servicio</th>
                        <th scope="col" class="px-3 py-2">Precio</th>
                        <th scope="col" class="px-3 py-2">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($services as $service)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                            <th scope="row" class="px-3 py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $service->id }}
                            </th>
                            <td class="px-3 py-2">
                                {{ $service->serviceCategory->name ?? '—' }}
                            </td>
                            <td class="px-3 py-2">
                                {{ $service->name }}
                            </td>
                            <td class="px-3 py-2">
                                {{ $service->price }} Bs.
                            </td>
                            <td class="px-3 py-2">
                                <div class="flex space-x-2">
                                    @can('admin.services.edit')
                                        <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-blue text-xs">Editar</a>
                                    @endcan

                                    @can('admin.services.destroy')
                                        <form class="delete-form" action="{{ route('admin.services.destroy', $service) }}" method="POST">
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
                {{ $services->links() }}
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
                @if (request('search'))
                    No se encontraron servicios con esa búsqueda.
                @else
                    Todavia no hay servicios registrados.
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
