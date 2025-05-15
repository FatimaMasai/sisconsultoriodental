<?php

namespace App\Livewire\Admin;

use App\Models\Patient;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class PatientSearch extends Component
{
    use WithPagination;

    public $search = '';

    public function render()
    {
        $patients = Patient::where('status', 1) // Filtramos por estado activo
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
        foreach ($patients as $patient) {
            $patient->person->age = Carbon::parse($patient->person->birth_date)->age;
        }

        return view('livewire.admin.patient-search', compact('patients'));
    }
}
