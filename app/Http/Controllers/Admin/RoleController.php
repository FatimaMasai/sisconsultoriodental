<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    // Antes este controlador no tenía ningún constructor, por lo que
    // cualquier usuario logueado (sin importar su rol) podía crear, editar
    // o eliminar roles y sus permisos entrando directo por la URL. Se
    // protege igual que el resto de los controladores del panel: cada
    // acción exige el permiso admin.roles.* correspondiente (todos
    // asignados solo al rol Admin en RoleSeeder).
    public function __construct()
    {
        $this->middleware('can:admin.roles.index')->only('index', 'show');
        $this->middleware('can:admin.roles.create')->only('create', 'store');
        $this->middleware('can:admin.roles.edit')->only('edit', 'update');
        $this->middleware('can:admin.roles.destroy')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::all();
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = Permission::all();
        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        //se crea el rol
        $role = Role::create($request->all());

        //se asignan los permisos al rol
        $role->permissions()->sync($request->permissions);

        session()->flash('swal', [
            'title' => 'Roles Creado',
            'text' => '¡Bien Hecho!.',
            'icon' => 'success',
        ]);
        

        return redirect()->route('admin.roles.index', $role);
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        return view('admin.roles.show', compact('role'));
    } 

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        $permissions = Permission::all();
        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $role->update($request->all());
        $role->permissions()->sync($request->permissions);
        session()->flash('swal', [
            'title' => 'Roles Actualizado',
            'text' => '¡Bien Hecho!.',
            'icon' => 'success',
        ]);
        return redirect()->route('admin.roles.index', $role);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $role->delete();
        session()->flash('swal', [
            'title' => 'Roles Eliminado',
            'text' => '¡Bien Hecho!.',
            'icon' => 'success',
        ]);
        return redirect()->route('admin.roles.index');
    }
}
