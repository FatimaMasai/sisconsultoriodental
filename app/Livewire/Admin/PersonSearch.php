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
                // Palabra por palabra, para que "Ana Luján" encuentre a alguien
                // aunque "Ana" y "Luján" estén en columnas distintas.
                $words = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY);

                foreach ($words as $word) {
                    $query->where(function ($q) use ($word) {
                        $q->where('name', 'LIKE', '%' . $word . '%')
                            ->orWhere('last_name_father', 'LIKE', '%' . $word . '%')
                            ->orWhere('last_name_mother', 'LIKE', '%' . $word . '%')
                            ->orWhere('identity_card', 'LIKE', '%' . $word . '%');
                    });
                }
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        foreach ($persons as $person) {
            $person->age = Carbon::parse($person->birth_date)->age;
        }

        return view('livewire.admin.person-search', compact('persons'));
    }
}
