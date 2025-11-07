<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

try {
    // Create Simple User Account
    $user = User::firstOrCreate([
        'email' => 'user@demo.com',
    ], [
        'name' => 'Demo User',
        'password' => Hash::make('password'),
        'role' => 'Registered',
    ]);

    echo "✅ User account created/exists!\n";
    echo "\n📝 Login with:\n";
    echo "   Email: user@demo.com\n";
    echo "   Password: password\n";
    echo "   Role: Regular User\n";
    echo "\n🎉 Ready to test!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}