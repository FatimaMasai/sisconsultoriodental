<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            ['name' => 'Limpieza Dental', 'category' => 'Limpieza', 'price' => 500],
            ['name' => 'Aplicación de Flúor', 'category' => 'Limpieza', 'price' => 300],
            ['name' => 'Brackets Metálicos', 'category' => 'Ortodoncia', 'price' => 10000],
            ['name' => 'Endodoncia (Tratamiento de Conducto)', 'category' => 'Endodoncia', 'price' => 2500],
            ['name' => 'Consulta Infantil', 'category' => 'Odontopediatría', 'price' => 400],
            ['name' => 'Blanqueamiento Dental', 'category' => 'Estética Dental', 'price' => 2000],
            ['name' => 'Extracción de Muelas del Juicio', 'category' => 'Cirugía', 'price' => 1800],
            ['name' => 'Radiografía Panorámica', 'category' => 'Radiología', 'price' => 600],
        ];

        foreach ($services as $service) {
            $category = ServiceCategory::where('name', $service['category'])->first();
            if ($category) {
                Service::create([
                    'name' => $service['name'],
                    'price' => $service['price'],
                    'status' => true, // o false si deseas algunos desactivados
                    'service_category_id' => $category->id,
                ]);
            }
        }


    }
}
