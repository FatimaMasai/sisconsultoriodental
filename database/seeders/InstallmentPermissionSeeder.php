<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Seeder idempotente para el nuevo permiso de "registrar pago de cuota".
 *
 * A diferencia de RoleSeeder (que crea los roles y fallaría si se corre
 * dos veces), este seeder usa firstOrCreate para poder ejecutarse sin
 * duplicar datos en una base de datos que ya tiene los roles creados.
 *
 * Ejecutar con:
 *   php artisan db:seed --class=InstallmentPermissionSeeder
 */
class InstallmentPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::where('name', 'Admin')->first();
        $recepcionista = Role::where('name', 'Recepcionista')->first();

        $roles = array_filter([$admin, $recepcionista]);

        $permission = Permission::firstOrCreate(
            ['name' => 'admin.sales.payInstallment'],
            ['description' => 'Registrar pago de cuota de una venta a crédito']
        );

        if (! empty($roles)) {
            $permission->syncRoles($roles);
        }
    }
}
