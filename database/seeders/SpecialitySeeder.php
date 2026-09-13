<?php

namespace Database\Seeders;

use App\Models\Speciality;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SpecialitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Las únicas especialidades que realmente se usan en la clínica.
        // (Ortodoncia, Endodoncia, Odontopediatría, Estética Dental y
        // Medicina Estética venían del seeder original/genérico y no se
        // usan: se sacaron a pedido para no volver a crearlas de más.)
        $specialities = [
            'Odontología General',
            'Medicina Ortomolecular',
            'Nutrición',
        ];
        foreach ($specialities as $speciality) 
        {
            Speciality::create([
                'name' => $speciality, 
                'status' => true,
            ]);
        } 
    }
}
