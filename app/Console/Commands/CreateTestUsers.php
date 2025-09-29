<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateTestUsers extends Command
{
    protected $signature = 'users:create-test';
    protected $description = 'Create test users for the application';

    public function handle()
    {
        // Delete existing test users first
        User::whereIn('email', [
            'admin@financial-dashboard.com',
            'editor@financial-dashboard.com', 
            'user@financial-dashboard.com'
        ])->delete();

        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@financial-dashboard.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create editor user
        User::create([
            'name' => 'Editor User',
            'email' => 'editor@financial-dashboard.com',
            'password' => Hash::make('password123'),
            'role' => 'editor',
            'email_verified_at' => now(),
        ]);

        // Create regular user
        User::create([
            'name' => 'Regular User',
            'email' => 'user@financial-dashboard.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        $this->info('Test users created successfully!');
        $this->info('Admin: admin@financial-dashboard.com / password123');
        $this->info('Editor: editor@financial-dashboard.com / password123');
        $this->info('User: user@financial-dashboard.com / password123');
    }
}