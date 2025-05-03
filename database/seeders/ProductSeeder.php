<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product; // Import Product model
use App\Models\Category; // Import Category model
use Illuminate\Support\Str; // To generate slugs

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some categories to associate products with
        $electronicsCategory = Category::where('slug', 'electronics')->first();
        $clothingCategory = Category::where('slug', 'clothing')->first();
        $booksCategory = Category::where('slug', 'books')->first();

        $products = [
            [
                'name' => 'Smartphone X',
                'description' => 'Latest model smartphone with advanced features.',
                'price' => 699.99,
                'stock' => 50,
                'is_active' => true,
                'category_id' => $electronicsCategory->id ?? null, // Associate with Electronics if found
                'image_path' => 'products/smartphone-x.jpg', // Placeholder image path
            ],
            [
                'name' => 'Laptop Pro',
                'description' => 'High performance laptop for professionals.',
                'price' => 1200.00,
                'stock' => 30,
                'is_active' => true,
                'category_id' => $electronicsCategory->id ?? null, // Associate with Electronics if found
                 'image_path' => 'products/laptop-pro.jpg', // Placeholder image path
            ],
            [
                'name' => 'T-Shirt Basic',
                'description' => 'Comfortable cotton t-shirt.',
                'price' => 15.50,
                'stock' => 200,
                'is_active' => true,
                'category_id' => $clothingCategory->id ?? null, // Associate with Clothing if found
                 'image_path' => 'products/tshirt-basic.webp', // Placeholder image path
            ],
             [
                'name' => 'Jeans Slim Fit',
                'description' => 'Stylish slim fit jeans.',
                'price' => 45.00,
                'stock' => 100,
                'is_active' => false, // Example of an inactive product
                'category_id' => $clothingCategory->id ?? null, // Associate with Clothing if found
                 'image_path' => 'products/jeans-slim.jpeg', // Placeholder image path
            ],
            [
                'name' => 'The Great Novel',
                'description' => 'A captivating story that will keep you hooked.',
                'price' => 20.00,
                'stock' => 75,
                'is_active' => true,
                'category_id' => $booksCategory->id ?? null, // Associate with Books if found
                 'image_path' => 'products/great-novel.webp', // Placeholder image path
            ],
        ];

        foreach ($products as $productData) {
             // Ensure slug exists, generate if not provided (though we provide here)
             if (!isset($productData['slug']) || empty($productData['slug'])) {
                 $productData['slug'] = Str::slug($productData['name']);
             }

            // Create product if it doesn't exist by slug
            Product::firstOrCreate(
                ['slug' => $productData['slug']], // Use slug for checking existence
                $productData // Use the rest of the data for creation
            );
        }

        $this->command->info('Products seeded successfully!');
    }
}
