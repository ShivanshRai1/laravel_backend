<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\EmailAlertPreference;
use App\Models\WeeklyDigestSubscription;

echo "=== Testing Notification Features ===\n\n";

// Test 1: Create email alert preference
echo "1. Creating email alert preference...\n";
$user = User::first();
if ($user) {
    $alert = EmailAlertPreference::create([
        'user_id' => $user->id,
        'company_id' => 'ON', // Onsemi
        'alert_type' => 'all',
        'threshold' => 5.0,
        'watched_ratios' => ['revenue', 'profit_margin'],
        'enabled' => true
    ]);
    echo "✓ Created alert ID: {$alert->id}\n";
    echo "  - User: {$user->email}\n";
    echo "  - Company: {$alert->company_id}\n";
    echo "  - Type: {$alert->alert_type}\n";
    echo "  - Threshold: {$alert->threshold}%\n\n";
} else {
    echo "✗ No users found\n\n";
}

// Test 2: Create weekly digest subscription
echo "2. Creating weekly digest subscription...\n";
if ($user) {
    $digest = WeeklyDigestSubscription::updateOrCreate(
        ['user_id' => $user->id],
        [
            'day_of_week' => 'monday',
            'preferred_time' => '09:00:00',
            'enabled' => true
        ]
    );
    echo "✓ Created/Updated digest subscription\n";
    echo "  - User: {$user->email}\n";
    echo "  - Day: {$digest->day_of_week}\n";
    echo "  - Time: {$digest->preferred_time}\n";
    echo "  - Enabled: " . ($digest->enabled ? 'Yes' : 'No') . "\n\n";
}

// Test 3: List all email alerts
echo "3. Listing all email alert preferences...\n";
$alerts = EmailAlertPreference::with('user')->get();
echo "✓ Found {$alerts->count()} alert(s)\n";
foreach ($alerts as $alert) {
    echo "  - Alert #{$alert->id}: {$alert->user->email} watching {$alert->company_id} ({$alert->alert_type})\n";
}
echo "\n";

// Test 4: List all weekly digest subscriptions
echo "4. Listing all weekly digest subscriptions...\n";
$digests = WeeklyDigestSubscription::with('user')->where('enabled', true)->get();
echo "✓ Found {$digests->count()} active subscription(s)\n";
foreach ($digests as $digest) {
    echo "  - {$digest->user->email}: {$digest->day_of_week} at {$digest->preferred_time}\n";
}
echo "\n";

// Test 5: Verify database tables exist
echo "5. Verifying database tables...\n";
$tables = ['email_alert_preferences', 'alert_history', 'weekly_digest_subscriptions', 'device_tokens'];
foreach ($tables as $table) {
    try {
        $count = DB::table($table)->count();
        echo "✓ Table '{$table}' exists ({$count} records)\n";
    } catch (\Exception $e) {
        echo "✗ Table '{$table}' error: " . $e->getMessage() . "\n";
    }
}
echo "\n";

echo "=== Testing Complete ===\n";
echo "\nNext Steps:\n";
echo "1. Configure mail settings in .env (MAIL_MAILER, MAIL_HOST, etc.)\n";
echo "2. Test email sending: php artisan tinker\n";
echo "3. Run scheduler: php artisan schedule:work\n";
echo "4. Run weekly digest: php artisan digest:send-weekly\n";
echo "5. Check frontend: Go to Settings > Email & Digest tab\n";
