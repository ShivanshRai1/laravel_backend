<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\WeeklyDigestSubscription;

echo "=== Testing Email Configuration ===" . PHP_EOL . PHP_EOL;

// Get first user
$user = User::first();
if (!$user) {
    echo "❌ No users found in database. Please create a user first." . PHP_EOL;
    exit(1);
}

echo "✓ Found user: {$user->email} (ID: {$user->id})" . PHP_EOL;

// Check or create weekly digest subscription
$subscription = WeeklyDigestSubscription::where('user_id', $user->id)->first();

if (!$subscription) {
    echo "Creating weekly digest subscription for testing..." . PHP_EOL;
    $subscription = WeeklyDigestSubscription::create([
        'user_id' => $user->id,
        'day_of_week' => 'monday',
        'preferred_time' => '09:00:00',
        'enabled' => true,
    ]);
    echo "✓ Created subscription (ID: {$subscription->id})" . PHP_EOL;
} else {
    echo "✓ Existing subscription found (ID: {$subscription->id})" . PHP_EOL;
    if (!$subscription->enabled) {
        $subscription->enabled = true;
        $subscription->save();
        echo "✓ Enabled subscription" . PHP_EOL;
    }
}

echo PHP_EOL . "Now run: php artisan digest:send-weekly" . PHP_EOL;
echo "Check your email: {$user->email}" . PHP_EOL;
