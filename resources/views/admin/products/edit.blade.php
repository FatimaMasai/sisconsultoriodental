<x-admin-layout> 
    <x-label class="text-black text-xl font-semibold mb-4">
        Editar Producto
    </x-label>

    <form action="{{route('admin.products.update', $product)}}" method="POST" >
        @csrf
        @method('PUT')

        <div class="mb-4">

            <x-validation-errors class="mb-4" /> 

            <x-label class="mb-2">
                Categoria de servicio
            </x-label>

            <x-select name="product_category_id" class="w-full">

                @foreach ($productCategories as $productCategorie)
                    <option value="{{$productCategorie->id}}"
                        
                        @selected(old('product_category_id', $product->product_category_id) == $productCategorie->id)>
                        
                        {{$productCategorie->name}}

                    </option>
                    
                @endforeach

            </x-select> 


            <x-label class="mb-2">
                Servicio
            </x-label>

            <x-input name="name" value="{{old('name', $product->name)}}" class="w-full mb-5" placeholder="Ingrese nombre de la categoria">

            </x-input>

            <x-label class="mb-2">
                Precio
            </x-label>

            <x-input name="price" value="{{old('price', $product->price)}}" class="w-full mb-5" placeholder="Ingrese un precio">

            </x-input>

            <x-label class="mb-2">
                Estado
            </x-label>

 
            <!-- select  activar o desactivar el servicio -->
            <x-select name="status" class="w-full mb-5">
                <option value="1" @selected($product->status == 1)>Alta</option>
                <option value="0" @selected($product->status == 0)>Baja</option>
            </x-select>


        </div>

        <div class="flex justify-end">
            <x-button>
                Actualizar
            </x-button>
        </div>

    </form>

</x-admin-layout>