<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
         // Call the RoleAndPermissionSeeder first to ensure roles and permissions exist
         $this->call(RoleAndPermissionSeeder::class);

         // Call other seeders to populate data
         $this->call(CategorySeeder::class);
         $this->call(ProductSeeder::class);
         $this->call(UserSeeder::class); // Call UserSeeder after RoleAndPermissionSeeder
    }
}
