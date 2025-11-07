<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Watchlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class WatchlistController extends Controller
{
    // List all watchlist items for the authenticated user
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $watchlist = Watchlist::where('user_id', $user->id)
                ->get();
                
            $formattedWatchlist = $watchlist->map(function($item) {
                return [
                    'id' => $item->uuid ?? $item->id, // Use UUID if available, fallback to ID
                    'company_id' => $item->company_id,
                    'symbol' => $item->company_id, // Use company_id as symbol
                    'company_name' => $item->company_name,
                    'alert_type' => $item->alert_type,
                    'alert_value' => $item->alert_value,
                    'created_at' => $item->created_at,
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $formattedWatchlist
            ]);
        } catch (\Exception $e) {
            \Log::error("Error fetching watchlist: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve watchlist: ' . $e->getMessage()
            ], 500);
        }
    }

    // Add a company to watchlist
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Log everything for debugging
        \Log::info('Watchlist store request', [
            'user' => $user->id,
            'payload' => $request->all(),
            'headers' => $request->header()
        ]);
        
        $validated = $request->validate([
            'company_id' => 'required', // Can be ticker, symbol or numeric ID
            'company_name' => 'nullable|string',
            'symbol' => 'nullable|string',
            'alert_type' => 'nullable|string',
            'alert_value' => 'nullable|string',
        ]);
        
        // Convert company_id to string to handle both numeric and string IDs
        $companyId = (string)$validated['company_id'];
        
        // Get company info from the database if possible
        $company = \App\Models\Company::where('id', $companyId)
            ->orWhere('symbol', $companyId)
            ->first();
        // Use company data from DB if available, otherwise use payload
        $companyName = $company ? $company->name : ($validated['company_name'] ?? 'Unknown Company');
        $symbol = $company ? $company->symbol : ($validated['symbol'] ?? $companyId);
        
        // Check if already in watchlist - using symbol or id
        $existing = Watchlist::where('user_id', $user->id)
            ->where(function($query) use ($companyId, $symbol) {
                $query->where('company_id', $companyId)
                      ->orWhere('company_id', $symbol);
            })->first();
            
        if ($existing) {
            return response()->json([
                'success' => false, 
                'message' => 'Company already in your watchlist',
                'data' => $existing
            ], 409);
        }
        
        try {
            // Create watchlist item with UUID
            $watchlist = new Watchlist();
            $watchlist->uuid = \Illuminate\Support\Str::uuid()->toString();
            $watchlist->user_id = $user->id;
            $watchlist->company_id = $symbol; // Store symbol in company_id
            $watchlist->company_name = $companyName;
            $watchlist->alert_type = $validated['alert_type'] ?? 'price_change';
            $watchlist->alert_value = $validated['alert_value'] ?? '5%';
            $watchlist->save();
            
            \Log::info('Added to watchlist', [
                'user' => $user->id,
                'company' => $companyId,
                'watchlist_id' => $watchlist->id
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Watchlist create error: {$e->getMessage()}", [
                'user' => $user->id,
                'company' => $companyId,
                'exception' => [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]
            ]);
            
            return response()->json([
                'success' => false,
                'message' => "Failed to add to watchlist: " . $e->getMessage()
            ], 500);
        }
        
        return response()->json([
            'success' => true, 
            'message' => 'Company added to your watchlist',
            'data' => $watchlist
        ], 201);
    }

    // Remove a company from watchlist
    public function destroy($id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Try to find by UUID, ID, or company ticker/symbol
            $watchlist = Watchlist::where('user_id', $user->id)
                ->where(function($query) use ($id) {
                    $query->where('uuid', $id)
                          ->orWhere('id', $id)
                          ->orWhere('company_id', $id);
                })->first();
                
            if (!$watchlist) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Watchlist item not found'
                ], 404);
            }
            
            $watchlist->delete();
            return response()->json([
                'success' => true, 
                'message' => 'Item removed from watchlist'
            ]);
        } catch (\Exception $e) {
            \Log::error("Error deleting watchlist item: {$e->getMessage()}");
            return response()->json([
                'success' => false, 
                'message' => 'Failed to remove from watchlist: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getWatchlists(): JsonResponse
    {
        try {
            $watchlists = Watchlist::all();
            return response()->json(['success' => true, 'data' => $watchlists]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function createWatchlist(Request $request): JsonResponse
    {
        try {
            $watchlist = Watchlist::create($request->all());
            return response()->json(['success' => true, 'data' => $watchlist]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function updateWatchlist(Request $request, $id): JsonResponse
    {
        try {
            $watchlist = Watchlist::findOrFail($id);
            $watchlist->update($request->all());
            return response()->json(['success' => true, 'data' => $watchlist]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function deleteWatchlist($id): JsonResponse
    {
        try {
            $watchlist = Watchlist::findOrFail($id);
            $watchlist->delete();
            return response()->json(['success' => true, 'message' => 'Watchlist deleted']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}