<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Faker\Factory as Faker;
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
        // Usuario admin fijo
        User::create([
            'name' => 'Fátima Chamo Masai',
            'email' => 'fatima@gmail.com',
            'password' => bcrypt('password'),
        ])->assignRole('Admin');

        // Crear 99 usuarios aleatorios en español
        $faker = Faker::create('es_ES');

        foreach (range(1, 3) as $i) {
            User::create([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'password' => bcrypt('password'),
            ]);
        }
    }
}
