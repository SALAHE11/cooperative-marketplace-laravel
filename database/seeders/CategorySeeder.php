<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Cosmétiques', 'description' => 'Produits de beauté et cosmétiques naturels'],
            ['name' => 'Alimentaire', 'description' => 'Produits alimentaires traditionnels et biologiques'],
            ['name' => 'Artisanat', 'description' => 'Produits artisanaux et décoratifs'],
            ['name' => 'Textile', 'description' => 'Vêtements et accessoires textiles'],
            ['name' => 'Épices et Herbes', 'description' => 'Épices, herbes et produits séchés'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
