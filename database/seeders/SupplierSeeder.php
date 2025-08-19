<?php

namespace Database\Seeders;

use App\Models\Person;
use App\Models\Supplier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class SupplierSeeder extends Seeder
{

   
    public function run(): void
    {
        

        $faker = Faker::create('es_ES');

        // Obtener personas disponibles
        $personIds = Person::pluck('id')->toArray();
 
    }


}
