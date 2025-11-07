<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EditorController;
use App\Http\Controllers\ShareLinkController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\WatchlistController;
use App\Http\Controllers\FinancialController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserPreferenceController;
use App\Http\Controllers\InsightsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Test route for CORS debugging
Route::get('/test-cors', function () {
    return response()->json(['message' => 'CORS test successful', 'timestamp' => now()]);
});

// Public route to get all companies (no auth required)
Route::get('/companies/all', [CompanyController::class, 'index']);

// Public routes for dashboard financial data (no auth required)
Route::get('/dashboard/financial-data', [FinancialController::class, 'getDashboardFinancialData']);
Route::get('/dashboard/all-financial-data', [FinancialController::class, 'getAllDashboardData']);

// Public route to get published articles (no auth required)
Route::get('/articles/published', [ContentController::class, 'getPublishedArticles']);

// Handle OPTIONS requests for CORS
Route::options('{any}', function () {
    return response('', 200);
})->where('any', '.*');

// Public authentication routes
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/admin-login', [AuthController::class, 'adminLogin']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

// Protected routes (require authentication)
Route::middleware('auth:api')->group(function () {

    // Fetch a single company by symbol or name (slug)
    Route::get('/companies/{identifier}', [CompanyController::class, 'showByIdentifier']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [\App\Http\Controllers\NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/mark-as-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-as-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'delete']);

    // Admin only routes
    Route::middleware('role:admin')->group(function () {
        // Financial data upload (enhanced)
        Route::post('/admin/financial-data/upload', [\App\Http\Controllers\FinancialDataController::class, 'upload']);
        Route::get('/admin/financial-data/upload-history', [\App\Http\Controllers\FinancialDataController::class, 'getUploadHistory']);
        Route::delete('/admin/financial-data/batch/{batchId}', [\App\Http\Controllers\FinancialDataController::class, 'deleteUploadBatch']);
        
        // Financial Ratio Management
        Route::get('/admin/financial-ratios/{companyId}', [\App\Http\Controllers\FinancialRatioController::class, 'getByCompany']);
        Route::put('/admin/financial-ratios/{companyId}/metric', [\App\Http\Controllers\FinancialRatioController::class, 'updateMetric']);
        Route::post('/admin/financial-ratios/{companyId}/bulk', [\App\Http\Controllers\FinancialRatioController::class, 'bulkUpdate']);
        
        // Financial Metrics Management
        Route::get('/admin/financial-metrics', [\App\Http\Controllers\FinancialRatioController::class, 'getMetrics']);
        Route::post('/admin/financial-metrics', [\App\Http\Controllers\FinancialRatioController::class, 'createMetric']);
        Route::delete('/admin/financial-metrics/{id}', [\App\Http\Controllers\FinancialRatioController::class, 'deleteMetric']);
        
        // Blog Post Approval
        Route::get('/admin/blog-posts/pending', [\App\Http\Controllers\BlogPostController::class, 'getPending']);
        Route::post('/admin/blog-posts/{id}/approve', [\App\Http\Controllers\BlogPostController::class, 'approve']);
        Route::post('/admin/blog-posts/{id}/reject', [\App\Http\Controllers\BlogPostController::class, 'reject']);
        Route::post('/admin/blog-posts/{id}/request-changes', [\App\Http\Controllers\BlogPostController::class, 'requestChanges']);
        Route::post('/admin/blog-posts/bulk-approve', [\App\Http\Controllers\BlogPostController::class, 'bulkApprove']);
        Route::get('/admin/blog-posts/approval-history/{postId?}', [\App\Http\Controllers\BlogPostController::class, 'getApprovalHistory']);
        
        // User management
        Route::get('/admin/users', [AdminController::class, 'getUsers']);
        Route::post('/admin/users', [AdminController::class, 'createUser']);
        Route::put('/admin/users/{id}', [AdminController::class, 'updateUser']);
        Route::delete('/admin/users/{id}', [AdminController::class, 'deleteUser']);

        // Company management
        Route::get('/admin/companies', [CompanyController::class, 'index']);
        Route::post('/admin/companies', [CompanyController::class, 'store']);
        Route::put('/admin/companies/{id}', [CompanyController::class, 'update']);
        Route::delete('/admin/companies/{id}', [CompanyController::class, 'destroy']);

        // System settings
        Route::get('/admin/settings', [AdminController::class, 'getSettings']);
        Route::put('/admin/settings', [AdminController::class, 'updateSettings']);

        // Admin dashboard stats
        Route::get('/admin/dashboard-stats', [AdminController::class, 'getDashboardStats']);
    });

    // Admin and Editor routes (Content Management)
    Route::middleware('role:admin,editor')->group(function () {
        // Financial reports management
        Route::get('/reports', [ReportController::class, 'getReports']);
        Route::post('/reports', [ReportController::class, 'createReport']);
        Route::put('/reports/{id}', [ReportController::class, 'updateReport']);
        Route::delete('/reports/{id}', [ReportController::class, 'deleteReport']);

        // Watchlist management
        Route::get('/watchlists', [WatchlistController::class, 'getWatchlists']);
        Route::post('/watchlists', [WatchlistController::class, 'createWatchlist']);
        Route::put('/watchlists/{id}', [WatchlistController::class, 'updateWatchlist']);
        Route::delete('/watchlists/{id}', [WatchlistController::class, 'deleteWatchlist']);

        // Editor dashboard stats
        Route::get('/editor/dashboard-stats', [EditorController::class, 'getDashboardStats']);
    });

    // All authenticated users routes
    Route::get('/financial-data', [FinancialController::class, 'getFinancialData']);
    Route::get('/companies/search', [CompanyController::class, 'search']);
    Route::get('/user/profile', [UserController::class, 'getProfile']);
    Route::put('/user/profile', [UserController::class, 'updateProfile']);
    
    // Blog/Article routes (All users can view and create, edit/delete own posts)
    Route::get('/content/articles', [ContentController::class, 'getArticles']);
    Route::post('/content/articles', [ContentController::class, 'createArticle']);
    Route::put('/content/articles/{id}', [ContentController::class, 'updateArticle']);
    Route::delete('/content/articles/{id}', [ContentController::class, 'deleteArticle']);

    // User dashboard route (protected for authenticated users)
    Route::get('/user/dashboard', [UserController::class, 'getDashboard']);

    // Watchlist CRUD routes
    Route::get('/watchlist', [\App\Http\Controllers\WatchlistController::class, 'index']);
    Route::post('/watchlist', [\App\Http\Controllers\WatchlistController::class, 'store']);
    Route::delete('/watchlist/{id}', [\App\Http\Controllers\WatchlistController::class, 'destroy']);

    // Newsletter and Subscriber routes
    // Newsletter management (admin/editor only)
    Route::get('/newsletters', [\App\Http\Controllers\NewsletterController::class, 'index']);
    Route::post('/newsletters', [\App\Http\Controllers\NewsletterController::class, 'store']);
    Route::get('/newsletters/{id}', [\App\Http\Controllers\NewsletterController::class, 'show']);
    Route::put('/newsletters/{id}', [\App\Http\Controllers\NewsletterController::class, 'update']);
    Route::delete('/newsletters/{id}', [\App\Http\Controllers\NewsletterController::class, 'destroy']);
    Route::post('/newsletters/{id}/send', [\App\Http\Controllers\NewsletterController::class, 'send']);
    Route::get('/newsletter-stats', [\App\Http\Controllers\NewsletterController::class, 'stats']);

    // Subscriber management
    Route::get('/subscribers', [\App\Http\Controllers\SubscriberController::class, 'index']);
    Route::post('/subscribers', [\App\Http\Controllers\SubscriberController::class, 'store']);
    Route::get('/subscribers/{id}', [\App\Http\Controllers\SubscriberController::class, 'show']);
    Route::put('/subscribers/{id}', [\App\Http\Controllers\SubscriberController::class, 'update']);
    Route::delete('/subscribers/{id}', [\App\Http\Controllers\SubscriberController::class, 'destroy']);
    Route::post('/subscribers/{id}/unsubscribe', [\App\Http\Controllers\SubscriberController::class, 'unsubscribe']);
    Route::get('/subscriber-stats', [\App\Http\Controllers\SubscriberController::class, 'stats']);

    // Saved Comparisons management
    Route::get('/saved-comparisons', [\App\Http\Controllers\SavedComparisonController::class, 'index']);
    Route::post('/saved-comparisons', [\App\Http\Controllers\SavedComparisonController::class, 'store']);
    Route::get('/saved-comparisons/{id}', [\App\Http\Controllers\SavedComparisonController::class, 'show']);
    Route::put('/saved-comparisons/{id}', [\App\Http\Controllers\SavedComparisonController::class, 'update']);
    Route::delete('/saved-comparisons/{id}', [\App\Http\Controllers\SavedComparisonController::class, 'destroy']);

    // Email Alert Preferences
    Route::get('/email-alerts', [\App\Http\Controllers\NotificationController::class, 'getEmailAlerts']);
    Route::post('/email-alerts', [\App\Http\Controllers\NotificationController::class, 'createEmailAlert']);
    Route::put('/email-alerts/{id}', [\App\Http\Controllers\NotificationController::class, 'updateEmailAlert']);
    Route::delete('/email-alerts/{id}', [\App\Http\Controllers\NotificationController::class, 'deleteEmailAlert']);

    // Weekly Digest Subscription
    Route::get('/weekly-digest', [\App\Http\Controllers\NotificationController::class, 'getWeeklyDigest']);
    Route::post('/weekly-digest', [\App\Http\Controllers\NotificationController::class, 'updateWeeklyDigest']);

    // Newsletter Subscription (User Preferences)
    Route::get('/newsletter-subscription', [\App\Http\Controllers\NotificationController::class, 'getNewsletterSubscription']);
    Route::post('/newsletter-subscription', [\App\Http\Controllers\NotificationController::class, 'updateNewsletterSubscription']);

    // Push Notifications
    Route::post('/devices/register', [\App\Http\Controllers\NotificationController::class, 'registerDevice']);
    Route::post('/devices/unregister', [\App\Http\Controllers\NotificationController::class, 'unregisterDevice']);

    // Alert History
    Route::get('/alert-history', [\App\Http\Controllers\NotificationController::class, 'getAlertHistory']);

    // Shareable dashboard link - authenticated route
    Route::post('/share-link', [ShareLinkController::class, 'generate']);

    // User Preferences - KPI Customization
    Route::get('/user/kpi-preferences', [UserPreferenceController::class, 'getKpiPreferences']);
    Route::post('/user/kpi-preferences', [UserPreferenceController::class, 'saveKpiPreferences']);
    Route::post('/user/kpi-preferences/reset', [UserPreferenceController::class, 'resetKpiPreferences']);
});

// Shareable dashboard link - public route (no auth required)
Route::get('/shared-dashboard/{token}', [ShareLinkController::class, 'resolve']);

// Company Insights - public route (can be accessed without auth for shared views)
Route::get('/company/{ticker}/insights', [InsightsController::class, 'getCompanyInsights']);