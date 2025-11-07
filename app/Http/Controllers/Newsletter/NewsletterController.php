<?php

namespace App\Http\Controllers\Newsletter;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NewsletterController extends \App\Http\Controllers\Controller
{
    // Subscribe to newsletter
    public function subscribe(Request $request): JsonResponse
    {
        // ...implementation...
        return response()->json(['success' => true]);
    }

    // Unsubscribe from newsletter
    public function unsubscribe(Request $request): JsonResponse
    {
        // ...implementation...
        return response()->json(['success' => true]);
    }

    // Send newsletter (admin)
    public function send(Request $request): JsonResponse
    {
        // ...implementation...
        return response()->json(['success' => true]);
    }
}
