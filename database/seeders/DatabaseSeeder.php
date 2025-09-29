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
        // First, let's check if users already exist to avoid duplicates
        if (User::where('email', 'admin@financial-dashboard.com')->doesntExist()) {
            // Create admin user with lowercase role
            User::create([
                'name' => 'Admin User',
                'email' => 'admin@financial-dashboard.com',
                'password' => bcrypt('password123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);
        }

        if (User::where('email', 'editor@financial-dashboard.com')->doesntExist()) {
            // Create editor user with lowercase role
            User::create([
                'name' => 'Editor User',
                'email' => 'editor@financial-dashboard.com',
                'password' => bcrypt('password123'),
                'role' => 'editor',
                'email_verified_at' => now(),
            ]);
        }

        if (User::where('email', 'user@financial-dashboard.com')->doesntExist()) {
            // Create regular user with lowercase role
            User::create([
                'name' => 'Regular User',
                'email' => 'user@financial-dashboard.com',
                'password' => bcrypt('password123'),
                'role' => 'user',
                'email_verified_at' => now(),
            ]);
        }
    }
}
