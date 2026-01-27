<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
public function run(): void
{
    // Не нужно очищать таблицы, firstOrCreate сам проверит дубликаты
    
    $categories = [
        ['name' => 'Футболки', 'slug' => 't-shirts'],
        ['name' => 'Джинсы', 'slug' => 'jeans'],
        ['name' => 'Худи', 'slug' => 'hoodies'],
        ['name' => 'Куртки', 'slug' => 'jackets'],
        ['name' => 'Рубашки', 'slug' => 'shirts'],
    ];
    
    foreach ($categories as $category) {
        // firstOrCreate проверяет, существует ли запись с таким slug
        Category::firstOrCreate(
            ['slug' => $category['slug']],
            $category
        );
    }

        // Create the products 
        $products = [
            [
                'name' => '1',
                'description' => 'tshirt',
                'price' => 29.99,
                'image_url' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab',
                'category_id' => 1,
                'is_featured' => true
            ],
            [
                'name' => '2',
                'description' => 'Jeans',
                'price' => 89.99,
                'image_url' => 'https://images.unsplash.com/photo-1542272604-787c3835535d',
                'category_id' => 2,
                'is_featured' => true
            ],
            // add products later
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}