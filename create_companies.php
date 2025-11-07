<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Company;

try {
    // Create sample companies
    $companies = [
        [
            'name' => 'Apple Inc',
            'symbol' => 'AAPL',
            'industry' => 'Technology',
            'sector' => 'Technology',
            'description' => 'Consumer electronics and software company'
        ],
        [
            'name' => 'Microsoft Corporation', 
            'symbol' => 'MSFT',
            'industry' => 'Technology',
            'sector' => 'Technology',
            'description' => 'Software and cloud services company'
        ],
        [
            'name' => 'Amazon.com Inc',
            'symbol' => 'AMZN', 
            'industry' => 'E-commerce',
            'sector' => 'Consumer Discretionary',
            'description' => 'E-commerce and cloud computing company'
        ]
    ];

    foreach ($companies as $companyData) {
        Company::firstOrCreate(
            ['symbol' => $companyData['symbol']], 
            $companyData
        );
        echo "✅ Company created: " . $companyData['name'] . " (" . $companyData['symbol'] . ")\n";
    }

    echo "\n🎉 Sample companies created successfully!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}