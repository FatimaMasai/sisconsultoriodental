<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Seeder idempotente para los permisos de "Configuración del sistema"
 * (Google Calendar y Apariencia). Sigue el mismo patrón que
 * ToothTreatmentPermissionSeeder/RecetaPermissionSeeder: usa firstOrCreate
 * para poder correr en una base que ya tiene los roles creados, sin
 * duplicar nada.
 *
 * admin.settings.google ya existía sembrado a mano en la base de datos
 * (por eso el sistema funcionaba antes de este seeder), pero nunca había
 * quedado guardado en ningún seeder: un migrate:fresh --seed lo hubiera
 * borrado sin dejar forma de volver a crearlo salvo a mano.
 *
 * Son configuraciones a nivel sistema, no del día a día: solo Admin.
 *
 * Ejecutar con:
 *   php artisan db:seed --class=SettingsPermissionSeeder
 */
class SettingsPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::where('name', 'Admin')->first();

        $roles = array_filter([$admin]);

        Permission::firstOrCreate(
            ['name' => 'admin.settings.google'],
            ['description' => 'Configurar la sincronización con Google Calendar']
        )->syncRoles($roles);

        Permission::firstOrCreate(
            ['name' => 'admin.settings.appearance'],
            ['description' => 'Configurar el logo y el color de marca del sistema']
        )->syncRoles($roles);
    }
}
