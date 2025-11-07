<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing User Dashboard Feature ===\n\n";

// Get a test user
$user = \App\Models\User::where('role', 'user')->first();

if (!$user) {
    echo "No user found. Creating test user...\n";
    $user = \App\Models\User::create([
        'name' => 'Test User',
        'email' => 'testuser@dashboard.com',
        'password' => bcrypt('password'),
        'role' => 'user'
    ]);
}

echo "Testing with user: {$user->name} (ID: {$user->id})\n\n";

// Check watchlist
$watchlist = \App\Models\Watchlist::where('user_id', $user->id)->get();
echo "Watchlist companies: {$watchlist->count()}\n";

if ($watchlist->count() === 0) {
    echo "\nAdding test companies to watchlist...\n";
    
    // Get some companies from the database
    $companies = \App\Models\Company::limit(3)->get();
    
    foreach ($companies as $company) {
        \App\Models\Watchlist::create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'company_name' => $company->name,
            'company_ticker' => $company->ticker ?? $company->id
        ]);
        echo "  Added: {$company->name}\n";
    }
    
    $watchlist = \App\Models\Watchlist::where('user_id', $user->id)->get();
}

echo "\n=== Simulating API Call ===\n\n";

// Simulate the controller logic
$watchlistData = [];

foreach ($watchlist as $item) {
    // Get company to find the numeric ID
    $company = \App\Models\Company::where('symbol', $item->company_id)
        ->orWhere('id', $item->company_id)
        ->first();
    
    if (!$company) {
        continue; // Skip if company not found
    }
    
    $companyData = [
        'id' => $company->id,
        'name' => $item->company_name ?? $company->name,
        'ticker' => $company->symbol,
        'revenue_growth' => null,
        'latest_revenue' => null,
        'previous_revenue' => null,
        'latest_quarter' => null,
        'previous_quarter' => null,
        'trend' => 'neutral'
    ];

    // Get financial data using the numeric company ID
    $financialData = \App\Models\UploadedFinancialData::where('company_id', $company->id)
        ->where(function($query) {
            $query->where('Metrics', 'like', '%Revenue%')
                  ->orWhere('Metrics', 'like', '%Sales%');
        })
        ->first();

    if ($financialData) {
        $quarters = ['CY_2025_Q1', 'CY_2024_Q4', 'CY_2024_Q3', 'CY_2024_Q2', 'CY_2024_Q1', 'CY_2023_Q4'];
        $quarterValues = [];
        
        foreach ($quarters as $quarter) {
            if (isset($financialData->$quarter) && $financialData->$quarter !== null && $financialData->$quarter !== '') {
                $quarterValues[$quarter] = floatval($financialData->$quarter);
            }
        }

        if (count($quarterValues) >= 2) {
            $quarterKeys = array_keys($quarterValues);
            $latestQuarter = $quarterKeys[0];
            $previousQuarter = $quarterKeys[1];
            
            $latestRevenue = $quarterValues[$latestQuarter];
            $previousRevenue = $quarterValues[$previousQuarter];

            if ($previousRevenue != 0) {
                $revenueGrowth = (($latestRevenue - $previousRevenue) / abs($previousRevenue)) * 100;
                
                $companyData['revenue_growth'] = round($revenueGrowth, 2);
                $companyData['latest_revenue'] = $latestRevenue;
                $companyData['previous_revenue'] = $previousRevenue;
                $companyData['latest_quarter'] = str_replace('CY_', '', $latestQuarter);
                $companyData['previous_quarter'] = str_replace('CY_', '', $previousQuarter);
                $companyData['trend'] = $revenueGrowth > 0 ? 'up' : ($revenueGrowth < 0 ? 'down' : 'neutral');
            }
        }
    }

    $watchlistData[] = $companyData;
}

echo "Dashboard Data:\n";
echo json_encode([
    'user' => [
        'name' => $user->name,
        'email' => $user->email
    ],
    'watchlist_count' => count($watchlistData),
    'watchlist_companies' => $watchlistData,
    'statistics' => [
        'total_watchlist' => count($watchlistData),
        'companies_with_growth' => count(array_filter($watchlistData, fn($c) => $c['trend'] === 'up')),
        'companies_with_decline' => count(array_filter($watchlistData, fn($c) => $c['trend'] === 'down')),
    ]
], JSON_PRETTY_PRINT);

echo "\n\n=== Test Complete ===\n";
