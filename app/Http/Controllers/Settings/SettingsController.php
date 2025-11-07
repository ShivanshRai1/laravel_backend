<?php

namespace App\Http\Controllers\Settings;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SettingsController extends \App\Http\Controllers\Controller
{
    // Get user settings
    public function index(Request $request): JsonResponse
    {
        // ...implementation...
        return response()->json(['success' => true, 'data' => []]);
    }

    // Update user settings
    public function update(Request $request): JsonResponse
    {
        // ...implementation...
        return response()->json(['success' => true]);
    }
}
