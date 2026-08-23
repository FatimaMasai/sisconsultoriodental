<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Seeder idempotente para el nuevo permiso "anular compra".
 *
 * A diferencia de RoleSeeder (que crea los roles y fallaría si se corre
 * dos veces), este seeder usa firstOrCreate para poder ejecutarse sin
 * duplicar datos en una base de datos que ya tiene los roles creados.
 *
 * Ejecutar con:
 *   php artisan db:seed --class=PurchaseCancelPermissionSeeder
 */
class PurchaseCancelPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::where('name', 'Admin')->first();
        $compras = Role::where('name', 'Compras')->first();

        $roles = array_filter([$admin, $compras]);

        $permission = Permission::firstOrCreate(
            ['name' => 'admin.purchases.cancel'],
            ['description' => 'Anular compras']
        );

        if (! empty($roles)) {
            $permission->syncRoles($roles);
        }
    }
}
