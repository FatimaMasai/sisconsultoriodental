<?php

namespace Database\Seeders;
 
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        
        


        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            SpecialitySeeder::class,

            // Permisos que se agregaron después de RoleSeeder y quedaron
            // como seeders sueltos (se corrían a mano con
            // `db:seed --class=...`). Van acá para que un `migrate:fresh
            // --seed` deje el sistema completo, sin que Odontograma o
            // Recetas queden bloqueados por falta de permisos. Usan
            // firstOrCreate, así que son seguros de correr siempre.
            FormTemplatePermissionSeeder::class,
            RecetaPermissionSeeder::class,
            ToothTreatmentPermissionSeeder::class,
            SettingsPermissionSeeder::class,

            // A propósito NO se llaman acá: ServiceCategorySeeder,
            // ServiceSeeder, ProductCategorySeeder, ProductSeeder,
            // PersonSeeder, PatientSeeder, DoctorSeeder. Esos seeders
            // cargan datos de ejemplo/prueba (Faker) que no queremos que
            // vuelvan a aparecer en una base ya lista para el cliente.
        ]);

    }
}
