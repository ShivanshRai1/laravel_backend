<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$batch = DB::table('migrations')->max('batch') + 1;

$migrations = [
    '2025_10_09_172339_create_saved_comparisons_table',
    '2025_10_14_000002_fix_saved_comparisons_table'
];

foreach ($migrations as $migration) {
    $exists = DB::table('migrations')->where('migration', $migration)->exists();
    if (!$exists) {
        DB::table('migrations')->insert([
            'migration' => $migration,
            'batch' => $batch
        ]);
        echo "Marked: $migration\n";
    }
}

echo "Done!\n";
