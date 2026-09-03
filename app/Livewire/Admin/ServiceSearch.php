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
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', '%' . $search . '%')
                        ->orWhereHas('serviceCategory', function ($categoryQuery) use ($search) {
                            $categoryQuery->where('name', 'LIKE', '%' . $search . '%');
                        });
                });
            })
            ->with('serviceCategory')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.admin.service-search', compact('services'));
    }
}
