<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Mail\WeeklyDigestMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class SendWeeklyDigests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'digest:send-weekly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send weekly digest emails to subscribed users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to send weekly digest emails...');

        // Get current day of week (lowercase)
        $currentDay = strtolower(now()->format('l'));
        
        // Get users who have weekly digest enabled for today
        $users = User::whereHas('weeklyDigestSubscription', function($query) use ($currentDay) {
            $query->where('enabled', true)
                  ->where('day_of_week', $currentDay);
        })->get();

        $sentCount = 0;
        
        foreach ($users as $user) {
            try {
                // Get user's watchlist data
                $watchlistData = $this->getWatchlistData($user);
                
                // Get recent blog posts
                $recentPosts = $this->getRecentBlogPosts();
                
                // Send digest email
                Mail::to($user->email)->send(new WeeklyDigestMail($user, $watchlistData, $recentPosts));
                
                $sentCount++;
                $this->info("Sent digest to: {$user->email}");
            } catch (\Exception $e) {
                $this->error("Failed to send digest to {$user->email}: " . $e->getMessage());
            }
        }

        $this->info("Weekly digest emails sent successfully to {$sentCount} users.");
        
        return Command::SUCCESS;
    }

    /**
     * Get watchlist data for user
     */
    private function getWatchlistData($user)
    {
        // Get user's watchlist with company financial data
        return $user->watchlist()->with('company')->get()->map(function($item) {
            return [
                'company_name' => $item->company->name ?? 'Unknown',
                'ticker' => $item->company->ticker ?? 'N/A',
                'notes' => $item->notes,
            ];
        });
    }

    /**
     * Get recent blog posts
     */
    private function getRecentBlogPosts()
    {
        return DB::table('blog_posts')
            ->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['title', 'slug', 'meta_description', 'created_at']);
    }
}
