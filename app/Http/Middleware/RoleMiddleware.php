<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

    // Make role check case-insensitive for compatibility with ENUM values
    $userRole = strtolower($user->role);
    $rolesLower = array_map('strtolower', $roles);
    if (!in_array($userRole, $rolesLower)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: Insufficient permissions'
            ], 403);
        }

        return $next($request);
    }
}
