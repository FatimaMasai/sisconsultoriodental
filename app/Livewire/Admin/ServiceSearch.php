<?php

namespace App\Livewire\Admin;

use App\Models\Service;
use Livewire\Component;
use Livewire\WithPagination; //agreamos

class ServiceSearch extends Component
{

    use WithPagination; //agreamos
 
    public $search = ''; 


    public function render()
    {
        $services = Service::where('name', 'LIKE', '%' .$this->search . '%') 
            ->orderBy('id', 'desc')
            ->paginate(10);


        return view('livewire.admin.service-search', compact('services'));
    }
}
