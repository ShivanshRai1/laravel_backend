<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\WeeklyDigestSubscription;

// Get user with your email
$user = User::where('email', 'raishivansh123@gmail.com')->first();

if (!$user) {
    echo "❌ User not found!" . PHP_EOL;
    exit(1);
}

echo "Found user: {$user->name} ({$user->email})" . PHP_EOL;

// Check if subscription exists
$subscription = WeeklyDigestSubscription::where('user_id', $user->id)->first();

if (!$subscription) {
    $subscription = WeeklyDigestSubscription::create([
        'user_id' => $user->id,
        'day_of_week' => 'monday',
        'preferred_time' => '09:00:00',
        'enabled' => true,
    ]);
    echo "✓ Created weekly digest subscription" . PHP_EOL;
} else {
    $subscription->enabled = true;
    $subscription->save();
    echo "✓ Enabled existing subscription" . PHP_EOL;
}

echo PHP_EOL . "✅ Ready to test!" . PHP_EOL;
echo "Run: php artisan digest:send-weekly" . PHP_EOL;
echo "Check your inbox: {$user->email}" . PHP_EOL;
