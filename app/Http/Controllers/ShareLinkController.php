<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\JsonResponse;

class ShareLinkController extends Controller
{
    /**
     * Generate a unique, tokenized share link for a dashboard view.
     * The link will be valid for 7 days and does not touch existing data.
     */
    public function generate(Request $request): JsonResponse
    {
        $user = auth()->user();
        $symbol = $request->input('symbol');
        if (!$symbol) {
            return response()->json(['success' => false, 'error' => 'Missing symbol'], 422);
        }
        // Generate a unique token
        $token = Str::random(32);
        // Store the mapping in cache for 7 days (no DB write)
        Cache::put('sharelink:' . $token, [
            'user_id' => $user->id,
            'symbol' => $symbol,
        ], now()->addDays(7));
        // Generate the public URL
        $url = URL::to('/shared-dashboard/' . $token);
        return response()->json(['success' => true, 'url' => $url]);
    }

    /**
     * Resolve a shared dashboard link by token.
     * Returns the symbol if valid, else 404.
     */
    public function resolve($token): JsonResponse
    {
        $data = Cache::get('sharelink:' . $token);
        if (!$data) {
            return response()->json(['success' => false, 'error' => 'Invalid or expired link'], 404);
        }
        return response()->json(['success' => true, 'symbol' => $data['symbol']]);
    }
}
