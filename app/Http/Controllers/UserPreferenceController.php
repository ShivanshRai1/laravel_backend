<?php

namespace App\Http\Controllers;

use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserPreferenceController extends Controller
{
    /**
     * Get user's KPI preferences
     */
    public function getKpiPreferences(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $preferences = UserPreference::where('user_id', $user->id)->first();

            if (!$preferences || empty($preferences->kpi_preferences)) {
                // Return default preferences
                return response()->json([
                    'success' => true,
                    'data' => UserPreference::getDefaultKpiPreferences()
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $preferences->kpi_preferences
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch preferences',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save user's KPI preferences
     */
    public function saveKpiPreferences(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $request->validate([
                'kpi_preferences' => 'required|array',
                'kpi_preferences.revenue' => 'boolean',
                'kpi_preferences.profit' => 'boolean',
                'kpi_preferences.market_cap' => 'boolean',
                'kpi_preferences.pe_ratio' => 'boolean',
                'kpi_preferences.dividend' => 'boolean',
                'kpi_preferences.volume' => 'boolean',
                'kpi_preferences.high_52w' => 'boolean',
                'kpi_preferences.low_52w' => 'boolean',
                'kpi_preferences.beta' => 'boolean',
                'kpi_preferences.eps' => 'boolean',
            ]);

            $preferences = UserPreference::updateOrCreate(
                ['user_id' => $user->id],
                ['kpi_preferences' => $request->kpi_preferences]
            );

            return response()->json([
                'success' => true,
                'message' => 'Preferences saved successfully',
                'data' => $preferences->kpi_preferences
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save preferences',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset KPI preferences to default
     */
    public function resetKpiPreferences(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $preferences = UserPreference::updateOrCreate(
                ['user_id' => $user->id],
                ['kpi_preferences' => UserPreference::getDefaultKpiPreferences()]
            );

            return response()->json([
                'success' => true,
                'message' => 'Preferences reset to default',
                'data' => $preferences->kpi_preferences
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset preferences',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
