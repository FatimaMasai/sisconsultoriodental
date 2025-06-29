<x-admin-layout>
    <div class="flex justify-between items-center mb-6">
        <div class="">
            <x-label class="text-black text-xl font-semibold">
                Listado de Compras
            </x-label>
        </div>
        <div class="">
            @can('admin.purchases.create')
                <a href="{{route('admin.purchases.create')}}" class="btn btn-green">
                    Nuevo
                </a>
            @endcan
        </div>
    </div> 

    

    @if ($purchases->count())

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
                            Fecha
                        </th> 
                        <th scope="col" class="px-3 py-2">
                            Pago
                        </th>  
                        <th scope="col" class="px-3 py-2">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchases as $purchase) 

                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                            <th scope="row" class="px-1 py-1 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{$purchase->id}}
                            </th>
                            <td class="px-3 py-2">
                                {{$purchase->supplier->person->name}} {{$purchase->supplier->person->last_name_father}} {{$purchase->supplier->person->last_name_mother}}  
                            </td>   

                            <td class="px-3 py-2">
                                {{ $purchase->date }} 
                            </td> 
                            <td class="px-3 py-2">
                                Bs. {{ $purchase->total }} 
                            </td>  

                            <td class="px-3 py-2" >
                                <div class="flex space-x-2"> 
                                     <a href="{{ route('admin.purchases.print', $purchase->id) }}" class="btn btn-orange text-xs" target="_blank">PDF</a> 
                                </div>

                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>

            <div class="mt-4">
                {{$purchases->links()}}
            </div>

        </div>
        
    @else

        <div class="flex items-center p-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
            <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
            </svg>

            <span class="sr-only">Info</span>
            <div>
                <span class="font-medium">Info alert!</span> Todavia no hay compras registrada.
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