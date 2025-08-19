<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Person;
use App\Models\Speciality;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Faker\Factory as Faker;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('es_ES');

        $personIds = Person::pluck('id')->toArray();
        $specialityIds = Speciality::pluck('id')->toArray();

        foreach (range(1, 5) as $i) {
            Doctor::create([
                'status' => true,
                //'person_id' => $faker->randomElement($personIds), // un solo ID
                'person_id' => $personIds, //cada paciente debe tener una persona asociada
                'speciality_id' => $faker->randomElement($specialityIds),
            ]);
        }
    }
}
