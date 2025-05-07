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
        ])->syncRoles([$role1, $role2, $role3, $role4]);

        //usuarios
        Permission::create([
            'name' => 'admin.users.index',
        ])->syncRoles([$role1, $role3]); 

        Permission::create([
            'name' => 'admin.users.create',
        ])->syncRoles([$role1, $role3]);

        Permission::create([
            'name' => 'admin.users.edit',
        ])->syncRoles([$role1, $role3]);

        Permission::create([
            'name' => 'admin.users.destroy',
        ])->syncRoles([$role1, $role3]);

        Permission::create([
            'name' => 'admin.users.pdf',
        ])->syncRoles([$role1, $role3]);



        //servicio de categorias
        Permission::create([
            'name' => 'admin.service_categories.index',
        ])->syncRoles([$role1, $role3]); 

        Permission::create([
            'name' => 'admin.service_categories.create',
        ])->syncRoles([$role1, $role3]);

        Permission::create([
            'name' => 'admin.service_categories.edit',
        ])->syncRoles([$role1, $role3]);

        Permission::create([
            'name' => 'admin.service_categories.destroy',
        ])->syncRoles([$role1]);

        Permission::create([
            'name' => 'admin.service_categories.pdf',
        ])->syncRoles([$role1, $role3]);


        //servicio
        Permission::create([
            'name' => 'admin.services.index',
        ])->syncRoles([$role1, $role3]);

        Permission::create([
            'name' => 'admin.services.create',
        ])->syncRoles([$role1, $role3]);

        Permission::create([
            'name' => 'admin.services.edit',
        ])->syncRoles([$role1, $role3]);

        Permission::create([
            'name' => 'admin.services.destroy',
        ])->syncRoles([$role1 ]);

        Permission::create([
            'name' => 'admin.services.pdf',
        ])->syncRoles([$role1, $role3]);


        //persona
        Permission::create([
            'name' => 'admin.persons.index',
        ])->syncRoles([$role1, $role3, $role4]);

        Permission::create([
            'name' => 'admin.persons.create',
        ])->syncRoles([$role1, $role3, $role4]);

        Permission::create([
            'name' => 'admin.persons.edit',
        ])->syncRoles([$role1, $role3, $role4]);

        Permission::create([
            'name' => 'admin.persons.destroy',
        ])->syncRoles([$role1]);

        Permission::create([
            'name' => 'admin.persons.pdf',
        ])->syncRoles([$role1, $role3]);


        //paciente
        Permission::create([
            'name' => 'admin.patients.index',
        ])->syncRoles([$role1, $role2, $role3]);

        Permission::create([
            'name' => 'admin.patients.create',
        ])->syncRoles([$role1, $role2, $role3]);

        Permission::create([
            'name' => 'admin.patients.edit',
        ])->syncRoles([$role1, $role2, $role3]);

        Permission::create([
            'name' => 'admin.patients.destroy',
        ])->syncRoles([$role1]);

        Permission::create([
            'name' => 'admin.patients.pdf',
        ])->syncRoles([$role1, $role3]);



        //especialidad
        Permission::create([
            'name' => 'admin.specialities.index',
        ])->syncRoles([$role1, $role2, $role3]);

        Permission::create([
            'name' => 'admin.specialities.create',
        ])->syncRoles([$role1, $role3]);

        Permission::create([
            'name' => 'admin.specialities.edit',
        ])->syncRoles([$role1, $role3]);

        Permission::create([
            'name' => 'admin.specialities.destroy',
        ])->syncRoles([$role1]);

        Permission::create([
            'name' => 'admin.specialities.pdf',
        ])->syncRoles([$role1, $role3]);


        //doctor
        Permission::create([
            'name' => 'admin.doctors.index',
        ])->syncRoles([$role1, $role2, $role3]);

        Permission::create([
            'name' => 'admin.doctors.create',
        ])->syncRoles([$role1, $role3]);

        Permission::create([
            'name' => 'admin.doctors.edit',
        ])->syncRoles([$role1, $role3]);

        Permission::create([
            'name' => 'admin.doctors.destroy',
        ])->syncRoles([$role1]);

        Permission::create([
            'name' => 'admin.doctors.pdf',
        ])->syncRoles([$role1, $role3]);


        //venta
        Permission::create([
            'name' => 'admin.sales.index',
        ])->syncRoles([$role1, $role3]);

        Permission::create([
            'name' => 'admin.sales.create',
        ])->syncRoles([$role1, $role3]);

        Permission::create([
            'name' => 'admin.sales.edit',
        ])->syncRoles([$role1, $role3]);

        Permission::create([
            'name' => 'admin.sales.destroy',
        ])->syncRoles([$role1]);

        Permission::create([
            'name' => 'admin.sales.print',
        ])->syncRoles([$role1, $role3]);


        //historia clinica
        Permission::create([
            'name' => 'admin.histories.index',
        ])->syncRoles([$role1, $role2 ]);

        Permission::create([
            'name' => 'admin.histories.create',
        ])->syncRoles([$role1, $role2]);

        Permission::create([
            'name' => 'admin.histories.show',
        ])->syncRoles([$role1, $role2]);

        Permission::create([
            'name' => 'admin.histories.edit',
        ])->syncRoles([$role1, $role2]);

        Permission::create([
            'name' => 'admin.histories.destroy',
        ])->syncRoles([$role1]);

        Permission::create([
            'name' => 'admin.histories.addNote',
        ])->syncRoles([$role1, $role2]);

        Permission::create([
            'name' => 'admin.histories.pdf',
        ])->syncRoles([$role1, $role2]);


        //categoria de productos
        Permission::create([
            'name' => 'admin.product_categories.index',
        ])->syncRoles([$role1, $role4]);

        Permission::create([
            'name' => 'admin.product_categories.create',
        ])->syncRoles([$role1, $role4]);

        Permission::create([
            'name' => 'admin.product_categories.edit',
        ])->syncRoles([$role1, $role4]);

        Permission::create([
            'name' => 'admin.product_categories.destroy',
        ])->syncRoles([$role1]);

        Permission::create([
            'name' => 'admin.product_categories.pdf',
        ])->syncRoles([$role1, $role3]);


        //producto
        Permission::create([
            'name' => 'admin.products.index',
        ])->syncRoles([$role1, $role4]);

        Permission::create([
            'name' => 'admin.products.create',
        ])->syncRoles([$role1, $role4]);

        Permission::create([
            'name' => 'admin.products.edit',
        ])->syncRoles([$role1, $role4]);

        Permission::create([
            'name' => 'admin.products.destroy',
        ])->syncRoles([$role1]);

        Permission::create([
            'name' => 'admin.products.pdf',
        ])->syncRoles([$role1, $role3]);


        //proveedor
        Permission::create([
            'name' => 'admin.suppliers.index',
        ])->syncRoles([$role1, $role4]);

        Permission::create([
            'name' => 'admin.suppliers.create',
        ])->syncRoles([$role1, $role4]);

        Permission::create([
            'name' => 'admin.suppliers.edit',
        ])->syncRoles([$role1, $role4]);

        Permission::create([
            'name' => 'admin.suppliers.destroy',
        ])->syncRoles([$role1]);

        Permission::create([
            'name' => 'admin.suppliers.pdf',
        ])->syncRoles([$role1, $role3]);


        //compra
        Permission::create([
            'name' => 'admin.purchases.index',
        ])->syncRoles([$role1, $role4]);

        Permission::create([
            'name' => 'admin.purchases.create',
        ])->syncRoles([$role1, $role4]);

        Permission::create([
            'name' => 'admin.purchases.edit',
        ])->syncRoles([$role1, $role4]);

        Permission::create([
            'name' => 'admin.purchases.destroy',
        ])->syncRoles([$role1]);

        Permission::create([
            'name' => 'admin.purchases.print',
        ])->syncRoles([$role1, $role4]);










    }
}
