<x-admin-layout>
    <x-label class="text-black text-xl font-semibold mb-4">
        Editar Usuario
    </x-label>
    <form action="{{ route('admin.users.update', $user) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-4">

            <x-validation-errors class="mb-4" />

            <x-label class="mb-2">
                Nombre
            </x-label>

            <x-input name="name" value="{{ old('name', $user->name) }}" class="w-full mb-4"
                placeholder="Ingrese nombre de la categoria">

            </x-input>

            <x-label class="mb-2">
                Listado de roles
            </x-label>
            @foreach ($roles as $role)
                <div class="flex items-center ps-4 border border-gray-200 rounded-lg dark:border-gray-700 mb-5">
                    <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                        id="bordered-checkbox-{{ $role->id }}"
                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded-lg 
                focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                        @if (in_array($role->id, old('roles', $user->roles->pluck('id')->toArray()))) checked @endif>
                    <label for="bordered-checkbox-{{ $role->id }}"
                        class="w-full py-4 ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                        {{ $role->name }}
                    </label>
                </div>
            @endforeach

        </div>

        <div class="flex justify-end">
            <x-button>
                Actualizar
            </x-button>
        </div>

    </form>

</x-admin-layout>
