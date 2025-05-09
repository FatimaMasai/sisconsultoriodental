<x-admin-layout>
    <x-label class="text-black text-xl font-semibold mb-4">
        Crear Rol
    </x-label>
    <form action="{{ route('admin.roles.store') }}" method="POST">
        @csrf

        <div class="mb-4">

            <x-validation-errors class="mb-4" />

            <x-label class="mb-2">
                Nombre
            </x-label>

            <x-input name="name" value="{{ old('name') }}" class="w-full mb-4" placeholder="Ingrese nombre del Rol" />

            <x-label class="mb-2">
                Listado de Permisos
            </x-label>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach ($permissions as $index => $permission)
                    <div class="flex items-center">
                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                            id="bordered-checkbox-{{ $permission->id }}"
                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-lg focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        <label for="bordered-checkbox-{{ $permission->id }}"
                            class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                            <!-- Numeración de permisos -->
                            {{ $index + 1 }}. {{ $permission->description }}
                        </label>
                    </div>
                @endforeach
            </div>

        </div>

        <div class="flex justify-end">
            <x-button>
                Guardar
            </x-button>
        </div>

    </form>

    {{-- agregando el script de la librería de sweetalert2 PASO 3 --}}
    @push('js')
    <script>
        forms = document.querySelectorAll('.delete-form');
        forms.forEach(form => {
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
