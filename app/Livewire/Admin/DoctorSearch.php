<?php

namespace App\Livewire\Admin;

use App\Models\Doctor;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

/**
 * Buscador en tiempo real de Doctores: filtra por nombre o apellidos
 * a medida que se escribe, sin recargar la página.
 */
class DoctorSearch extends Component
{
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        // Si el usuario ya estaba en otra página de resultados y cambia
        // la búsqueda, lo mandamos de vuelta a la página 1.
        $this->resetPage();
    }

    public function render()
    {
        $search = trim($this->search);

        $doctors = Doctor::where('status', 1)
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('person', function ($personQuery) use ($search) {
                    $personQuery->where('name', 'LIKE', '%' . $search . '%')
                        ->orWhere('last_name_father', 'LIKE', '%' . $search . '%')
                        ->orWhere('last_name_mother', 'LIKE', '%' . $search . '%');
                });
            })
            ->with(['person', 'speciality'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        foreach ($doctors as $doctor) {
            $doctor->person->age = Carbon::parse($doctor->person->birth_date)->age;
        }

        return view('livewire.admin.doctor-search', compact('doctors'));
    }
}
