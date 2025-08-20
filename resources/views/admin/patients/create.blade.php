<x-admin-layout>
    <x-label class="text-black text-xl font-semibold mb-4">
        Crear Paciente
    </x-label>



    <form action="{{ route('admin.patients.store') }}" method="POST">
        @csrf

        <x-validation-errors class="mb-4" />

        <div class="grid gap-6 mb-6 md:grid-cols-2">

            <div class="">
                <x-label class="form-label">
                    Paciente
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
                <x-label class="form-label">Alergia</x-label>
                <x-input  value="{{ old('allergy') }}" name="allergy" class="input-label"
                    placeholder="Ingrese alergias del paciente" />
            </div>
            <div>
                <x-label class="form-label">Obervación</x-label>
                <x-input  value="{{ old('observation') }}" name="observation" class="input-label"
                    placeholder="Ingrese observaciones importantes del paciente" />
            </div>
            <div>
                <x-label class="form-label">Recomendado por:</x-label>
                <x-input  value="{{ old('recommended_by') }}" name="recommended_by" class="input-label"
                    placeholder="Nombre de la persona que recomendó" />
            </div>
            <div>
                <x-label class="form-label">Responsable</x-label>
                <x-input value="{{ old('responsible_person') }}" name="responsible_person" class="input-label"
                    placeholder="Nombre de la persona responsable"  />
            </div>
            <div>
                <x-label class="form-label">Antecedentes</x-label>
                <x-input value="{{ old('medical_history') }}" name="medical_history" class="input-label"
                    placeholder="Ingrese antecedentes médicos del paciente" />
            </div>  
 
        </div>

        <div class="flex justify-end">
            <x-button>
                Guardar
            </x-button>
        </div>

    </form>






</x-admin-layout>
