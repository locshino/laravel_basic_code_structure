<?php
// database/seeders/RoleAndPermissionSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role; // Import Spatie Role model
use Spatie\Permission\Models\Permission; // Import Spatie Permission model
use App\Models\User; // Import your User model
use Illuminate\Support\Facades\Hash; // To create a test user password

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        // This is important when adding new permissions or roles
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // --- 1. Create Permissions ---
        // Define permissions based on the required features

        $permissions = [
            // Product Permissions
            'manage products', // Covers create, update, delete for products
            'create products',
            'update products',
            'delete products',
            'view all products', // Permission to view inactive products (for Manager/Admin)

            // Category Permissions
            'manage categories', // Covers create, update, delete for categories

            // User Management Permissions (Typically Admin Only)
            'create users',
            'manage users',    // Covers listing, viewing, updating, deleting users
            'assign roles',    // Covers assigning/removing roles to users
        ];

        // Create permissions if they don't exist
        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        $allPermissions = Permission::all(); // Get all created permissions

        // --- 2. Create Roles ---

        $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
        $roleManager = Role::firstOrCreate(['name' => 'manager']);
        $roleGuest = Role::firstOrCreate(['name' => 'guest']); // Role for non-authenticated users or default new users


        // --- 3. Assign Permissions to Roles ---

        // Admin gets all permissions
        $roleAdmin->syncPermissions($allPermissions); // syncPermissions removes existing and adds new ones

        // Manager gets permissions for products and categories
        $managerPermissions = [
            'manage products', // Manager can create, update, delete products
            'create products',
            'update products',
            'delete products',
            'view all products', // Manager can view all products (incl. inactive)
            'manage categories', // Manager can manage categories
        ];
        $roleManager->syncPermissions($managerPermissions); // syncPermissions ensures only these are assigned


        // Guest role typically doesn't have specific permissions assigned directly via Spatie
        // Unauthenticated users have no permissions by default.
        // If you use 'guest' role for newly registered users before approval, you might assign basic permissions here.
        // For this app, we assume guest access is handled by allowing public routes (index, show)
        // and checking $product->is_active in the Service/Policy for guests.
        $roleGuest->syncPermissions([]); // Ensure guest role has no permissions


        // --- 4. (Optional) Create Test Users and Assign Roles ---

        // Create an Admin user
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'), // Change to a strong password in production
                'email_verified_at' => now(),
            ]
        );
        $adminUser->assignRole('admin'); // Assign the 'admin' role

        // Create a Manager user
        $managerUser = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Manager User',
                'password' => Hash::make('password'), // Change to a strong password in production
                'email_verified_at' => now(),
            ]
        );
        $managerUser->assignRole('manager'); // Assign the 'manager' role

        // Create a regular User (with guest role if you use it, or no specific role)
        $regularUser = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Regular User',
                'password' => Hash::make('password'), // Change to a strong password in production
                'email_verified_at' => now(),
            ]
        );
        // $regularUser->assignRole('guest'); // Assign the 'guest' role if you created it and want to assign it
        // By default, a user without roles/permissions has no special privileges, acting like a guest for most purposes.


        $this->command->info('Roles and permissions seeded successfully!');
        $this->command->info('Test Users:');
        $this->command->info('- Admin: admin@example.com / password');
        $this->command->info('- Manager: manager@example.com / password');
        $this->command->info('- Regular User: user@example.com / password');
    }
}
