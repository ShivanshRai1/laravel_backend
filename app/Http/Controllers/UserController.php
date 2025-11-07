<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\Watchlist;
use App\Models\UploadedFinancialData;
use App\Models\Company;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Get dashboard data for the authenticated user
     */
    public function getDashboard(): JsonResponse
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthenticated.'
                ], 401);
            }

            // Get user's watchlist companies
            $watchlist = Watchlist::where('user_id', $user->id)
                ->with('company')
                ->get();

            $watchlistData = [];
            
            foreach ($watchlist as $item) {
                // Get company to find the numeric ID
                $company = Company::where('symbol', $item->company_id)
                    ->orWhere('id', $item->company_id)
                    ->first();
                
                if (!$company) {
                    continue; // Skip if company not found
                }
                
                $companyData = [
                    'id' => $company->id,
                    'name' => $item->company_name ?? $company->name,
                    'ticker' => $company->symbol,
                    'revenue_growth' => null,
                    'latest_revenue' => null,
                    'previous_revenue' => null,
                    'latest_quarter' => null,
                    'previous_quarter' => null,
                    'trend' => 'neutral'
                ];

                // Get financial data using the numeric company ID
                $financialData = UploadedFinancialData::where('company_id', $company->id)
                    ->where('Metrics', 'like', '%Revenue%')
                    ->orWhere('Metrics', 'like', '%Sales%')
                    ->first();

                if ($financialData) {
                    // Get the two most recent quarters with data
                    $quarters = ['CY_2025_Q1', 'CY_2024_Q4', 'CY_2024_Q3', 'CY_2024_Q2', 'CY_2024_Q1', 'CY_2023_Q4'];
                    $quarterValues = [];
                    
                    foreach ($quarters as $quarter) {
                        if (isset($financialData->$quarter) && $financialData->$quarter !== null && $financialData->$quarter !== '') {
                            $quarterValues[$quarter] = floatval($financialData->$quarter);
                        }
                    }

                    if (count($quarterValues) >= 2) {
                        $quarterKeys = array_keys($quarterValues);
                        $latestQuarter = $quarterKeys[0];
                        $previousQuarter = $quarterKeys[1];
                        
                        $latestRevenue = $quarterValues[$latestQuarter];
                        $previousRevenue = $quarterValues[$previousQuarter];

                        if ($previousRevenue != 0) {
                            $revenueGrowth = (($latestRevenue - $previousRevenue) / abs($previousRevenue)) * 100;
                            
                            $companyData['revenue_growth'] = round($revenueGrowth, 2);
                            $companyData['latest_revenue'] = $latestRevenue;
                            $companyData['previous_revenue'] = $previousRevenue;
                            $companyData['latest_quarter'] = str_replace('CY_', '', $latestQuarter);
                            $companyData['previous_quarter'] = str_replace('CY_', '', $previousQuarter);
                            $companyData['trend'] = $revenueGrowth > 0 ? 'up' : ($revenueGrowth < 0 ? 'down' : 'neutral');
                        }
                    }
                }

                $watchlistData[] = $companyData;
            }

            $dashboardData = [
                'user' => $user,
                'welcome_message' => 'Welcome to your dashboard, ' . $user->name . '!',
                'watchlist_count' => $watchlist->count(),
                'watchlist_companies' => $watchlistData,
                'statistics' => [
                    'total_watchlist' => $watchlist->count(),
                    'companies_with_growth' => collect($watchlistData)->filter(fn($c) => $c['trend'] === 'up')->count(),
                    'companies_with_decline' => collect($watchlistData)->filter(fn($c) => $c['trend'] === 'down')->count(),
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $dashboardData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getProfile(): JsonResponse
    {
        try {
            $user = auth()->user();
            return response()->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            
            $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $user->id
            ]);

            $user->update($request->only(['name', 'email']));

            return response()->json([
                'success' => true,
                'data' => $user,
                'message' => 'Profile updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}