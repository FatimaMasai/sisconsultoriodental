<?php

namespace App\Livewire\Admin;

use App\Models\Service;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Buscador en tiempo real de Servicios: filtra por nombre de servicio
 * o de categoría a medida que se escribe.
 */
class ServiceSearch extends Component
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

        $services = Service::where('status', 1)
            ->when($search !== '', function ($query) use ($search) {
                // Palabra por palabra, para que encuentre coincidencias sin importar el orden.
                $words = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY);

                foreach ($words as $word) {
                    $query->where(function ($q) use ($word) {
                        $q->where('name', 'LIKE', '%' . $word . '%')
                            ->orWhereHas('serviceCategory', function ($categoryQuery) use ($word) {
                                $categoryQuery->where('name', 'LIKE', '%' . $word . '%');
                            });
                    });
                }
            })
            ->with('serviceCategory')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.admin.service-search', compact('services'));
    }
}
