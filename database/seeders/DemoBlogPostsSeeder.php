<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BlogPost;
use App\Models\User;
use Carbon\Carbon;

class DemoBlogPostsSeeder extends Seeder
{
    public function run()
    {
        $admin = User::where('role', 'admin')->first();
        $editor = User::where('role', 'editor')->first();
        $now = Carbon::now();

        $posts = [
            [
                'title' => 'Welcome to the Financial Dashboard!',
                'slug' => 'welcome-to-the-financial-dashboard',
                'excerpt' => 'This is your hub for market insights and financial knowledge.',
                'content' => 'This is your hub for market insights and financial knowledge.',
                'status' => 'published',
                'user_id' => $admin ? $admin->id : 1,
                'created_at' => $now,
                'updated_at' => $now,
                'published_at' => $now,
            ],
            [
                'title' => 'How to Compare Companies Effectively',
                'slug' => 'how-to-compare-companies-effectively',
                'excerpt' => 'Learn the best practices for comparing financial metrics.',
                'content' => 'Learn the best practices for comparing financial metrics.',
                'status' => 'draft',
                'user_id' => $editor ? $editor->id : 2,
                'created_at' => $now->copy()->subDay(),
                'updated_at' => $now->copy()->subDay(),
                'published_at' => null,
            ],
            [
                'title' => 'Market Trends for October 2025',
                'slug' => 'market-trends-for-october-2025',
                'excerpt' => 'A quick overview of the latest market trends.',
                'content' => 'A quick overview of the latest market trends.',
                'status' => 'published',
                'user_id' => $admin ? $admin->id : 1,
                'created_at' => $now->copy()->subDays(2),
                'updated_at' => $now->copy()->subDays(2),
                'published_at' => $now->copy()->subDays(2),
            ],
        ];

        foreach ($posts as $post) {
            BlogPost::create($post);
        }
    }
}
