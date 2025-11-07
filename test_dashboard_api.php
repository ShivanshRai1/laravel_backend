<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Watchlist;
use App\Models\Company;
use App\Models\UploadedFinancialData;

$userId = 1;

// Get watchlist
$watchlist = Watchlist::where('user_id', $userId)->get();
echo "Watchlist items: " . $watchlist->count() . "\n\n";

foreach ($watchlist as $item) {
    echo "Watchlist item:\n";
    echo "  Company ID (from watchlist): {$item->company_id}\n";
    
    // Try to find company
    $company = Company::where('symbol', $item->company_id)
        ->orWhere('id', $item->company_id)
        ->first();
    
    if ($company) {
        echo "  Found Company:\n";
        echo "    ID: {$company->id}\n";
        echo "    Name: {$company->name}\n";
        echo "    Symbol: {$company->symbol}\n";
        
        // Get financial data
        $financialData = UploadedFinancialData::where('company_id', $company->id)
            ->where(function($query) {
                $query->where('Metrics', 'like', '%Revenue%')
                      ->orWhere('Metrics', 'like', '%Sales%');
            })
            ->first();
        
        if ($financialData) {
            echo "  Financial Data Found:\n";
            echo "    Metric: {$financialData->Metrics}\n";
            
            $quarters = ['CY_2025_Q1', 'CY_2024_Q4', 'CY_2024_Q3', 'CY_2024_Q2'];
            foreach ($quarters as $q) {
                if (isset($financialData->$q) && $financialData->$q) {
                    echo "    {$q}: {$financialData->$q}\n";
                }
            }
        } else {
            echo "  NO FINANCIAL DATA FOUND\n";
        }
    } else {
        echo "  COMPANY NOT FOUND\n";
    }
    echo "\n";
}
