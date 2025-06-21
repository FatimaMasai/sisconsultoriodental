<div>

    <div class="">
     <!-- Formulario de búsqueda -->
    <x-input wire:model.live="search" class="form-control w-full" placeholder="Buscar por nombre" />

    </div>
    <br>

     @if ($patients->count())

        <div class="relative overflow-x-auto">
            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-3 py-2">
                            ID
                        </th>
                        <th scope="col" class="px-3 py-2">
                            Paciente
                        </th> 
                        <th scope="col" class="px-3 py-2">
                            Edad
                        </th> 
                        {{-- <th scope="col" class="px-3 py-2">
                            Sexo 
                        </th>  --}}
                        <th scope="col" class="px-3 py-2">
                            Alergia
                        </th> 
                        <th scope="col" class="px-3 py-2">
                            Observación
                        </th> 
                        <th scope="col" class="px-3 py-2">
                            Antecedentes
                        </th> 
                        <th scope="col" class="px-3 py-2">
                            Acciones
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($patients as $patient) 

                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
                            <th scope="row" class="px-3 py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{$patient->id}}
                            </th>
                            <td class="px-3 py-2">
                                {{$patient->person->name}} {{$patient->person->last_name_father}} {{$patient->person->last_name_mother}}
                            </td>  
                            <td class="px-3 py-2">
                                {{ $patient->person->age }} Años
                            </td> 
                            {{-- <td class="px-3 py-2">
                                {{ $patient->person->gender }}  
                            </td>  --}}
                            <td class="px-3 py-2">
                                {{ $patient->allergy }} 
                            </td> 

                            <td class="px-3 py-2">
                                {{ $patient->observation }} 
                            </td> 
                            <td class="px-3 py-2">
                                {{ $patient->medical_history }}    
                            </td> 
 

                            <td class="px-3 py-2" >
                                <div class="flex space-x-2">

                                    @can('admin.patients.edit' )
                                        <a href="{{route('admin.patients.edit', $patient)}}" class="btn btn-blue text-xs">Editar</a>
                                    @endcan
                                
                                    @can('admin.patients.destroy' )
                                        <form class="delete-form" action="{{route('admin.patients.destroy', $patient)}}" method="POST">

                                            @csrf
                                            @method('DELETE')
            
                                            <button class="btn btn-red text-xs">
                                                Eliminar
                                            </button>

                                        </form>
                                    @endcan
                                    
                                </div>

                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>

            <div class="mt-4">
                {{$patients->links()}}
            </div>

        </div>
        
    @else

        <div class="flex items-center p-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400" role="alert">
            <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
            </svg>

            <span class="sr-only">Info</span>
            <div>
                <span class="font-medium">Info alert!</span> Todavia no hay pacientes registrados.
            </div>
        </div>

    @endif


</div>
