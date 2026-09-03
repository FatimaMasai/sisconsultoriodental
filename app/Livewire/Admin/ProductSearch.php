<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Buscador en tiempo real de Productos: filtra por nombre de producto
 * o de categoría a medida que se escribe.
 */
class ProductSearch extends Component
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

        $products = Product::where('status', 1)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', '%' . $search . '%')
                        ->orWhereHas('productCategory', function ($categoryQuery) use ($search) {
                            $categoryQuery->where('name', 'LIKE', '%' . $search . '%');
                        });
                });
            })
            ->with('productCategory')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.admin.product-search', compact('products'));
    }
}
