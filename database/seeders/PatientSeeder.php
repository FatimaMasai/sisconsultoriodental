<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\Person;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Faker\Factory as Faker;


class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        

        $faker = Faker::create('es_ES');

        // Obtener personas disponibles
        $personIds = Person::pluck('id')->toArray();

        foreach (range(1, 10) as $i) {
            Patient::create([
                'allergy' => $faker->randomElement(['Ninguna', 'Penicilina', 'Aspirina', 'Ibuprofeno', 'Lácteos']),
                'observation' => $faker->sentence(4),
                'recommended_by' => $faker->name,
                'responsible_person' => $faker->name,
                'medical_history' => $faker->sentence(2),
                'status' => true,
                //'person_id' => $faker->randomElement($personIds),
                'person_id' => $personIds, //cada paciente debe tener una persona asociada
            ]);
        }
    }
}
