<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiToken = config('services.api.token');
        
        if (!$apiToken) {
            return response()->json([
                'error' => 'API not configured',
                'message' => 'API token not set in configuration'
            ], 500);
        }
        
        $providedToken = $request->header('X-Api-Token') 
                      ?? $request->header('Authorization')
                      ?? $request->input('api_token');
        
        // Handle Bearer token format
        if (str_starts_with($providedToken ?? '', 'Bearer ')) {
            $providedToken = substr($providedToken, 7);
        }
        
        if (!$providedToken || !hash_equals($apiToken, $providedToken)) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid or missing API token'
            ], 401);
        }
        
        return $next($request);
    }
}