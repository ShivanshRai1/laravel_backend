<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\Company;
use App\Models\BlogPost;
use App\Models\FinancialData;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Get dashboard statistics for admin
     */
    public function getDashboardStats(): JsonResponse
    {
        try {
            // Total companies count
            $totalCompanies = Company::count();
            
            // Latest uploads count (financial data uploaded in last 7 days)
            $latestUploads = FinancialData::where('created_at', '>=', Carbon::now()->subDays(7))->count();
            
            // New users count (users registered in last 30 days)
            $newUsers = User::where('created_at', '>=', Carbon::now()->subDays(30))->count();
            
            // Pending blog posts count (draft or pending approval)
            $pendingBlogPosts = BlogPost::whereIn('status', ['draft', 'pending'])->count();
            
            // Additional stats for trends
            $totalUsers = User::count();
            $totalBlogPosts = BlogPost::count();
            $totalFinancialData = FinancialData::count();
            
            // Recent activity
            $recentUsers = User::latest()->take(5)->get(['id', 'name', 'email', 'created_at']);
            $recentBlogPosts = BlogPost::latest()->take(5)->get(['id', 'title', 'status', 'created_at']);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'widgets' => [
                        'total_companies' => $totalCompanies,
                        'latest_uploads' => $latestUploads,
                        'new_users' => $newUsers,
                        'pending_blog_posts' => $pendingBlogPosts
                    ],
                    'totals' => [
                        'total_users' => $totalUsers,
                        'total_blog_posts' => $totalBlogPosts,
                        'total_financial_data' => $totalFinancialData
                    ],
                    'recent_activity' => [
                        'recent_users' => $recentUsers,
                        'recent_blog_posts' => $recentBlogPosts
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all users with pagination
     */
    public function getUsers(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 10);
            $users = User::paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new user
     */
    public function createUser(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'role' => 'required|in:admin,editor,user'
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'role' => $request->role,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing user
     */
    public function updateUser(Request $request, $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            
            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
                'password' => 'sometimes|string|min:8',
                'role' => 'sometimes|required|in:admin,editor,user'
            ]);

            $updateData = $request->only(['name', 'email', 'role']);
            if ($request->has('password')) {
                $updateData['password'] = bcrypt($request->password);
            }

            $user->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a user
     */
    public function deleteUser($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system settings
     */
    public function getSettings(): JsonResponse
    {
        // This would typically fetch from a settings table
        // For now, return some default settings
        return response()->json([
            'success' => true,
            'data' => [
                'app_name' => 'Financial Dashboard',
                'maintenance_mode' => false,
                'max_upload_size' => '10MB',
                'allowed_file_types' => ['csv', 'xlsx', 'pdf']
            ]
        ]);
    }

    /**
     * Update system settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        // This would typically update a settings table
        // For now, just return success
        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
            'data' => $request->all()
        ]);
    }
}