<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Checking Financial Data ===\n\n";

// Check what companies have financial data
$financialData = \DB::table('uploaded_financial_data')
    ->select('company_id', 'Metrics', 'Company', 'CY_2025_Q1', 'CY_2024_Q4', 'CY_2024_Q3')
    ->whereNotNull('company_id')
    ->get();

echo "Total financial data rows: " . $financialData->count() . "\n\n";

echo "Companies with data:\n";
$companies = $financialData->pluck('company_id')->unique();
foreach ($companies as $compId) {
    echo "  - Company ID: $compId\n";
    
    $revenueData = \DB::table('uploaded_financial_data')
        ->where('company_id', $compId)
        ->where(function($query) {
            $query->where('Metrics', 'like', '%Revenue%')
                  ->orWhere('Metrics', 'like', '%Sales%');
        })
        ->first();
    
    if ($revenueData) {
        echo "    Metric: {$revenueData->Metrics}\n";
        echo "    Q1 2025: {$revenueData->CY_2025_Q1}\n";
        echo "    Q4 2024: {$revenueData->CY_2024_Q4}\n";
    }
}
