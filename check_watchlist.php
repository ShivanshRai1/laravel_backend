<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$userId = 1;
$watchlist = DB::table('watchlists')->where('user_id', $userId)->get();

echo "Watchlist Count: " . count($watchlist) . PHP_EOL;
foreach($watchlist as $item) {
    echo "Company ID: " . $item->company_id . PHP_EOL;
}

// Check companies table
$companies = DB::table('companies')->whereIn('symbol', ['ON', 'TI'])->get();
echo "\nCompanies:\n";
foreach($companies as $company) {
    echo "ID: {$company->id}, Symbol: {$company->symbol}, Name: {$company->name}\n";
}
