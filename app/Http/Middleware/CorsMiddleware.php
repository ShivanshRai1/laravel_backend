<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->header('Origin');
        $method = $request->getMethod();
        $path = $request->getPathInfo();
        
        // Direct file logging to ensure we capture everything
        file_put_contents(storage_path('logs/cors-debug.log'), 
            '[' . now() . '] CORS Request: ' . $method . ' ' . $path . ' from origin: ' . ($origin ?: 'null') . PHP_EOL, 
            FILE_APPEND | LOCK_EX
        );
        
        // Log incoming request details
        Log::info('CORS Middleware - Incoming Request', [
            'method' => $method,
            'path' => $path,
            'origin' => $origin,
            'headers' => $request->headers->all(),
            'user_agent' => $request->header('User-Agent'),
        ]);
        
    // For development, allow only the requesting origin if present, else block
    $allowOrigin = $origin ? $origin : '';
    $allowCredentials = 'true';
    if ($method === "OPTIONS") {
            Log::info('CORS Middleware - Handling preflight OPTIONS request', [
                'origin' => $origin,
                'allow_origin' => $allowOrigin,
                'allow_credentials' => $allowCredentials,
                'requested_headers' => $request->header('Access-Control-Request-Headers'),
                'requested_method' => $request->header('Access-Control-Request-Method')
            ]);

            $response = response('', 200)
                ->header('Access-Control-Allow-Origin', $allowOrigin)
                ->header('Vary', 'Origin')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, HEAD')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, X-CSRF-TOKEN, Origin')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Max-Age', '86400');
                
            Log::info('CORS Middleware - Preflight response headers set', [
                'response_headers' => $response->headers->all()
            ]);
            
            // Also write to Laravel log file directly to ensure it shows up
            file_put_contents(storage_path('logs/cors-debug.log'), 
                '[' . now() . '] OPTIONS request handled for origin: ' . $origin . PHP_EOL, 
                FILE_APPEND | LOCK_EX
            );
            
            return $response;
        }

        try {
            $response = $next($request);
            
            Log::info('CORS Middleware - Processing actual request', [
                'method' => $method,
                'path' => $path,
                'response_status' => $response->getStatusCode()
            ]);
            
        } catch (\Exception $e) {
            Log::error('CORS Middleware - Error processing request', [
                'method' => $method,
                'path' => $path,
                'origin' => $origin,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        // Add CORS headers to all responses
        $response = $response
            ->header('Access-Control-Allow-Origin', $allowOrigin)
            ->header('Vary', 'Origin')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, HEAD')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, X-CSRF-TOKEN, Origin')
            ->header('Access-Control-Allow-Credentials', 'true');

        Log::info('CORS Middleware - Final response headers set', [
            'allow_origin' => $allowOrigin,
            'allow_credentials' => $allowCredentials,
            'response_status' => $response->getStatusCode(),
            'response_headers' => $response->headers->all()
        ]);

        return $response;
    }
}
