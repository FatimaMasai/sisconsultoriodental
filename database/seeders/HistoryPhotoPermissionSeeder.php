<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Seeder idempotente para los permisos de fotos de Antes/Después
 * en el Historial Médico.
 *
 * A diferencia de RoleSeeder (que crea los roles y fallaría si se corre
 * dos veces), este seeder usa firstOrCreate para poder ejecutarse sin
 * duplicar datos en una base de datos que ya tiene los roles creados.
 *
 * Ejecutar con:
 *   php artisan db:seed --class=HistoryPhotoPermissionSeeder
 */
class HistoryPhotoPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::where('name', 'Admin')->first();
        $doctor = Role::where('name', 'Doctor')->first();

        $adminYDoctor = array_filter([$admin, $doctor]);

        Permission::firstOrCreate(
            ['name' => 'admin.histories.photos.store'],
            ['description' => 'Subir fotos de antes/después al historial médico']
        )->syncRoles($adminYDoctor);

        Permission::firstOrCreate(
            ['name' => 'admin.histories.photos.destroy'],
            ['description' => 'Eliminar fotos de antes/después del historial médico']
        )->syncRoles($adminYDoctor);
    }
}
