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
        $users = User::where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orderBy('id', 'desc')
                    ->paginate(10);

        return view('livewire.admin.user-search', compact('users'));
    }
 

}
