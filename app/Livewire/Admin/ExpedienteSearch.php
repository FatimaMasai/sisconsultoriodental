<?php

namespace App\Livewire\Admin;

use App\Models\Expediente;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Buscador en tiempo real de Expedientes: filtra por paciente o
 * especialidad a medida que se escribe, sin recargar la página.
 */
class ExpedienteSearch extends Component
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

        // Si quien busca es un doctor con cuenta vinculada, por defecto solo
        // ve expedientes de su propia especialidad (más las que se le hayan
        // autorizado explícitamente). Admin, Recepción, etc. ven todo.
        $doctor = auth()->user()?->doctor;

        $expedientes = Expediente::with('patient.person', 'speciality')
            ->withCount('consultas')
            ->withMax('consultas', 'date')
            ->when($doctor, function ($query) use ($doctor) {
                $specialityIds = array_merge([$doctor->speciality_id], $doctor->grantedSpecialityIds());

                $query->whereIn('speciality_id', $specialityIds);
            })
            ->when($search !== '', function ($query) use ($search) {
                $words = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY);

                foreach ($words as $word) {
                    $query->where(function ($q) use ($word) {
                        $q->whereHas('patient.person', function ($personQuery) use ($word) {
                            $personQuery->where('name', 'LIKE', '%' . $word . '%')
                                ->orWhere('last_name_father', 'LIKE', '%' . $word . '%')
                                ->orWhere('last_name_mother', 'LIKE', '%' . $word . '%');
                        })
                        ->orWhereHas('speciality', function ($specialityQuery) use ($word) {
                            $specialityQuery->where('name', 'LIKE', '%' . $word . '%');
                        });
                    });
                }
            })
            ->orderByDesc('updated_at')
            ->paginate(50);

        return view('livewire.admin.expediente-search', compact('expedientes'));
    }
}
