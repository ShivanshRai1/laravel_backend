<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Checking Notifications ===\n\n";

// Get all users
$users = \App\Models\User::all();

foreach ($users as $user) {
    echo "User: {$user->name} (ID: {$user->id}, Email: {$user->email})\n";
    echo "Role: {$user->role}\n";
    
    $notificationCount = $user->notifications()->count();
    $unreadCount = $user->unreadNotifications()->count();
    
    echo "Total Notifications: {$notificationCount}\n";
    echo "Unread Notifications: {$unreadCount}\n";
    
    if ($notificationCount > 0) {
        echo "\nRecent Notifications:\n";
        $recentNotifications = $user->notifications()->latest()->take(3)->get();
        
        foreach ($recentNotifications as $notification) {
            $data = is_string($notification->data) ? json_decode($notification->data, true) : $notification->data;
            echo "  - Type: {$notification->type}\n";
            echo "    Created: {$notification->created_at}\n";
            echo "    Read: " . ($notification->read_at ? $notification->read_at : 'Unread') . "\n";
            echo "    Data: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
            echo "\n";
        }
    }
    
    echo str_repeat("-", 80) . "\n\n";
}

echo "\n=== Blog Posts with Authors ===\n\n";
$posts = \App\Models\BlogPost::with('author')->get();

foreach ($posts as $post) {
    echo "Post: {$post->title}\n";
    echo "Status: {$post->status}\n";
    echo "Author: " . ($post->author ? $post->author->name . " (ID: {$post->author->id})" : 'No author') . "\n";
    echo "Created: {$post->created_at}\n";
    if ($post->rejection_reason) {
        echo "Rejection Reason: {$post->rejection_reason}\n";
    }
    if ($post->admin_notes) {
        echo "Admin Notes: {$post->admin_notes}\n";
    }
    echo str_repeat("-", 80) . "\n";
}
