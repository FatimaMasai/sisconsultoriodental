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
                // Palabra por palabra, para que "Ana Luján" encuentre a alguien
                // aunque "Ana" y "Luján" estén en columnas distintas.
                $words = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY);

                foreach ($words as $word) {
                    $query->where(function ($q) use ($word) {
                        $q->where('company', 'LIKE', '%' . $word . '%')
                            ->orWhere('nit', 'LIKE', '%' . $word . '%')
                            ->orWhereHas('person', function ($personQuery) use ($word) {
                                $personQuery->where('name', 'LIKE', '%' . $word . '%')
                                    ->orWhere('last_name_father', 'LIKE', '%' . $word . '%')
                                    ->orWhere('last_name_mother', 'LIKE', '%' . $word . '%');
                            });
                    });
                }
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
