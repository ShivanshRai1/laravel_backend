<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Watchlist;

class WatchlistController extends Controller
{
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