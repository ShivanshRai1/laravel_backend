<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\BlogPost;
use App\Models\Watchlist;

class EditorController extends Controller
{
    /**
     * Get dashboard statistics for editors
     */
    public function getDashboardStats(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Editor-specific statistics
            $stats = [
                'articlesEdited' => BlogPost::where('updated_by', $user->id)
                    ->where('updated_at', '>=', now()->subWeek())
                    ->count(),
                'reportsCreated' => BlogPost::where('author_id', $user->id)
                    ->where('created_at', '>=', now()->subMonth())
                    ->count(),
                'watchlistsManaged' => Watchlist::where('updated_by', $user->id)
                    ->where('updated_at', '>=', now()->subWeek())
                    ->count(),
                'pendingReviews' => BlogPost::where('status', 'pending')
                    ->whereNull('reviewed_by')
                    ->count(),
                'totalArticles' => BlogPost::where('author_id', $user->id)->count(),
                'publishedArticles' => BlogPost::where('author_id', $user->id)
                    ->where('status', 'published')
                    ->count(),
                'draftArticles' => BlogPost::where('author_id', $user->id)
                    ->where('status', 'draft')
                    ->count(),
            ];

            // Recent activity
            $recentActivity = [
                'articles' => BlogPost::where('author_id', $user->id)
                    ->orWhere('updated_by', $user->id)
                    ->orderBy('updated_at', 'desc')
                    ->limit(5)
                    ->get(['id', 'title', 'status', 'updated_at']),
                'watchlists' => Watchlist::where('updated_by', $user->id)
                    ->orderBy('updated_at', 'desc')
                    ->limit(3)
                    ->get(['id', 'name', 'updated_at']),
            ];

            // Performance metrics for charts
            $performanceData = [
                'monthly_articles' => $this->getMonthlyArticleStats($user->id),
                'weekly_engagement' => $this->getWeeklyEngagementStats($user->id),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'recent_activity' => $recentActivity,
                    'performance_data' => $performanceData,
                    'user' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get monthly article statistics for the editor
     */
    private function getMonthlyArticleStats(int $userId): array
    {
        $months = [];
        $data = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $months[] = $month->format('M Y');
            
            $count = BlogPost::where('author_id', $userId)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
                
            $data[] = $count;
        }

        return [
            'labels' => $months,
            'data' => $data,
        ];
    }

    /**
     * Get weekly engagement statistics
     */
    private function getWeeklyEngagementStats(int $userId): array
    {
        $weeks = [];
        $views = [];
        $edits = [];

        for ($i = 6; $i >= 0; $i--) {
            $weekStart = now()->subWeeks($i)->startOfWeek();
            $weekEnd = now()->subWeeks($i)->endOfWeek();
            
            $weeks[] = $weekStart->format('M d');
            
            // Simulate view data (you can replace with actual analytics)
            $views[] = rand(50, 200);
            
            // Count edits made during this week
            $editCount = BlogPost::where('updated_by', $userId)
                ->whereBetween('updated_at', [$weekStart, $weekEnd])
                ->count();
            $edits[] = $editCount;
        }

        return [
            'labels' => $weeks,
            'views' => $views,
            'edits' => $edits,
        ];
    }

    /**
     * Get editor permissions and capabilities
     */
    public function getPermissions(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'permissions' => [
                'can_create_articles' => true,
                'can_edit_articles' => true,
                'can_delete_articles' => true,
                'can_manage_watchlists' => true,
                'can_create_reports' => true,
                'can_view_analytics' => true,
                'can_manage_users' => false, // Editor cannot manage users
                'can_access_admin_settings' => false, // Editor cannot access admin settings
            ],
        ]);
    }
}