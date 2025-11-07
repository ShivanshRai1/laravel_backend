<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$columns = DB::select('DESCRIBE watchlists');
echo "Watchlists table columns:\n";
foreach($columns as $col) {
    echo "  - " . $col->Field . "\n";
}
