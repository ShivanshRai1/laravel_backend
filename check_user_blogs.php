<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Blog Posts for Test User (ID 1) ===\n\n";

$posts = \App\Models\BlogPost::where('user_id', 1)->get();

echo "Total posts: " . $posts->count() . "\n\n";

foreach ($posts as $post) {
    echo "ID: {$post->id}\n";
    echo "Title: {$post->title}\n";
    echo "Status: {$post->status}\n";
    echo "Rejection Reason: " . ($post->rejection_reason ?? 'None') . "\n";
    echo "Created: {$post->created_at}\n";
    echo str_repeat("-", 80) . "\n";
}

echo "\n=== Testing getArticles logic ===\n\n";

// Simulate what the API does for a regular user
$userId = 1;
$user = \App\Models\User::find($userId);

echo "User: {$user->name} (Role: {$user->role})\n\n";

// This is what the ContentController does
$query = \App\Models\BlogPost::with('author');

if (!in_array(strtolower($user->role), ['admin', 'editor'])) {
    echo "Applying regular user filter...\n";
    $query->where(function($q) use ($user) {
        $q->where('status', 'published')
          ->orWhere('user_id', $user->id);
    });
}

$articles = $query->orderBy('created_at', 'desc')->get();

echo "Total articles returned: " . $articles->count() . "\n\n";

foreach ($articles as $article) {
    echo "- {$article->title} (Status: {$article->status}, Author: {$article->author->name})\n";
}
