<?php

namespace App\Livewire\Admin;

use App\Models\ServiceCategory;
use Livewire\Component;
use Livewire\WithPagination; //agreamos

class ServiceCategorySearch extends Component
{

    use WithPagination; //agreamos

    
    public $search = ''; 


    public function render()
    {
        $service_categories = ServiceCategory::where('name', 'LIKE', '%' .$this->search . '%') 
            ->orderBy('id', 'desc')
            ->paginate(10);


        return view('livewire.admin.service-category-search', compact('service_categories'));
    }
}
