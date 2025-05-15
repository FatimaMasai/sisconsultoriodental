<?php

namespace App\Livewire\Admin;

use App\Models\Person;
use Livewire\Component;
use Livewire\WithPagination; //agreamos

use Carbon\Carbon; //agreamos

class PersonSearch extends Component
{

    use WithPagination; //agreamos
    public $search = ''; //agreamos

    public function render()
    {
        $persons = Person::where('status', 1) // Primero filtramos por estado activo
            
            ->where(function ($query) {
                // Aplicamos las búsquedas solo sobre los campos relevantes
                $query->where('name', 'LIKE', '%' . $this->search . '%')
                    ->orWhere('last_name_father', 'LIKE', '%' . $this->search . '%')
                    ->orWhere('last_name_mother', 'LIKE', '%' . $this->search . '%')
                    ->orWhere('identity_card', 'LIKE', '%' . $this->search . '%');
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

            // Calcula la edad de cada persona
        foreach ($persons as $person) {
            $person->age = Carbon::parse($person->birth_date)->age; // Esto calculará la edad
        }

        return view('livewire.admin.person-search', compact('persons')); 
    }
}
