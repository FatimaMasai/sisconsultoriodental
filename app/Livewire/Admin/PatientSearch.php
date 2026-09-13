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
                // Se busca palabra por palabra (ej. "Ana Luján" = "Ana" y "Luján")
                // para que encuentre el nombre aunque esté repartido entre nombre,
                // apellido paterno y materno, no solo dentro de una sola columna.
                $words = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY);

                $query->whereHas('person', function ($personQuery) use ($words) {
                    foreach ($words as $word) {
                        $personQuery->where(function ($q) use ($word) {
                            $q->where('name', 'LIKE', '%' . $word . '%')
                                ->orWhere('last_name_father', 'LIKE', '%' . $word . '%')
                                ->orWhere('last_name_mother', 'LIKE', '%' . $word . '%');
                        });
                    }
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
