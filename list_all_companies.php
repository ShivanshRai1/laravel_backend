<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== All Companies in Database ===\n\n";

$companies = \App\Models\Company::orderBy('name')->get();

echo "Total companies: " . $companies->count() . "\n\n";

foreach ($companies as $company) {
    echo "ID: {$company->id}\n";
    echo "  Name: {$company->name}\n";
    echo "  Symbol: " . ($company->symbol ?? 'N/A') . "\n";
    echo "  Sector: " . ($company->sector ?? 'N/A') . "\n";
    echo "  Market Cap: " . ($company->market_cap ?? 'N/A') . "\n";
    echo "  ---\n";
}
