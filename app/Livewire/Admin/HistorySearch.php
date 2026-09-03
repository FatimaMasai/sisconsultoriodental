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

        $histories = History::with('patient.person', 'doctor.person', 'service')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('patient.person', function ($personQuery) use ($search) {
                        $personQuery->where('name', 'LIKE', '%' . $search . '%')
                            ->orWhere('last_name_father', 'LIKE', '%' . $search . '%')
                            ->orWhere('last_name_mother', 'LIKE', '%' . $search . '%');
                    })
                    ->orWhereHas('doctor.person', function ($personQuery) use ($search) {
                        $personQuery->where('name', 'LIKE', '%' . $search . '%')
                            ->orWhere('last_name_father', 'LIKE', '%' . $search . '%');
                    })
                    ->orWhereHas('service', function ($serviceQuery) use ($search) {
                        $serviceQuery->where('name', 'LIKE', '%' . $search . '%');
                    });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(50);

        return view('livewire.admin.history-search', compact('histories'));
    }
}
