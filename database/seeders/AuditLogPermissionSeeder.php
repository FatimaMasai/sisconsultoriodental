<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Seeder idempotente para el permiso de la bitácora de auditoría.
 * Igual que AppointmentPermissionSeeder: usa firstOrCreate para poder
 * ejecutarse sin duplicar datos en una base de datos ya sembrada.
 *
 * Ejecutar con:
 *   php artisan db:seed --class=AuditLogPermissionSeeder
 */
class AuditLogPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::where('name', 'Admin')->first();

        Permission::firstOrCreate(
            ['name' => 'admin.audit_logs.index'],
            ['description' => 'Ver la bitácora de anulaciones']
        )->syncRoles(array_filter([$admin]));
    }
}
