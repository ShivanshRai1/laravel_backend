<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Testing Meta Description Implementation ===\n\n";

// Check if column exists
echo "1. Database Column Check:\n";
echo "   meta_description column exists: " . (\Illuminate\Support\Facades\Schema::hasColumn('blog_posts', 'meta_description') ? "✓ YES" : "✗ NO") . "\n\n";

// Check if it's in the fillable array
echo "2. Model Fillable Check:\n";
$model = new \App\Models\BlogPost();
$fillable = $model->getFillable();
echo "   meta_description in fillable: " . (in_array('meta_description', $fillable) ? "✓ YES" : "✗ NO") . "\n\n";

// Check existing blog posts
echo "3. Existing Blog Posts:\n";
$posts = \App\Models\BlogPost::all();
foreach ($posts as $post) {
    echo "   - {$post->title}\n";
    echo "     Meta Description: " . ($post->meta_description ?? '[Not set - NULL]') . "\n\n";
}

echo "=== Test Complete ===\n";
echo "\nTo test in the UI:\n";
echo "1. Refresh your browser (Ctrl+F5)\n";
echo "2. Go to Blog/Knowledge Hub\n";
echo "3. Click 'CREATE ARTICLE' button\n";
echo "4. You should see the new 'Meta Description (SEO)' field\n";
echo "5. It will show character count: 0/160 characters\n";
