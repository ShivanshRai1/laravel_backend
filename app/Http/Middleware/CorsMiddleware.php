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
        
        // Log incoming request details
        Log::info('CORS Middleware - Incoming Request', [
            'method' => $method,
            'path' => $path,
            'origin' => $origin,
            'headers' => $request->headers->all(),
            'user_agent' => $request->header('User-Agent'),
        ]);
        
        // Define allowed origins
        $allowedOrigins = [
            'https://react-frontend-mauve-six.vercel.app',
            'http://localhost:3000',
            'http://localhost:3001', 
            'http://127.0.0.1:3000',
            'http://127.0.0.1:3001'
        ];
        
        // Check if origin is allowed
        $allowOrigin = '*';
        $allowCredentials = 'false';
        $originAllowed = false;
        
        if ($origin && in_array($origin, $allowedOrigins)) {
            $allowOrigin = $origin;
            $allowCredentials = 'true';
            $originAllowed = true;
            Log::info('CORS Middleware - Origin allowed from static list', ['origin' => $origin]);
        } elseif ($origin && preg_match('/^https:\/\/.*\.vercel\.app$/', $origin)) {
            // Allow all Vercel deployments
            $allowOrigin = $origin;
            $allowCredentials = 'true';
            $originAllowed = true;
            Log::info('CORS Middleware - Origin allowed from Vercel pattern', ['origin' => $origin]);
        } else {
            Log::warning('CORS Middleware - Origin not specifically allowed, using wildcard', [
                'origin' => $origin,
                'allowed_origins' => $allowedOrigins
            ]);
        }
        
        // Handle preflight OPTIONS requests
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
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, X-CSRF-TOKEN')
                ->header('Access-Control-Allow-Credentials', $allowCredentials)
                ->header('Access-Control-Max-Age', '3600');
                
            Log::info('CORS Middleware - Preflight response headers set', [
                'response_headers' => $response->headers->all()
            ]);
            
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
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, X-CSRF-TOKEN')
            ->header('Access-Control-Allow-Credentials', $allowCredentials);

        Log::info('CORS Middleware - Final response headers set', [
            'allow_origin' => $allowOrigin,
            'allow_credentials' => $allowCredentials,
            'response_status' => $response->getStatusCode(),
            'response_headers' => $response->headers->all()
        ]);

        return $response;
    }
}
