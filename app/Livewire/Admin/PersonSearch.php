<?php

namespace App\Livewire\Admin;

use App\Models\Person;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

/**
 * Buscador en tiempo real de Personas: filtra por nombre, apellidos
 * o carnet de identidad a medida que se escribe, sin recargar la página.
 */
class PersonSearch extends Component
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

        $persons = Person::where('status', 1)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', '%' . $search . '%')
                        ->orWhere('last_name_father', 'LIKE', '%' . $search . '%')
                        ->orWhere('last_name_mother', 'LIKE', '%' . $search . '%')
                        ->orWhere('identity_card', 'LIKE', '%' . $search . '%');
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        foreach ($persons as $person) {
            $person->age = Carbon::parse($person->birth_date)->age;
        }

        return view('livewire.admin.person-search', compact('persons'));
    }
}
