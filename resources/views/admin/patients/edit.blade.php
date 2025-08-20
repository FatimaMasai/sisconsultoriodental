<x-admin-layout>
    <x-label class="text-black text-xl font-semibold mb-4">
        Editar Paciente
    </x-label>



    <form action="{{ route('admin.patients.update', $patient) }}" method="POST">
        @csrf
        @method('PUT')
        <x-validation-errors class="mb-4" />

        <div class="grid gap-6 mb-6 md:grid-cols-2">

            <div class="">
                <x-label class="form-label">
                    Paciente
                </x-label>

                   <!-- Mostrar el nombre del paciente sin permitir cambios -->
                <p class="input-label">
                    {{ $patient->person->name }} {{ $patient->person->last_name_father }} {{ $patient->person->last_name_mother }}
                </p>

                <!-- Campo oculto para almacenar el person_id -->
                <input type="hidden" name="person_id" value="{{ $patient->person_id }}" />
            </div>


            <div>
                <x-label class="form-label">Alergia</x-label>
                <x-input  value="{{ old('allergy', $patient->allergy) }}" name="allergy" class="input-label"
                    placeholder="Ingrese alergias del paciente" />
            </div>
            <div>
                <x-label class="form-label">Obervación</x-label>
                <x-input  value="{{ old('observation', $patient->observation ) }}" name="observation" class="input-label"
                    placeholder="Ingrese observaciones importantes del paciente" />
            </div>
            <div>
                <x-label class="form-label">Recomendado por:</x-label>
                <x-input  value="{{ old('recommended_by', $patient->recommended_by ) }}" name="recommended_by" class="input-label"
                    placeholder="Nombre de la persona que recomendó" />
            </div>
            <div>
                <x-label class="form-label">Responsable</x-label>
                <x-input value="{{ old('responsible_person', $patient->responsible_person ) }}" name="responsible_person" class="input-label"
                    placeholder="Nombre de la persona responsable"  />
            </div>
            <div>
                <x-label class="form-label">Antecedentes</x-label>
                <x-input value="{{ old('medical_history', $patient->medical_history ) }}" name="medical_history" class="input-label"
                    placeholder="Ingrese antecedentes médicos del paciente" />
            </div>  
 
        </div>

        <div class="flex justify-end">
            <x-button>
                Actualizar
            </x-button>
        </div>

    </form>






</x-admin-layout>
