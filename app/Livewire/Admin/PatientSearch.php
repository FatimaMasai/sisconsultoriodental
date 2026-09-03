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

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $search = trim($this->search);

        $patients = Patient::where('status', 1) // Filtramos por estado activo
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('person', function ($personQuery) use ($search) {
                    $personQuery->where('name', 'LIKE', '%' . $search . '%')
                        ->orWhere('last_name_father', 'LIKE', '%' . $search . '%')
                        ->orWhere('last_name_mother', 'LIKE', '%' . $search . '%');
                });
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
