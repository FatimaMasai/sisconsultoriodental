<x-admin-layout>
    <x-label class="text-black text-xl font-semibold mb-4">
        Crear Proveedor
    </x-label>



    <form action="{{ route('admin.suppliers.store') }}" method="POST">
        @csrf

        <x-validation-errors class="mb-4" />

        <div class="grid gap-6 mb-6 md:grid-cols-2">

            <div class="">
                <x-label class="form-label">
                    Proveedor
                </x-label>
                <x-select name="person_id" class="w-full">
                    @foreach ($persons as $person)
                        <option value="{{$person->id}}"
                            @selected(old('person_id') == $person->id)>
                            {{$person->name}} {{$person->last_name_father}} {{$person->last_name_mother}}
                        </option>
                        
                    @endforeach
                </x-select>
            </div>


            <div>
                <x-label class="form-label">Empresa</x-label>
                <x-input  value="{{ old('company') }}" name="company" class="input-label"
                    placeholder="Bagó" />
            </div>
            <div>
                <x-label class="form-label">Nit</x-label>
                <x-input  value="{{ old('nit') }}" name="nit" class="input-label"
                    placeholder="Ingrese el nit" />
            </div> 
 
        </div>

        <div class="flex justify-end">
            <x-button>
                Guardar
            </x-button>
        </div>

    </form>






</x-admin-layout>
