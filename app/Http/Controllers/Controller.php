<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

abstract class Controller
{
    /**
     * Log API request details for debugging
     */
    protected function logApiRequest(Request $request, string $action = null): void
    {
        Log::info('API Request', [
            'action' => $action ?? class_basename($this),
            'method' => $request->getMethod(),
            'path' => $request->getPathInfo(),
            'origin' => $request->header('Origin'),
            'user_agent' => $request->header('User-Agent'),
            'ip' => $request->ip(),
            'params' => $request->except(['password', 'password_confirmation'])
        ]);
    }

    /**
     * Log API response for debugging
     */
    protected function logApiResponse($response, int $statusCode = null): void
    {
        Log::info('API Response', [
            'status_code' => $statusCode ?? ($response instanceof \Illuminate\Http\JsonResponse ? $response->getStatusCode() : 200),
            'response_type' => is_object($response) ? get_class($response) : gettype($response)
        ]);
    }

    /**
     * Log API errors
     */
    protected function logApiError(\Exception $e, Request $request = null): void
    {
        Log::error('API Error', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'method' => $request ? $request->getMethod() : 'unknown',
            'path' => $request ? $request->getPathInfo() : 'unknown',
            'origin' => $request ? $request->header('Origin') : 'unknown',
            'trace' => $e->getTraceAsString()
        ]);
    }
}
