<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Seeder idempotente para los permisos de Recetas médicas.
 *
 * Sigue el mismo patrón que HistoryPhotoPermissionSeeder: usa firstOrCreate
 * para poder correr en una base de datos que ya tiene los roles creados,
 * sin duplicar nada.
 *
 * Ejecutar con:
 *   php artisan db:seed --class=RecetaPermissionSeeder
 */
class RecetaPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::where('name', 'Admin')->first();
        $doctor = Role::where('name', 'Doctor')->first();

        $adminYDoctor = array_filter([$admin, $doctor]);

        Permission::firstOrCreate(
            ['name' => 'admin.histories.recetas.store'],
            ['description' => 'Escribir o editar una receta médica']
        )->syncRoles($adminYDoctor);

        Permission::firstOrCreate(
            ['name' => 'admin.histories.recetas.destroy'],
            ['description' => 'Eliminar una receta médica']
        )->syncRoles($adminYDoctor);
    }
}
