<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Fixing Watchlist company_ids ===\n\n";

$watchlist = \DB::table('watchlists')->get();

foreach ($watchlist as $item) {
    $company = null;
    
    // Try to find by symbol first
    if ($item->company_id) {
        $company = \DB::table('companies')
            ->where('symbol', $item->company_id)
            ->orWhere('id', $item->company_id)
            ->first();
    }
    
    // Try by name if not found
    if (!$company && $item->company_name) {
        $company = \DB::table('companies')
            ->where('name', 'like', '%' . $item->company_name . '%')
            ->first();
    }
    
    if ($company) {
        \DB::table('watchlists')
            ->where('id', $item->id)
            ->update([
                'company_id' => $company->id,
                'company_name' => $company->name
            ]);
        
        echo "✓ Updated watchlist ID {$item->id}: '{$item->company_id}' → {$company->id} ({$company->name})\n";
    } else {
        echo "✗ Could not find company for watchlist ID {$item->id}: '{$item->company_name}'\n";
    }
}

echo "\n=== Verification ===\n";
$watchlist = \DB::table('watchlists')->get();
foreach ($watchlist as $item) {
    echo "Watchlist: company_id={$item->company_id}, name={$item->company_name}\n";
}

echo "\n=== Complete ===\n";
