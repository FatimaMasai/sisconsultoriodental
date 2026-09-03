<x-admin-layout>
    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between mb-6">
        <x-label class="text-black text-lg sm:text-xl font-semibold">
            Listado de Pacientes
        </x-label>
        <div class="flex flex-wrap items-center gap-2">
            @can('admin.patients.pdf')
                <a href="{{ route('admin.patients.pdf') }}" class="btn btn-orange" target="_blank">
                    <i class="fa-solid fa-file-pdf mr-1"></i> PDF
                </a>
                <a href="{{ route('admin.patients.excel') }}" class="btn btn-green">
                    <i class="fa-solid fa-file-excel mr-1"></i> Excel
                </a>
            @endcan
            @can('admin.patients.create')
                <a href="{{route('admin.patients.create')}}" class="btn btn-green">
                    Nuevo
                </a>
            @endcan
        </div>
    </div>

    <livewire:admin.patient-search />

    {{-- agregando el script de la libreria de sweetalert2 PASO 3--}}
    {{-- Delegado en document para que también funcione con las filas
         que Livewire vuelve a renderizar al buscar o paginar. --}}

    @push('js')
    <script>
        document.addEventListener('submit', function (e) {
            const form = e.target.closest('.delete-form');
            if (!form) return;

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
        });
    </script>

@endpush

</x-admin-layout>
