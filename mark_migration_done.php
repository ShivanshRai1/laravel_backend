<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

DB::table('migrations')->insert([
    'migration' => '2025_10_09_150047_add_uuid_and_company_info_to_watchlists_table',
    'batch' => DB::table('migrations')->max('batch') + 1
]);

echo "Migration marked as run successfully\n";
