<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination; //agreamos

class UserSearch extends Component
{
    use WithPagination; //agreamos
 
    public $search = ''; 

    public function updatingSearch()
    {
        $this->resetPage();
    }

   
    public function render()
    {
        $search = trim($this->search);

        $users = User::when($search !== '', function ($query) use ($search) {
                // Palabra por palabra, para que encuentre coincidencias sin importar el orden.
                $words = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY);

                foreach ($words as $word) {
                    $query->where(function ($q) use ($word) {
                        $q->where('name', 'LIKE', '%' . $word . '%')
                            ->orWhere('email', 'LIKE', '%' . $word . '%');
                    });
                }
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.admin.user-search', compact('users'));
    }
 

}
