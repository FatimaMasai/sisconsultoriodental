<?php

namespace Database\Seeders;

use App\Models\Person;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class PersonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('es_ES');


        foreach (range(1, 15) as $i) {
            Person::create([
                'name' => $faker->firstName,
                'last_name_father' => $faker->lastName,
                'last_name_mother' => $faker->lastName,
                'identity_card' => $faker->unique()->numerify('#######'),
                'birth_date' => $faker->date('Y-m-d', '2005-12-31'),
                'gender' => $faker->randomElement(['Femenino', 'Masculino']),
                'phone' => $faker->numerify('7#######'),
                'email' => $faker->unique()->safeEmail,
                'address' => $faker->address,
                'status' => true,
            ]);
        }$faker = Faker::create();


        
    }
}
