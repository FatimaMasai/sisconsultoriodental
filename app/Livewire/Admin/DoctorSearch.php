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
                // Palabra por palabra, para que "Ana Luján" encuentre a alguien
                // aunque "Ana" y "Luján" estén en columnas distintas.
                $words = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY);

                $query->whereHas('person', function ($personQuery) use ($words) {
                    foreach ($words as $word) {
                        $personQuery->where(function ($q) use ($word) {
                            $q->where('name', 'LIKE', '%' . $word . '%')
                                ->orWhere('last_name_father', 'LIKE', '%' . $word . '%')
                                ->orWhere('last_name_mother', 'LIKE', '%' . $word . '%');
                        });
                    }
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
