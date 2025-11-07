<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get pending migrations
$files = scandir(__DIR__ . '/database/migrations');
$batch = DB::table('migrations')->max('batch') + 1;

$pendingMigrations = [
    '2025_10_08_133923_create_newsletters_table',
    '2025_10_08_134000_create_subscribers_table',
    '2025_10_09_172006_create_saved_comparisons_table'
];

foreach ($pendingMigrations as $migration) {
    // Check if already in migrations table
    $exists = DB::table('migrations')->where('migration', $migration)->exists();
    if (!$exists) {
        DB::table('migrations')->insert([
            'migration' => $migration,
            'batch' => $batch
        ]);
        echo "Marked as run: $migration\n";
    } else {
        echo "Already exists: $migration\n";
    }
}

echo "\nDone!\n";
