<x-admin-layout> 
    <x-label class="text-black text-xl font-semibold mb-4">
        Crear Categoria Producto
    </x-label>
    <form action="{{route('admin.product_categories.store')}}" method="POST" >
        @csrf

        <div class="mb-4">

            <x-validation-errors class="mb-4" /> 

            <x-label class="mb-2">
                Nombre
            </x-label>

            <x-input name="name" value="{{old('name')}}" class="w-full" placeholder="Ingrese nombre de la categoria">

            </x-input>
        </div>

        <div class="flex justify-end">
            <x-button>
                Guardar
            </x-button>
        </div>

    </form>

</x-admin-layout>