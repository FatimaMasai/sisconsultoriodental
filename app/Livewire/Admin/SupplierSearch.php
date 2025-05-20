<?php

namespace App\Livewire\Admin;

use App\Models\Supplier;
use Livewire\Component;

use Livewire\WithPagination;
use Carbon\Carbon;

class SupplierSearch extends Component
{

    use WithPagination;

    public $search = '';


    public function render()
    {

        $suppliers = Supplier::where('status', 1) // Filtramos por estado activo
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
        foreach ($suppliers as $supplier) {
            $supplier->person->age = Carbon::parse($supplier->person->birth_date)->age;
        }

        return view('livewire.admin.supplier-search', compact('suppliers'));
    }
}
