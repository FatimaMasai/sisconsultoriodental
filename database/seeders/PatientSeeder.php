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

        // Cada persona sembrada se convierte en un paciente (relación 1 a 1: Person hasOne Patient).
        $personIds = Person::pluck('id');

        foreach ($personIds as $personId) {
            Patient::create([
                'allergy' => $faker->randomElement(['Ninguna', 'Ninguna', 'Ninguna', 'Penicilina', 'Aspirina', 'Ibuprofeno', 'Lácteos', 'Polen']),
                'observation' => $faker->sentence(4),
                'recommended_by' => $faker->name,
                'responsible_person' => $faker->name,
                'medical_history' => $faker->sentence(3),
                'status' => true,
                'person_id' => $personId, // un solo id por paciente, no el arreglo completo
            ]);
        }
    }
}
