<x-admin-layout> 
    <x-label class="text-black text-xl font-semibold mb-4">
        Editar Categoria Producto
    </x-label>
    <form action="{{route('admin.product_categories.update', $productCategory)}}" method="POST" >
        @csrf
        @method('PUT')
        <div class="mb-4">

            <x-validation-errors class="mb-4" /> 

            <x-label class="mb-2">
                Nombre
            </x-label>

            <x-input name="name" value="{{old('name', $productCategory->name)}}" class="w-full" placeholder="Ingrese nombre de la categoria">

            </x-input>
        </div>

        <div class="flex justify-end">
            <x-button>
                Actualizar
            </x-button>
        </div>

    </form>

</x-admin-layout>