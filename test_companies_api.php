<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Companies API Endpoint ===\n\n";

// Simulate the API call
$controller = new \App\Http\Controllers\CompanyController();
$response = $controller->index();

$data = json_decode($response->getContent(), true);

if ($data['success']) {
    echo "✓ API Response Success!\n\n";
    echo "Total Companies: " . count($data['data']) . "\n\n";
    
    foreach ($data['data'] as $company) {
        echo "- {$company['name']} ({$company['symbol']}) - {$company['sector']} - \${$company['market_cap']}B\n";
    }
} else {
    echo "✗ API Error: " . ($data['error'] ?? 'Unknown error') . "\n";
}
