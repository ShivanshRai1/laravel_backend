<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$watchlist = \DB::table('watchlists')->where('user_id', 1)->get();

echo "Watchlist entries:\n";
foreach ($watchlist as $item) {
    echo "  company_id: {$item->company_id} ({$item->company_name})\n";
}

echo "\nFinancial data for these companies:\n";
foreach ($watchlist as $item) {
    $data = \DB::table('uploaded_financial_data')
        ->where('company_id', $item->company_id)
        ->where('Metrics', 'like', '%Revenue%')
        ->first();
    
    if ($data) {
        echo "  ✓ {$item->company_name}: Has revenue data\n";
    } else {
        echo "  ✗ {$item->company_name}: No revenue data found for company_id '{$item->company_id}'\n";
    }
}

echo "\nChecking if company_id matches:\n";
$companies = \DB::table('companies')->whereIn('id', ['ON', 'TI'])->get();
foreach ($companies as $comp) {
    echo "  Company ID: {$comp->id}, Name: {$comp->name}, Symbol: {$comp->symbol}\n";
}

echo "\nFinancial data company_ids:\n";
$finCompanies = \DB::table('uploaded_financial_data')->select('company_id')->distinct()->get();
foreach ($finCompanies as $fc) {
    echo "  - {$fc->company_id}\n";
}
