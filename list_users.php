<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\WeeklyDigestSubscription;

echo "=== All Users ===" . PHP_EOL;
foreach (User::all() as $user) {
    echo "ID: {$user->id} | Email: {$user->email} | Name: {$user->name}" . PHP_EOL;
    
    $sub = WeeklyDigestSubscription::where('user_id', $user->id)->first();
    if ($sub) {
        echo "  └─ Weekly Digest: " . ($sub->enabled ? "✓ Enabled" : "✗ Disabled") . " ({$sub->day_of_week} at {$sub->preferred_time})" . PHP_EOL;
    } else {
        echo "  └─ No weekly digest subscription" . PHP_EOL;
    }
}

echo PHP_EOL . "To test email, make sure your personal email has a weekly digest subscription enabled." . PHP_EOL;
