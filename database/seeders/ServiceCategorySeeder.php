<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $serviceCategories = [
            'Limpieza',
            'Ortodoncia',
            'Endodoncia',
            'Odontopediatría',
            'Estética Dental',
            'Cirugía',
            'Radiología',

        ];
        foreach ($serviceCategories as $serviceCategory) 
        {
            ServiceCategory::create([
                'name' => $serviceCategory, 
                'status' => true,
            ]);
        } 
    }
}
