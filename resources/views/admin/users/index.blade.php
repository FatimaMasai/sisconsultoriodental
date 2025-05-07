<x-admin-layout>
    <div class="flex justify-between items-center mb-6">
        <div class="">
            <x-label class="text-black text-xl font-semibold">
                Listado de Usuarios
            </x-label>
        </div>
        <div class="">
            @can('admin.users.pdf')
            <a href="{{ route('admin.users.pdf') }}" class="btn btn-orange" target="_blank">PDF</a>
            @endcan
            
            @can('admin.users.create')
            <a href="{{route('admin.users.create')}}" class="btn btn-green">
                Nuevo
            </a>
            @endcan
        </div> 
    </div> 

     

    @livewire('admin.user-search') 
 



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