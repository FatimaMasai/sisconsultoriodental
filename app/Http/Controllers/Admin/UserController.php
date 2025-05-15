<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf as PDF; 
use Spatie\Permission\Models\Role;


class UserController extends Controller
{
    //proteccion de rutas a través de un constructor con middleware 

  
    public function __construct()
    {
        // $this->middleware('auth');
        $this->middleware('can:admin.users.index')->only('index');
        $this->middleware('can:admin.users.edit')->only('edit', 'update');
        $this->middleware('can:admin.users.pdf')->only('pdf');
    }


    public function index()
    {
        return view('admin.users.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::all();
        return view('admin.users.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    // Validación de los datos del usuario
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8|confirmed',
    ]);

    // Creación del nuevo usuario
    $user = new User();
    $user->name = $request->input('name');
    $user->email = $request->input('email');
    $user->password = bcrypt($request->input('password'));
    $user->save();

    // Asignar roles
    if ($request->has('roles')) {
        $user->roles()->sync($request->roles);
    }

    session()->flash('swal', [
        'title' => 'Usuario Creado',
        'text' => '¡Bien Hecho!',
        'icon' => 'success',
    ]);

    return redirect()->route('admin.users.index');
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
        // Verifica si el usuario tiene un rol asignado
        if ($user->roles()->exists()) {
            session()->flash('swal', [
                'title' => 'Error',
                'text' => 'No se puede eliminar un usuario con roles asignados.',
                'icon' => 'error',
            ]);
            return redirect()->route('admin.users.index');
        }

        // Elimina el usuario
        $user->delete();

        session()->flash('swal', [
            'title' => 'Usuario Eliminado',
            'text' => '¡Bien Hecho!.',
            'icon' => 'success',
        ]);

        return redirect()->route('admin.users.index');
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
