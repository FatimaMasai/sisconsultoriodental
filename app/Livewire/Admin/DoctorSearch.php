<?php

namespace App\Livewire\Admin;

use App\Models\Doctor;
use Livewire\Component;

use Livewire\WithPagination;
use Carbon\Carbon;

class DoctorSearch extends Component
{
    use WithPagination;

    public $search = '';

    public function render()
    {

        $doctors = Doctor::where('status', 1) // Filtramos por estado activo
            ->whereHas('person', function ($query) {
                // Aplicamos las búsquedas sobre los campos de la tabla 'persons'
                $query->where('name', 'LIKE', '%' . $this->search . '%')
                    ->orWhere('last_name_father', 'LIKE', '%' . $this->search . '%')
                    ->orWhere('last_name_mother', 'LIKE', '%' . $this->search . '%')
                    ->orWhere('identity_card', 'LIKE', '%' . $this->search . '%');
            })
            ->with('person') // Cargamos la relación 'person' para obtener los datos de la persona asociada
            ->orderBy('id', 'desc')
            ->paginate(10);

        // Calcula la edad de cada persona asociada al paciente
        foreach ($doctors as $doctor) {
            $doctor->person->age = Carbon::parse($doctor->person->birth_date)->age;
        }

        return view('livewire.admin.doctor-search', compact('doctors'));
    }
}
