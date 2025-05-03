<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category; // Import Category model
use Illuminate\Support\Str; // To generate slugs

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Electronics', 'description' => 'Electronic devices and gadgets.'],
            ['name' => 'Clothing', 'description' => 'Apparel and fashion items.'],
            ['name' => 'Books', 'description' => 'Various genres of books.'],
            ['name' => 'Home & Garden', 'description' => 'Items for home and garden.'],
        ];

        foreach ($categories as $categoryData) {
            // Create category if it doesn't exist by name
            Category::firstOrCreate(
                ['name' => $categoryData['name']],
                [
                    'slug' => Str::slug($categoryData['name']), // Generate slug
                    'description' => $categoryData['description'],
                ]
            );
        }

        $this->command->info('Categories seeded successfully!');
    }
}
