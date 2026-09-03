<?php

namespace App\Livewire\Admin;

use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

/**
 * Buscador en tiempo real de Proveedores: filtra por nombre, empresa
 * o NIT a medida que se escribe.
 */
class SupplierSearch extends Component
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

        $suppliers = Supplier::where('status', 1)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('company', 'LIKE', '%' . $search . '%')
                        ->orWhere('nit', 'LIKE', '%' . $search . '%')
                        ->orWhereHas('person', function ($personQuery) use ($search) {
                            $personQuery->where('name', 'LIKE', '%' . $search . '%')
                                ->orWhere('last_name_father', 'LIKE', '%' . $search . '%')
                                ->orWhere('last_name_mother', 'LIKE', '%' . $search . '%');
                        });
                });
            })
            ->with('person')
            ->orderBy('id', 'desc')
            ->paginate(10);

        foreach ($suppliers as $supplier) {
            $supplier->person->age = Carbon::parse($supplier->person->birth_date)->age;
        }

        return view('livewire.admin.supplier-search', compact('suppliers'));
    }
}
