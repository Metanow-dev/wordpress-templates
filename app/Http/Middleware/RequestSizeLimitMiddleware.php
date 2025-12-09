<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequestSizeLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if this route is excluded from size limiting
        if ($this->isExcluded($request)) {
            return $next($request);
        }

        $maxSizeMB = config('security.request.max_body_size_mb', 10);
        $maxSizeBytes = $maxSizeMB * 1024 * 1024;

        // Get content length from header
        $contentLength = $request->header('Content-Length', 0);

        if ($contentLength > $maxSizeBytes) {
            Log::warning('Request body size limit exceeded', [
                'content_length_mb' => round($contentLength / 1024 / 1024, 2),
                'max_allowed_mb' => $maxSizeMB,
                'ip' => $request->ip(),
                'path' => $request->path(),
                'method' => $request->method(),
            ]);

            return response()->json([
                'error' => 'Payload too large',
                'message' => "Request body size exceeds maximum allowed size of {$maxSizeMB}MB.",
                'content_length_mb' => round($contentLength / 1024 / 1024, 2),
            ], 413);
        }

        // Validate JSON depth for JSON requests
        if ($request->isJson()) {
            $maxDepth = config('security.request.max_json_depth', 512);

            try {
                $content = $request->getContent();
                $decoded = json_decode($content, true, $maxDepth, JSON_THROW_ON_ERROR);

                if ($this->getArrayDepth($decoded) > $maxDepth) {
                    Log::warning('JSON depth limit exceeded', [
                        'ip' => $request->ip(),
                        'path' => $request->path(),
                    ]);

                    return response()->json([
                        'error' => 'Invalid JSON structure',
                        'message' => "JSON payload exceeds maximum nesting depth of {$maxDepth} levels.",
                    ], 400);
                }
            } catch (\JsonException $e) {
                return response()->json([
                    'error' => 'Invalid JSON',
                    'message' => 'The request body contains invalid JSON.',
                ], 400);
            }
        }

        return $next($request);
    }

    /**
     * Check if the current route is excluded from size limiting.
     */
    protected function isExcluded(Request $request): bool
    {
        $excludedRoutes = config('security.request.excluded_routes', []);

        foreach ($excludedRoutes as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calculate the maximum depth of a nested array.
     */
    protected function getArrayDepth($array, $currentDepth = 0): int
    {
        if (!is_array($array)) {
            return $currentDepth;
        }

        $maxDepth = $currentDepth;

        foreach ($array as $value) {
            if (is_array($value)) {
                $depth = $this->getArrayDepth($value, $currentDepth + 1);
                $maxDepth = max($maxDepth, $depth);
            }
        }

        return $maxDepth;
    }
}
