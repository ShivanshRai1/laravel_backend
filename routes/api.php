<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EditorController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\WatchlistController;
use App\Http\Controllers\FinancialController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\UserController;

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

// Public authentication routes
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/admin-login', [AuthController::class, 'adminLogin']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    
    // Admin only routes
    Route::middleware('role:admin')->group(function () {
    // Financial data upload
    Route::post('/admin/financial-data/upload', [\App\Http\Controllers\FinancialDataController::class, 'upload']);
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
        // Blog/Article management
        Route::get('/content/articles', [ContentController::class, 'getArticles']);
        Route::post('/content/articles', [ContentController::class, 'createArticle']);
        Route::put('/content/articles/{id}', [ContentController::class, 'updateArticle']);
        Route::delete('/content/articles/{id}', [ContentController::class, 'deleteArticle']);
        
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
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});