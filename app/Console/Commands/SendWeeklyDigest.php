<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WeeklyDigestSubscription;
use App\Models\UploadedFinancialData;
use App\Models\BlogPost;
use App\Models\Watchlist;
use App\Mail\WeeklyDigestMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendWeeklyDigest extends Command
{
    protected $signature = 'digest:send-weekly';
    protected $description = 'Send weekly digest emails to subscribed users';

    public function handle()
    {
        $this->info('Starting weekly digest send...');
        
        $currentDay = strtolower(now()->format('l')); // monday, tuesday, etc.
        
        // Get subscriptions for today that are enabled
        $subscriptions = WeeklyDigestSubscription::where('enabled', true)
            ->where('day_of_week', $currentDay)
            ->with('user')
            ->get();

        $sentCount = 0;
        $failedCount = 0;

        foreach ($subscriptions as $subscription) {
            try {
                $digestData = $this->prepareDigestData($subscription->user);
                
                // Send email
                Mail::to($subscription->user->email)->send(
                    new WeeklyDigestMail($subscription->user, $digestData)
                );

                // Update last sent timestamp
                $subscription->update(['last_sent_at' => now()]);
                
                $sentCount++;
                $this->info("Sent digest to {$subscription->user->email}");
            } catch (\Exception $e) {
                $failedCount++;
                $this->error("Failed to send digest to {$subscription->user->email}: " . $e->getMessage());
                Log::error('Weekly digest failed', [
                    'user_id' => $subscription->user_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info("Weekly digest completed. Sent: {$sentCount}, Failed: {$failedCount}");
        
        return Command::SUCCESS;
    }

    private function prepareDigestData($user): array
    {
        $oneWeekAgo = now()->subWeek();

        // Get user's watchlist companies
        $watchlist = Watchlist::where('user_id', $user->id)->get();
        $companySymbols = $watchlist->pluck('company_id')->toArray();

        // Get financial data updates for watchlist companies
        $recentFinancialUpdates = UploadedFinancialData::whereIn('company_id', function($query) use ($companySymbols) {
                $query->select('id')
                    ->from('companies')
                    ->whereIn('symbol', $companySymbols);
            })
            ->where('created_at', '>=', $oneWeekAgo)
            ->get();

        // Calculate top gainers and losers
        $performanceData = [];
        foreach ($watchlist as $item) {
            $company = \App\Models\Company::where('symbol', $item->company_id)->first();
            if ($company) {
                $financialData = UploadedFinancialData::where('company_id', $company->id)
                    ->where('Metrics', 'like', '%Revenue%')
                    ->first();

                if ($financialData) {
                    $quarters = ['CY_2025_Q1', 'CY_2024_Q4'];
                    $quarterValues = [];
                    
                    foreach ($quarters as $quarter) {
                        if (isset($financialData->$quarter) && $financialData->$quarter) {
                            $quarterValues[$quarter] = floatval($financialData->$quarter);
                        }
                    }

                    if (count($quarterValues) >= 2) {
                        $values = array_values($quarterValues);
                        $growth = (($values[0] - $values[1]) / abs($values[1])) * 100;
                        
                        $performanceData[] = [
                            'company' => $company->name,
                            'symbol' => $company->symbol,
                            'growth' => round($growth, 2)
                        ];
                    }
                }
            }
        }

        // Sort by growth
        usort($performanceData, fn($a, $b) => $b['growth'] <=> $a['growth']);
        
        $topGainers = array_slice($performanceData, 0, 3);
        $topLosers = array_slice(array_reverse($performanceData), 0, 3);

        // Get recent blog posts
        $recentBlogs = BlogPost::where('status', 'published')
            ->where('created_at', '>=', $oneWeekAgo)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return [
            'watchlist_count' => $watchlist->count(),
            'financial_updates_count' => $recentFinancialUpdates->count(),
            'top_gainers' => $topGainers,
            'top_losers' => $topLosers,
            'recent_blogs' => $recentBlogs,
            'week_start' => $oneWeekAgo->format('M d, Y'),
            'week_end' => now()->format('M d, Y')
        ];
    }
}
