<?php

namespace App\Livewire\Admin;

use App\Models\History;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Buscador en tiempo real del Historial Médico: filtra por paciente,
 * doctor o servicio a medida que se escribe, sin recargar la página.
 */
class HistorySearch extends Component
{
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        // Si el usuario ya estaba en la página 3 de resultados y cambia
        // la búsqueda, lo mandamos de vuelta a la página 1.
        $this->resetPage();
    }

    public function render()
    {
        $search = trim($this->search);

        // Si quien busca es un doctor con cuenta vinculada, por defecto solo
        // ve historiales de su propia especialidad (más las que se le hayan
        // autorizado explícitamente). Admin, Recepción, etc. ven todo.
        $doctor = auth()->user()?->doctor;

        $histories = History::with('patient.person', 'doctor.person', 'service')
            ->when($doctor, function ($query) use ($doctor) {
                $specialityIds = array_merge([$doctor->speciality_id], $doctor->grantedSpecialityIds());

                $query->whereHas('doctor', function ($doctorQuery) use ($specialityIds) {
                    $doctorQuery->whereIn('speciality_id', $specialityIds);
                });
            })
            ->when($search !== '', function ($query) use ($search) {
                // Palabra por palabra, para que "Ana Luján" encuentre al paciente
                // aunque "Ana" y "Luján" estén en columnas distintas.
                $words = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY);

                foreach ($words as $word) {
                    $query->where(function ($q) use ($word) {
                        $q->whereHas('patient.person', function ($personQuery) use ($word) {
                            $personQuery->where('name', 'LIKE', '%' . $word . '%')
                                ->orWhere('last_name_father', 'LIKE', '%' . $word . '%')
                                ->orWhere('last_name_mother', 'LIKE', '%' . $word . '%');
                        })
                        ->orWhereHas('doctor.person', function ($personQuery) use ($word) {
                            $personQuery->where('name', 'LIKE', '%' . $word . '%')
                                ->orWhere('last_name_father', 'LIKE', '%' . $word . '%');
                        })
                        ->orWhereHas('service', function ($serviceQuery) use ($word) {
                            $serviceQuery->where('name', 'LIKE', '%' . $word . '%');
                        });
                    });
                }
            })
            ->orderBy('id', 'desc')
            ->paginate(50);

        return view('livewire.admin.history-search', compact('histories'));
    }
}
