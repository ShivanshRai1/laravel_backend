<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

try {
    // Create Demo User
    $demoUser = User::firstOrCreate([
        'email' => 'demo@example.com',
    ], [
        'name' => 'Demo User',
        'password' => Hash::make('password'),
        'role' => 'Registered',
    ]);
    echo "✅ Demo User created/exists: demo@example.com / password\n";

    // Create Admin User
    $adminUser = User::firstOrCreate([
        'email' => 'admin@test.com',
    ], [
        'name' => 'Admin User', 
        'password' => Hash::make('password123'),
        'role' => 'Admin',
    ]);
    echo "✅ Admin User created/exists: admin@test.com / password123\n";

    // Create Regular User
    $regularUser = User::firstOrCreate([
        'email' => 'user@test.com',
    ], [
        'name' => 'Test User',
        'password' => Hash::make('password'),
        'role' => 'Registered',
    ]);
    echo "✅ Regular User created/exists: user@test.com / password\n";

    echo "\n🎉 All test users created successfully!\n";
    echo "\n📝 You can now login with:\n";
    echo "   Demo: demo@example.com / password\n";
    echo "   Admin: admin@test.com / password123\n";
    echo "   User: user@test.com / password\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}