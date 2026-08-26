<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Seeder idempotente para los permisos del nuevo módulo de Citas.
 *
 * A diferencia de RoleSeeder (que crea los roles y fallaría si se corre
 * dos veces), este seeder usa firstOrCreate para poder ejecutarse sin
 * duplicar datos en una base de datos que ya tiene los roles creados.
 *
 * Ejecutar con:
 *   php artisan db:seed --class=AppointmentPermissionSeeder
 */
class AppointmentPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::where('name', 'Admin')->first();
        $doctor = Role::where('name', 'Doctor')->first();
        $recepcionista = Role::where('name', 'Recepcionista')->first();

        $verYRecepcion = array_filter([$admin, $doctor, $recepcionista]);
        $soloRecepcion = array_filter([$admin, $recepcionista]);

        Permission::firstOrCreate(
            ['name' => 'admin.appointments.index'],
            ['description' => 'Ver la agenda de citas']
        )->syncRoles($verYRecepcion);

        Permission::firstOrCreate(
            ['name' => 'admin.appointments.create'],
            ['description' => 'Agendar citas']
        )->syncRoles($soloRecepcion);

        Permission::firstOrCreate(
            ['name' => 'admin.appointments.edit'],
            ['description' => 'Editar citas']
        )->syncRoles($soloRecepcion);

        Permission::firstOrCreate(
            ['name' => 'admin.appointments.cancel'],
            ['description' => 'Cancelar citas']
        )->syncRoles($soloRecepcion);
    }
}
