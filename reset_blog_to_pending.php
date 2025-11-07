<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Reset the blog post to pending status
$updated = DB::table('blog_posts')
    ->where('slug', 'understanding-semiconductor-market-trends-2025')
    ->update([
        'status' => 'pending',
        'rejection_reason' => null,
        'admin_notes' => null,
        'reviewed_by' => null,
        'reviewed_at' => null,
        'published_at' => null,
        'updated_at' => now()
    ]);

if ($updated) {
    echo "✅ Blog post has been reset to PENDING status!\n";
    echo "You can now test the approval feature again.\n";
} else {
    echo "❌ Blog post not found or already pending.\n";
}
