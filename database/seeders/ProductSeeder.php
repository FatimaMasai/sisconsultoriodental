<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            ['name' => 'Resina Dental Filtek Z350 XT', 'category' => 'Material de Restauración', 'price' => 500],
            ['name' => 'Forceps de extracción dental', 'category' => 'Instrumental Quirúrgico', 'price' => 300],
            ['name' => 'Bolsas de esterilización autoadhesivas', 'category' => 'Material de Esterilización', 'price' => 10000],
            ['name' => 'Unidad dental con lámpara LED', 'category' => 'Equipos Dentales', 'price' => 25500],
        ];

        foreach ($products as $product) {
            $category = ProductCategory::where('name', $product['category'])->first();
            if ($category) {
                Product::create([
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'status' => true, // o false si deseas algunos desactivados
                    'product_category_id' => $category->id,
                ]);
            }
        }
    }
}
