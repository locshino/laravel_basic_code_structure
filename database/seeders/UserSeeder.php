<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; // Import User model
use Illuminate\Support\Facades\Hash; // To hash passwords

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // The RoleAndPermissionSeeder already creates admin, manager, and a regular user.
        // This seeder can add more regular users if needed.

        $users = [
            [
                'name' => 'Another User',
                'email' => 'another.user@example.com',
                'password' => Hash::make('password'), // Change to a strong password
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Third User',
                'email' => 'third.user@example.com',
                'password' => Hash::make('password'), // Change to a strong password
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            // Create user if they don't exist by email
            User::firstOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        $this->command->info('Additional users seeded successfully!');
    }
}
