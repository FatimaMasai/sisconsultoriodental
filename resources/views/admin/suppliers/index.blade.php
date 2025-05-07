<x-admin-layout>
    <div class="flex justify-between items-center mb-6">
        <div class="">
            <x-label class="text-black text-xl font-semibold">
                Listado de Proveedores
            </x-label>
        </div>
        <div class="">
            @can('admin.suppliers.create')
            <a href="{{route('admin.suppliers.create')}}" class="btn btn-green">
                Nuevo
            </a>
            @endcan
        </div>
    </div> 

    

    @if ($suppliers->count())

        <div class="relative overflow-x-auto">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-1 py-1">
                            ID
                        </th>
                        <th scope="col" class="px-3 py-2">
                            Proveedor
                        </th> 
                        <th scope="col" class="px-3 py-2">
                            Nit 
                        </th> 
                        <th scope="col" class="px-3 py-2">
                            Empresa
                        </th> 
                        <th scope="col" class="px-3 py-2">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($suppliers as $supplier) 

                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                            <th scope="row" class="px-1 py-1 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{$supplier->id}}
                            </th>
                            <td class="px-3 py-2">
                                {{$supplier->person->name}} {{$supplier->person->last_name_father}} {{$supplier->person->last_name_mother}}
                            </td>    

                            <td class="px-3 py-2">
                                {{ $supplier->nit }} 
                            </td> 
                            <td class="px-3 py-2">
                                {{ $supplier->company }}    
                            </td> 
 

                            <td class="px-3 py-2" >
                                <div class="flex space-x-2">

                                
                                    @can('admin.suppliers.edit')
                                    <a href="{{route('admin.suppliers.edit', $supplier)}}" class="btn btn-blue text-xs">Editar</a>
                                    @endcan
                                    
                                    @can('admin.suppliers.destroy')
                                    <form class="delete-form" action="{{route('admin.suppliers.destroy', $supplier)}}" method="POST">

                                        @csrf
                                        @method('DELETE')
        
                                        <button class="btn btn-red text-xs">
                                            Eliminar
                                        </button>

                                    </form>
                                    @endcan

                                </div>

                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>

            <div class="mt-4">
                {{$suppliers->links()}}
            </div>

        </div>
        
    @else

        <div class="flex items-center p-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
            <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
            </svg>

            <span class="sr-only">Info</span>
            <div>
                <span class="font-medium">Info alert!</span> Todavia no hay proveedor registrados.
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