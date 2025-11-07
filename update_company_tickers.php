<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Updating Company Ticker and Market Cap Data ===\n\n";

$updates = [
    'NXP Semiconductors' => ['ticker' => 'NXPI', 'market_cap' => 31.28, 'sector' => 'Semiconductors'],
    'Onsemi' => ['ticker' => 'ON', 'market_cap' => 31.20, 'sector' => 'Semiconductors'],
    'Texas Instruments' => ['ticker' => 'TXN', 'market_cap' => 168.28, 'sector' => 'Semiconductors'],
    'Analog Devices' => ['ticker' => 'ADI', 'market_cap' => 108.28, 'sector' => 'Semiconductors'],
    'Vishay Intertechnology' => ['ticker' => 'VSH', 'market_cap' => 2.90, 'sector' => 'Semiconductors'],
    'Infineon Technologies' => ['ticker' => 'IFX', 'market_cap' => 42.88, 'sector' => 'Semiconductors'],
    'Rohm Semiconductor' => ['ticker' => '6963.T', 'market_cap' => 8.18, 'sector' => 'Semiconductors'],
    'Apple Inc' => ['ticker' => 'AAPL', 'market_cap' => 3500.00, 'sector' => 'Technology'],
    'Microsoft Corporation' => ['ticker' => 'MSFT', 'market_cap' => 2800.00, 'sector' => 'Technology'],
    'Amazon.com Inc' => ['ticker' => 'AMZN', 'market_cap' => 1700.00, 'sector' => 'E-commerce'],
];

foreach ($updates as $companyName => $data) {
    $company = \App\Models\Company::where('name', 'like', "%{$companyName}%")->first();
    
    if ($company) {
        $company->update([
            'symbol' => $data['ticker'],
            'market_cap' => $data['market_cap'],
            'sector' => $data['sector']
        ]);
        echo "✓ Updated: {$companyName} - Ticker: {$data['ticker']}, Market Cap: \${data['market_cap']}B\n";
    } else {
        echo "✗ Not found: {$companyName}\n";
    }
}

echo "\n=== Update Complete ===\n";
