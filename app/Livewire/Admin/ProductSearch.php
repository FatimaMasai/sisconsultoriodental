<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination; //agreamos

class ProductSearch extends Component
{
    use WithPagination; //agreamos
 
    public $search = ''; 


    public function render()
    {
        $products = Product::where('name', 'LIKE', '%' .$this->search . '%') 
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.admin.product-search', compact('products'));
    }
}
