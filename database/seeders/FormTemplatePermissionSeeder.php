<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Seeder idempotente para el permiso del constructor de plantillas de
 * historial (FormTemplate/FormSection/FormField) por especialidad.
 *
 * Sigue el mismo patrón que HistoryPhotoPermissionSeeder: usa firstOrCreate
 * para poder correr en una base de datos que ya tiene los roles creados,
 * sin duplicar nada.
 *
 * Ejecutar con:
 *   php artisan db:seed --class=FormTemplatePermissionSeeder
 */
class FormTemplatePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::where('name', 'Admin')->first();
        $recepcionista = Role::where('name', 'Recepcionista')->first();

        // Es una pantalla de configuración clínica (define qué campos se
        // llenan en cada especialidad), no una acción del día a día: la
        // dejamos en el mismo grupo que "editar especialidades".
        $roles = array_filter([$admin, $recepcionista]);

        Permission::firstOrCreate(
            ['name' => 'admin.form_templates.edit'],
            ['description' => 'Configurar la plantilla de historial de una especialidad']
        )->syncRoles($roles);
    }
}
