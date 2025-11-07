<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    // Remove all existing users and reset auto-increment
    DB::statement('DELETE FROM users');
    DB::statement('ALTER TABLE users AUTO_INCREMENT = 1');
        // Create or update test admin user
        User::create([
            'id' => 1,
            'name' => 'Admin User',
            'email' => 'admin@financial-dashboard.com',
            'password' => Hash::make('password123'),
            'role' => 'Admin',
        ]);

        User::create([
            'id' => 2,
            'name' => 'Editor User',
            'email' => 'editor@financial-dashboard.com',
            'password' => Hash::make('password123'),
            'role' => 'Editor',
        ]);

        User::create([
            'id' => 3,
            'name' => 'Test User',
            'email' => 'user@test.com',
            'password' => Hash::make('password123'),
            'role' => 'User',
        ]);

        User::create([
            'id' => 4,
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'password' => Hash::make('password123'),
            'role' => 'User',
        ]);

        User::create([
            'id' => 5,
            'name' => 'Guest User',
            'email' => 'guest@example.com',
            'password' => Hash::make('password123'),
            'role' => 'Guest',
        ]);

        // Create or update test editor user
        User::updateOrCreate([
            'email' => 'editor@test.com',
        ], [
            'name' => 'Editor User',
            'password' => Hash::make('password123'),
            'role' => strtolower('editor'),
        ]);

        // Create or update test registered user
        User::updateOrCreate([
            'email' => 'user@test.com',
        ], [
            'name' => 'Test User',
            'password' => Hash::make('password123'),
            'role' => strtolower('user'),
        ]);

        // Create or update demo registered user
        User::updateOrCreate([
            'email' => 'demo@example.com',
        ], [
            'name' => 'Demo User',
            'password' => Hash::make('password'),
            'role' => strtolower('user'),
        ]);

        // Create or update guest user (non-registered)
        User::updateOrCreate([
            'email' => 'guest@example.com',
        ], [
            'name' => 'Guest User',
            'password' => Hash::make('guestpassword'),
            'role' => strtolower('guest'),
        ]);
    }
}
