<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productCategories = [
            'Material de Restauración',
            'Instrumental Quirúrgico',
            'Material de Esterilización',
            'Equipos Dentales', 

        ];
        foreach ($productCategories as $productCategory) 
        {
            ProductCategory::create([
                'name' => $productCategory, 
                'status' => true,
            ]);
        } 

    }
}
