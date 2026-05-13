<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $structure = [
            'men' => [
                'Tops' => [
                    'Long Sleeve T-Shirts',
                    'Polos',
                    'Shirts (Button Ups)',
                    'Short Sleeve T-Shirts',
                    'Sweaters & Knitwear',
                    'Sweatshirts & Hoodies',
                    'Tank Tops & Sleeveless',
                    'Jerseys',
                ],
                'Bottoms' => [
                    'Casual Pants',
                    'Cropped Pants',
                    'Denim',
                    'Leggings',
                    'Overalls & Jumpsuits',
                    'Shorts',
                    'Sweatpants & Joggers',
                    'Swimwear',
                ],
                'Outerwear' => [
                    'Bombers',
                    'Cloaks & Capes',
                    'Denim Jackets',
                    'Heavy Coats',
                    'Leather Jackets',
                    'Light Jackets',
                    'Parkas',
                    'Raincoats',
                    'Vests',
                ],
                'Footwear' => [
                    'Boots',
                    'Casual Leather Shoes',
                    'Formal Shoes',
                    'Hi-Top Sneakers',
                    'Low-Top Sneakers',
                    'Sandals',
                    'Slip Ons',
                ],
                'Accessories' => [
                    'Bags & Luggage',
                    'Belts',
                    'Glasses',
                    'Gloves & Scarves',
                    'Hats',
                    'Jewelry & Watches',
                    'Wallets',
                    'Socks & Underwear',
                    'Sunglasses',
                ],
                'Tailoring' => [
                    'Blazers',
                    'Formal Shirting',
                    'Formal Trousers',
                    'Suits',
                    'Tuxedos',
                    'Vests',
                ],
            ],

            'women' => [
                'Tops' => [
                    'Blouses',
                    'Crop Tops',
                    'Long Sleeve Tops',
                    'Short Sleeve Tops',
                    'Sweaters & Knitwear',
                    'Sweatshirts & Hoodies',
                    'Tank Tops',
                ],
                'Bottoms' => [
                    'Jeans',
                    'Pants',
                    'Shorts',
                    'Skirts',
                    'Leggings',
                ],
                'Outerwear' => [
                    'Blazers',
                    'Coats',
                    'Denim Jackets',
                    'Leather Jackets',
                    'Light Jackets',
                    'Puffer Jackets',
                    'Vests',
                ],
                'Footwear' => [
                    'Boots',
                    'Flats',
                    'Heels',
                    'Low-Top Sneakers',
                    'Hi-Top Sneakers',
                    'Sandals',
                ],
                'Accessories' => [
                    'Bags',
                    'Belts',
                    'Glasses',
                    'Hats',
                    'Jewelry',
                    'Scarves',
                    'Wallets',
                    'Watches',
                ],
                'Tailoring' => [
                    'Blazers',
                    'Formal Dresses',
                    'Formal Pants',
                    'Sets',
                    'Suits',
                ],
            ],
        ];

        foreach ($structure as $department => $parents) {
            foreach ($parents as $parentName => $children) {
                $parentSlug = $this->makeUniqueSlug($department . '-' . $parentName);

                $parent = Category::firstOrCreate(
                    [
                        'name' => $parentName,
                        'department' => $department,
                        'parent_id' => null,
                    ],
                    [
                        'slug' => $parentSlug,
                    ]
                );

                foreach ($children as $childName) {
                    $childSlug = $this->makeUniqueSlug($department . '-' . $parentName . '-' . $childName);

                    Category::firstOrCreate(
                        [
                            'name' => $childName,
                            'department' => $department,
                            'parent_id' => $parent->id,
                        ],
                        [
                            'slug' => $childSlug,
                        ]
                    );
                }
            }
        }
    }

    private function makeUniqueSlug(string $value): string
    {
        $base = Str::slug($value);
        $slug = $base;
        $counter = 1;

        while (Category::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}