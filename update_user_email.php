<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

$user = User::first();

if (!$user) {
    echo "No user found!" . PHP_EOL;
    exit(1);
}

echo "Current email: {$user->email}" . PHP_EOL;
echo "Enter your personal email to test with: ";
$newEmail = trim(fgets(STDIN));

if (filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
    $user->email = $newEmail;
    $user->save();
    echo "✓ Updated user email to: {$newEmail}" . PHP_EOL;
    echo PHP_EOL . "Now run: php artisan digest:send-weekly" . PHP_EOL;
} else {
    echo "❌ Invalid email format" . PHP_EOL;
}
