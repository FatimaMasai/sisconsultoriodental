<?php

namespace App\Livewire\Admin;

use App\Models\Speciality;
use Livewire\Component;
use Livewire\WithPagination; //agreamos

class SpecialitySearch extends Component
{

    use WithPagination;

    public $search = '';


    public function render()
    {

        $specialities = Speciality::where('name', 'LIKE', '%' .$this->search . '%')
        ->orderBy('id', 'desc')
        ->paginate(10);

        return view('livewire.admin.speciality-search', compact('specialities'));
    }
}
