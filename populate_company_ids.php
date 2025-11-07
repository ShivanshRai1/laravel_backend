<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Populating company_id in uploaded_financial_data ===\n\n";

// Get all rows without company_id
$rows = \DB::table('uploaded_financial_data')
    ->whereNull('company_id')
    ->orWhere('company_id', '')
    ->get();

echo "Found {$rows->count()} rows without company_id\n\n";

$updated = 0;
$notFound = 0;
$companiesNotFound = [];

foreach ($rows as $row) {
    if (empty($row->Company)) {
        echo "⚠ Row {$row->id}: No Company name - skipping\n";
        continue;
    }

    // Try to find the company by name or symbol
    $company = \DB::table('companies')
        ->where('name', 'like', '%' . $row->Company . '%')
        ->orWhere('symbol', $row->Company)
        ->orWhere('id', $row->Company)
        ->first();

    if ($company) {
        \DB::table('uploaded_financial_data')
            ->where('id', $row->id)
            ->update(['company_id' => $company->id]);
        
        echo "✓ Row {$row->id}: '{$row->Company}' → company_id: {$company->id} ({$company->name})\n";
        $updated++;
    } else {
        echo "✗ Row {$row->id}: Company '{$row->Company}' not found in companies table\n";
        $notFound++;
        $companiesNotFound[] = $row->Company;
    }
}

echo "\n=== Summary ===\n";
echo "Updated: $updated rows\n";
echo "Not found: $notFound rows\n";

if (count($companiesNotFound) > 0) {
    echo "\nCompanies not found in database:\n";
    $unique = array_unique($companiesNotFound);
    foreach ($unique as $compName) {
        echo "  - $compName\n";
    }
    
    echo "\nThese companies need to be added to the 'companies' table first.\n";
}

echo "\n=== Verifying Results ===\n";
$withCompanyId = \DB::table('uploaded_financial_data')->whereNotNull('company_id')->where('company_id', '!=', '')->count();
$total = \DB::table('uploaded_financial_data')->count();
echo "Rows with company_id: $withCompanyId / $total\n";

echo "\n=== Complete ===\n";
