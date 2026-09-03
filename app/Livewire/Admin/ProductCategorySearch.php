<?php

namespace App\Livewire\Admin;

use App\Models\ProductCategory;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Buscador en tiempo real de Categorías de Producto.
 */
class ProductCategorySearch extends Component
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

        $product_categories = ProductCategory::where('status', 1)
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'LIKE', '%' . $search . '%');
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.admin.product-category-search', compact('product_categories'));
    }
}
