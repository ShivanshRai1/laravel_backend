<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\WeeklyDigestSubscription;
use App\Models\User;
use App\Mail\WeeklyDigestMail;
use Illuminate\Support\Facades\Mail;

echo "=== Sending Test Weekly Digest ===" . PHP_EOL . PHP_EOL;

// Get the subscription for your email
$user = User::where('email', 'raishivansh123@gmail.com')->first();

if (!$user) {
    echo "❌ User not found!" . PHP_EOL;
    exit(1);
}

echo "Sending test digest to: {$user->email}" . PHP_EOL;

// Prepare digest data
$digestData = [
    'week_start' => now()->startOfWeek()->format('M d, Y'),
    'week_end' => now()->endOfWeek()->format('M d, Y'),
    'watchlist_count' => 5,
    'financial_updates_count' => 12,
    'top_gainers' => [
        ['symbol' => 'AAPL', 'company' => 'Apple Inc.', 'growth' => 5.2],
        ['symbol' => 'MSFT', 'company' => 'Microsoft Corp.', 'growth' => 3.8],
    ],
    'top_losers' => [
        ['symbol' => 'TSLA', 'company' => 'Tesla Inc.', 'growth' => -2.1],
    ],
    'recent_blogs' => collect([
        (object)[
            'title' => 'Market Analysis for Q3 2025', 
            'created_at' => now()->subDays(2),
            'meta_description' => 'Comprehensive analysis of market trends and financial performance.',
        ],
    ]),
];

try {
    // Send the email
    Mail::to($user->email)->send(new WeeklyDigestMail($user, $digestData));
    
    echo "✅ Email sent successfully!" . PHP_EOL;
    echo PHP_EOL . "Check your inbox: {$user->email}" . PHP_EOL;
    echo "⚠️ If you don't see it, check your SPAM folder" . PHP_EOL;
    echo "⚠️ Also verify your Gmail app password in .env is correct" . PHP_EOL;
    
} catch (\Exception $e) {
    echo "❌ Failed to send email!" . PHP_EOL;
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo PHP_EOL . "Common issues:" . PHP_EOL;
    echo "1. Check MAIL_USERNAME and MAIL_PASSWORD in .env" . PHP_EOL;
    echo "2. Make sure you're using Gmail App Password (not regular password)" . PHP_EOL;
    echo "3. Enable 2FA and generate App Password from Google Account settings" . PHP_EOL;
}
