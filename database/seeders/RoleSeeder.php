<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role1 = Role::create([
            'name' => 'Admin' 
        ]);


        $role2 = Role::create([
            'name' => 'Doctor' 
        ]);


        $role3 = Role::create([
            'name' => 'Recepcionista' 
        ]);


        $role4 = Role::create([
            'name' => 'Compras' 
        ]);



        //permisos
        Permission::create([
            'name' => 'admin.dashboard', //o dasboard directo xq admin ya esta agregado haremos la prueba
            'description' => 'Ver dashboard',
            ])->syncRoles([$role1, $role2, $role3, $role4]);


        //roles
        Permission::create([
            'name' => 'admin.roles.index',
            'description' => 'Ver listado de roles',
        ])->syncRoles([$role1]);

        Permission::create([
            'name' => 'admin.roles.create',
            'description' => 'Crear roles',

        ])->syncRoles([$role1]);
        Permission::create([
            'name' => 'admin.roles.edit',
            'description' => 'Editar roles',

        ])->syncRoles([$role1]);
        Permission::create([
            'name' => 'admin.roles.destroy',
            'description' => 'Eliminar roles',

        ])->syncRoles([$role1]);
        Permission::create([
            'name' => 'admin.roles.pdf',
            'description' => 'Imprimir roles',
        ])->syncRoles([$role1]);


        //usuarios
        Permission::create([
            'name' => 'admin.users.index',
            'description' => 'Ver listado de usuarios',
        ])->syncRoles([$role1, $role3]); 

        Permission::create([
            'name' => 'admin.users.create',
            'description' => 'Crear usuarios',
        ])->syncRoles([$role1, $role3]);

        Permission::create([
            'name' => 'admin.users.edit',
            'description' => 'Editar usuarios',
        ])->syncRoles([$role1, $role3]);

        Permission::create([
            'name' => 'admin.users.destroy',
            'description' => 'Eliminar usuarios',
        ])->syncRoles([$role1, $role3]);

        Permission::create([
            'name' => 'admin.users.pdf',
            'description' => 'Imprimir usuarios',
        ])->syncRoles([$role1, $role3]);




        //servicio de categorias
        Permission::create([
            'name' => 'admin.service_categories.index',
            'description' => 'Ver listado de categorias de servicios',
        ])->syncRoles([$role1, $role3]); 

        Permission::create([
            'name' => 'admin.service_categories.create',
            'description' => 'Crear categorias de servicios',
        ])->syncRoles([$role1, $role3]);

        Permission::create([
            'name' => 'admin.service_categories.edit',
            'description' => 'Editar categorias de servicios',
        ])->syncRoles([$role1, $role3]);

        Permission::create([
            'name' => 'admin.service_categories.destroy',
            'description' => 'Eliminar categorias de servicios',
        ])->syncRoles([$role1]);

        Permission::create([
            'name' => 'admin.service_categories.pdf',
            'description' => 'Imprimir categorias de servicios',
        ])->syncRoles([$role1, $role3]);


        //servicio
        Permission::create([
            'name' => 'admin.services.index',
            'description' => 'Ver listado de servicios',
        ])->syncRoles([$role1, $role3]);

        Permission::create([
            'name' => 'admin.services.create',
            'description' => 'Crear servicios',
        ])->syncRoles([$role1, $role3]);

        Permission::create([
            'name' => 'admin.services.edit',
            'description' => 'Editar servicios',
        ])->syncRoles([$role1, $role3]);

        Permission::create([
            'name' => 'admin.services.destroy',
            'description' => 'Eliminar servicios',
        ])->syncRoles([$role1 ]);

        Permission::create([
            'name' => 'admin.services.pdf',
            'description' => 'Imprimir servicios',
        ])->syncRoles([$role1, $role3]);


        //persona
        Permission::create([
            'name' => 'admin.persons.index',
            'description' => 'Ver listado de personas',
        ])->syncRoles([$role1, $role3, $role4]);

        Permission::create([
            'name' => 'admin.persons.create',
            'description' => 'Crear personas',
        ])->syncRoles([$role1, $role3, $role4]);

        Permission::create([
            'name' => 'admin.persons.edit',
            'description' => 'Editar personas',
        ])->syncRoles([$role1, $role3, $role4]);

        Permission::create([
            'name' => 'admin.persons.destroy',
            'description' => 'Eliminar personas',
        ])->syncRoles([$role1]);

        Permission::create([
            'name' => 'admin.persons.pdf',
            'description' => 'Imprimir personas',
        ])->syncRoles([$role1, $role3]);


        //paciente
        Permission::create([
            'name' => 'admin.patients.index',
            'description' => 'Ver listado de pacientes',
        ])->syncRoles([$role1, $role2, $role3]);

        Permission::create([
            'name' => 'admin.patients.create',
            'description' => 'Crear pacientes',
        ])->syncRoles([$role1, $role2, $role3]);

        Permission::create([
            'name' => 'admin.patients.edit',
            'description' => 'Editar pacientes',
        ])->syncRoles([$role1, $role2, $role3]);

        Permission::create([
            'name' => 'admin.patients.destroy',
            'description' => 'Eliminar pacientes',
        ])->syncRoles([$role1]);

        Permission::create([
            'name' => 'admin.patients.pdf',
            'description' => 'Imprimir pacientes',
        ])->syncRoles([$role1, $role3]);



        //especialidad
        Permission::create([
            'name' => 'admin.specialities.index',
            'description' => 'Ver listado de especialidades',
        ])->syncRoles([$role1, $role2, $role3]);

        Permission::create([
            'name' => 'admin.specialities.create',
            'description' => 'Crear especialidades',
        ])->syncRoles([$role1, $role3]);

        Permission::create([
            'name' => 'admin.specialities.edit',
            'description' => 'Editar especialidades',
        ])->syncRoles([$role1, $role3]);

        Permission::create([
            'name' => 'admin.specialities.destroy',
            'description' => 'Eliminar especialidades',
        ])->syncRoles([$role1]);

        Permission::create([
            'name' => 'admin.specialities.pdf',
            'description' => 'Imprimir especialidades',
        ])->syncRoles([$role1, $role3]);


        //doctor
        Permission::create([
            'name' => 'admin.doctors.index',
            'description' => 'Ver listado de doctores',
        ])->syncRoles([$role1, $role2, $role3]);

        Permission::create([
            'name' => 'admin.doctors.create',
            'description' => 'Crear doctores',
        ])->syncRoles([$role1, $role3]);

        Permission::create([
            'name' => 'admin.doctors.edit',
            'description' => 'Editar doctores',
        ])->syncRoles([$role1, $role3]);

        Permission::create([
            'name' => 'admin.doctors.destroy',
            'description' => 'Eliminar doctores',
        ])->syncRoles([$role1]);

        Permission::create([
            'name' => 'admin.doctors.pdf',
            'description' => 'Imprimir doctores',
        ])->syncRoles([$role1, $role3]);


        //venta
        Permission::create([
            'name' => 'admin.sales.index',
            'description' => 'Ver listado de ventas',
        ])->syncRoles([$role1, $role3]);

        Permission::create([
            'name' => 'admin.sales.create',
            'description' => 'Crear ventas',
        ])->syncRoles([$role1, $role3]);

        Permission::create([
            'name' => 'admin.sales.edit',
            'description' => 'Editar ventas',
        ])->syncRoles([$role1, $role3]);

        Permission::create([
            'name' => 'admin.sales.destroy',
            'description' => 'Eliminar ventas',
        ])->syncRoles([$role1]);

        Permission::create([
            'name' => 'admin.sales.print',
            'description' => 'Imprimir ventas',
        ])->syncRoles([$role1, $role3]);


        //historia clinica
        Permission::create([
            'name' => 'admin.histories.index',
            'description' => 'Ver listado de historias clinicas',
        ])->syncRoles([$role1, $role2 ]);

        Permission::create([
            'name' => 'admin.histories.create',
            'description' => 'Crear historias clinicas',
        ])->syncRoles([$role1, $role2]);

        Permission::create([
            'name' => 'admin.histories.show',
            'description' => 'Ver historias clinicas',
        ])->syncRoles([$role1, $role2]);

        // Permission::create([
        //     'name' => 'admin.histories.edit',
        //     'description' => 'Editar historias clinicas',
        // ])->syncRoles([$role1, $role2]);

        // Permission::create([
        //     'name' => 'admin.histories.destroy',
        //     'description' => 'Eliminar historias clinicas',
        // ])->syncRoles([$role1]);

        Permission::create([
            'name' => 'admin.histories.addNote',
            'description' => 'Agregar notas a historias clinicas',
        ])->syncRoles([$role1, $role2]);

        Permission::create([
            'name' => 'admin.histories.pdf',
            'description' => 'Imprimir historias clinicas',
        ])->syncRoles([$role1, $role2]);


        //categoria de productos
        Permission::create([
            'name' => 'admin.product_categories.index',
            'description' => 'Ver listado de categorias de productos',
        ])->syncRoles([$role1, $role4]);

        Permission::create([
            'name' => 'admin.product_categories.create',
            'description' => 'Crear categorias de productos',
        ])->syncRoles([$role1, $role4]);

        Permission::create([
            'name' => 'admin.product_categories.edit',
            'description' => 'Editar categorias de productos',
        ])->syncRoles([$role1, $role4]);

        Permission::create([
            'name' => 'admin.product_categories.destroy',
            'description' => 'Eliminar categorias de productos',
        ])->syncRoles([$role1]);

        Permission::create([
            'name' => 'admin.product_categories.pdf',
            'description' => 'Imprimir categorias de productos',
        ])->syncRoles([$role1, $role3]);


        //producto
        Permission::create([
            'name' => 'admin.products.index',
            'description' => 'Ver listado de productos',
        ])->syncRoles([$role1, $role4]);

        Permission::create([
            'name' => 'admin.products.create',
            'description' => 'Crear productos',
        ])->syncRoles([$role1, $role4]);

        Permission::create([
            'name' => 'admin.products.edit',
            'description' => 'Editar productos',
        ])->syncRoles([$role1, $role4]);

        Permission::create([
            'name' => 'admin.products.destroy',
            'description' => 'Eliminar productos',
        ])->syncRoles([$role1]);

        Permission::create([
            'name' => 'admin.products.pdf',
            'description' => 'Imprimir productos',
        ])->syncRoles([$role1, $role3]);


        //proveedor
        Permission::create([
            'name' => 'admin.suppliers.index',
            'description' => 'Ver listado de proveedores',
        ])->syncRoles([$role1, $role4]);

        Permission::create([
            'name' => 'admin.suppliers.create',
            'description' => 'Crear proveedores',
        ])->syncRoles([$role1, $role4]);

        Permission::create([
            'name' => 'admin.suppliers.edit',
            'description' => 'Editar proveedores',
        ])->syncRoles([$role1, $role4]);

        Permission::create([
            'name' => 'admin.suppliers.destroy',
            'description' => 'Eliminar proveedores',
        ])->syncRoles([$role1]);

        Permission::create([
            'name' => 'admin.suppliers.pdf',
            'description' => 'Imprimir proveedores',
        ])->syncRoles([$role1, $role3]);


        //compra
        Permission::create([
            'name' => 'admin.purchases.index',
            'description' => 'Ver listado de compras',
        ])->syncRoles([$role1, $role4]);

        Permission::create([
            'name' => 'admin.purchases.create',
            'description' => 'Crear compras',
        ])->syncRoles([$role1, $role4]);

        Permission::create([
            'name' => 'admin.purchases.edit',
            'description' => 'Editar compras',
        ])->syncRoles([$role1, $role4]);

        Permission::create([
            'name' => 'admin.purchases.destroy',
            'description' => 'Eliminar compras',
        ])->syncRoles([$role1]);

        Permission::create([
            'name' => 'admin.purchases.print',
            'description' => 'Imprimir compras',
        ])->syncRoles([$role1, $role4]);










    }
}
