<?php

namespace App\Livewire\Admin;

use App\Models\ProductCategory;
use Livewire\Component;
use Livewire\WithPagination; //agreamos

class ProductCategorySearch extends Component
{
    use WithPagination; //agreamos 
    public $search = ''; 

    public function render()
    {
        $product_categories = ProductCategory::where('name', 'LIKE', '%' .$this->search . '%') 
            ->orderBy('id', 'desc')
            ->paginate(10);
        return view('livewire.admin.product-category-search', compact('product_categories'));
    }
}
