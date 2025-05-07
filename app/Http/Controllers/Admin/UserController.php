<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf as PDF; 
use Spatie\Permission\Models\Role;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.users.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $user->roles()->sync($request->roles); 

        session()->flash('swal', [
            'title' => 'Roles Actualizados',
            'text' => '¡Bien Hecho!.',
            'icon' => 'success',
        ]);

        return redirect()->route('admin.users.index', $user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }

    // public function pdf()
    // {
    //     //obtenemos todos los usuarios
    //     $users = User::orderBy('id', 'desc')->get(); 

    //     //cargamos la vista y le pasamos los usuarios
    //     $pdf = PDF::loadView('admin.users.pdf', compact('users'));

    //     //mostramos el pdf en el navegador
    //     return $pdf->stream('admin.users.pdf');

    // } 

    public function pdf()
    {
        // Lógica para generar el PDF de los usuarios
        $users = User::all(); // O lo que necesites obtener
        $pdf = PDF::loadView('admin.users.pdf', compact('users'));
        return $pdf->stream('users.pdf');
    }

}
