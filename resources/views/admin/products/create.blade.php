<x-admin-layout> 
    <x-label class="text-black text-xl font-semibold mb-4">
        Crear Servicio
    </x-label>
    <form action="{{route('admin.products.store')}}" method="POST" >
        @csrf

        <div class="mb-4">

            <x-validation-errors class="mb-4" /> 

            <x-label class="mb-2">
                Categoria de Producto
            </x-label>
            <x-select name="product_category_id" class="w-full mb-5">
                @foreach ($productCategories as $productCategorie)
                    <option value="{{$productCategorie->id}}"
                        @selected(old('product_category_id') == $productCategorie->id)>
                        {{$productCategorie->name}}
                    </option>
                    
                @endforeach
            </x-select>


            <x-label class="mb-2">
                Producto
            </x-label>

            <x-input name="name" value="{{old('name')}}" class="w-full mb-5" placeholder="Ingrese nombre de la categoria">

            </x-input>

            <x-label class="mb-2">
                Precio
            </x-label>

            <x-input name="price" value="{{old('price')}}" class="w-full mb-5" placeholder="Ingrese un precio">

            </x-input>


        </div>

        <div class="flex justify-end">
            <x-button>
                Guardar
            </x-button>
        </div>

    </form>

</x-admin-layout>