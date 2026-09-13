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

        // Un doctor = una persona (un solo id), y cada doctor debe ser una
        // persona distinta (por eso se sortean 5 ids sin repetir, no un
        // randomElement() que podría repetir la misma persona dos veces).
        $doctorPersonIds = (array) $faker->randomElements($personIds, 5, false);

        foreach ($doctorPersonIds as $personId) {
            Doctor::create([
                'status' => true,
                'person_id' => $personId,
                'speciality_id' => $faker->randomElement($specialityIds),
            ]);
        }
    }
}
