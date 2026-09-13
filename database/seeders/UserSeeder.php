<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    // public function run(): void
    // {
    //     User::create([
    //         'name' => 'Fátima Chamo Masai',
    //         'email' => 'fatima@gmail.com',
    //         'password' => bcrypt('password'),
    //     ])->assignRole('Admin');
        
    //     User::factory(99)->create('es_ES');

    // }

    public function run(): void
    {
        // Único usuario que deja este seeder: el admin. No se crean
        // usuarios de relleno (antes se generaban 3 usuarios aleatorios
        // con Faker) para que una base recién instalada quede lista para
        // cargar datos reales, no de prueba.
        User::create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
        ])->assignRole('Admin');
    }
}
