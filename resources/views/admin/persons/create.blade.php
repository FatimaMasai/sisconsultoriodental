<x-admin-layout>
    <x-label class="text-black text-xl font-semibold mb-4">
        Crear Personas
    </x-label>



    <form action="{{ route('admin.persons.store') }}" method="POST">
        @csrf
        <x-validation-errors class="mb-4" />
        <div class="grid gap-6 mb-6 md:grid-cols-2">

            

            <div>
                <x-label class="form-label">Nombre Completo</x-label>
                <x-input  value="{{ old('name') }}" name="name" class="input-label"
                    placeholder="Lupita" />
            </div>
            <div>
                <x-label class="form-label">Apellido Paterno</x-label>
                <x-input  value="{{ old('last_name_father') }}" name="last_name_father" class="input-label"
                    placeholder="Cuellar" />
            </div>
            <div>
                <x-label class="form-label">Apellido Materno</x-label>
                <x-input  value="{{ old('last_name_mother') }}" name="last_name_mother" class="input-label"
                    placeholder="Paz" />
            </div>
            <div>
                <x-label class="form-label">Carnet Identidad</x-label>
                <x-input value="{{ old('identity_card') }}" name="identity_card" class="input-label"
                    placeholder="12566956"  />
            </div>
            <div>
                <x-label class="form-label">Fecha Nac.</x-label>
                <x-input value="{{ old('birth_date') }}" type="date" name="birth_date" class="input-label"
                    placeholder="25/11/1995" />
            </div>
            <div>
                <x-label class="form-label">Sexo</x-label>
                <x-select name="gender" class="input-label">
                    <option value="Femenino" @selected(old('gender') == 'Femenino')>Femenino</option>
                    <option value="Masculino" @selected(old('gender') == 'Masculino')>Masculino</option>
                </x-select>
            </div>

            <div>
                <x-label class="form-label">Celular</x-label>
                <x-input value="{{ old('phone') }}" name="phone" class="input-label"
                    placeholder="75304552" />
            </div>
            <div>
                <x-label class="form-label">Email</x-label>
                <x-input value="{{ old('email') }}" name="email" class="input-label" placeholder="lupita@gmail.com"
                 />
            </div>

            <div>
                <x-label class="form-label">Dirección</x-label>
                <x-input  value="{{ old('address') }}" name="address" class="input-label"
                    placeholder="km6 doble via calle 6 #18" />
            </div>
        </div>

        <div class="flex justify-end">
            <x-button>
                Guardar
            </x-button>
        </div>

    </form>






</x-admin-layout>
