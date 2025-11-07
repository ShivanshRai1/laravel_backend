<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Database Tables Check ===\n\n";

$tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
echo "Available tables:\n";
foreach ($tables as $table) {
    $tableName = array_values((array)$table)[0];
    if (strpos($tableName, 'financial') !== false || strpos($tableName, 'company') !== false || strpos($tableName, 'watchlist') !== false) {
        echo "  - $tableName\n";
    }
}

echo "\n=== Checking financial data structure ===\n\n";

// Check uploaded_financial_data
if (\Illuminate\Support\Facades\Schema::hasTable('uploaded_financial_data')) {
    echo "uploaded_financial_data columns:\n";
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('uploaded_financial_data');
    foreach ($columns as $col) {
        echo "  - $col\n";
    }
    $count = \DB::table('uploaded_financial_data')->count();
    echo "  Total rows: $count\n\n";
}

// Check financial_ratios
if (\Illuminate\Support\Facades\Schema::hasTable('financial_ratios')) {
    echo "financial_ratios columns:\n";
    $columns = \Illuminate\Support\Facades\Schema::getColumnListing('financial_ratios');
    foreach ($columns as $col) {
        echo "  - $col\n";
    }
    $count = \DB::table('financial_ratios')->count();
    echo "  Total rows: $count\n\n";
}

// Check financial_metrics
if (\Illuminate\Support\Facades\Schema::hasTable('financial_metrics')) {
    echo "financial_metrics (Revenue metric):\n";
    $revenueMetric = \DB::table('financial_metrics')
        ->where('name', 'like', '%revenue%')
        ->orWhere('name', 'like', '%sales%')
        ->first();
    if ($revenueMetric) {
        echo "  Found: {$revenueMetric->name} (ID: {$revenueMetric->id})\n\n";
    }
}

echo "=== Sample Company Financial Data ===\n\n";
$sample = \DB::table('uploaded_financial_data')
    ->select('company_id', 'quarter', 'revenue', 'net_income')
    ->limit(3)
    ->get();
foreach ($sample as $row) {
    echo "Company: {$row->company_id}, Quarter: {$row->quarter}, Revenue: {$row->revenue}, Net Income: {$row->net_income}\n";
}
