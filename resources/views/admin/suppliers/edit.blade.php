<x-admin-layout>
    <x-label class="text-black text-xl font-semibold mb-4">
        Editar Proveedor
    </x-label>



    <form action="{{ route('admin.suppliers.update', $supplier) }}" method="POST">
        @csrf
        @method('PUT')
        <x-validation-errors class="mb-4" />

        <div class="grid gap-6 mb-6 md:grid-cols-2">

            <div class="">
                <x-label class="form-label">
                    Proveedor
                </x-label>

                   <!-- Mostrar el nombre del proveedor sin permitir cambios -->
                <p class="input-label">
                    {{ $supplier->person->name }} {{ $supplier->person->last_name_father }} {{ $supplier->person->last_name_mother }}
                </p>

                <!-- Campo oculto para almacenar el person_id -->
                <input type="hidden" name="person_id" value="{{ $supplier->person_id }}" />
            </div>


            <div>
                <x-label class="form-label">Empresa</x-label>
                <x-input  value="{{ old('company', $supplier->company) }}" name="company" class="input-label"
                     />
            </div>
            <div>
                <x-label class="form-label">Nit</x-label>
                <x-input  value="{{ old('nit', $supplier->nit ) }}" name="nit" class="input-label"
                     />
            </div>
            <div>  
 
        </div>

        <div class="flex justify-end">
            <x-button>
                Actualizar
            </x-button>
        </div>

    </form>






</x-admin-layout>
