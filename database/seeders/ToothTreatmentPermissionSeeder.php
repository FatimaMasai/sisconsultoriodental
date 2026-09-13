<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Seeder idempotente para los permisos del Odontograma.
 *
 * Sigue el mismo patrón que RecetaPermissionSeeder: usa firstOrCreate para
 * poder correr en una base de datos que ya tiene los roles creados, sin
 * duplicar nada.
 *
 * Ejecutar con:
 *   php artisan db:seed --class=ToothTreatmentPermissionSeeder
 */
class ToothTreatmentPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::where('name', 'Admin')->first();
        $doctor = Role::where('name', 'Doctor')->first();

        $adminYDoctor = array_filter([$admin, $doctor]);

        Permission::firstOrCreate(
            ['name' => 'admin.histories.odontograma.store'],
            ['description' => 'Cargar o editar un tratamiento del odontograma']
        )->syncRoles($adminYDoctor);

        Permission::firstOrCreate(
            ['name' => 'admin.histories.odontograma.destroy'],
            ['description' => 'Eliminar un tratamiento del odontograma']
        )->syncRoles($adminYDoctor);
    }
}
