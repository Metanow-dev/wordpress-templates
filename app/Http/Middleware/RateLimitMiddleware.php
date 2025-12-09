<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $tier = 'api'): Response
    {
        $config = config("security.rate_limiting.{$tier}");

        // Skip if rate limiting is disabled
        if (!$config || !($config['enabled'] ?? false)) {
            return $next($request);
        }

        $maxAttempts = (int) $config['max_attempts'];
        $decayMinutes = (int) $config['decay_minutes'];

        // Generate unique key for this IP and tier
        $key = $this->resolveRequestSignature($request, $tier);

        // Use the configured cache driver for rate limiting
        $cacheDriver = config('security.rate_limiting.cache_driver', 'redis');
        $cache = Cache::store($cacheDriver);

        // Get current attempt count
        $attempts = (int) $cache->get($key, 0);

        // Check if limit exceeded
        if ($attempts >= $maxAttempts) {
            Log::warning('Rate limit exceeded', [
                'ip' => $request->ip(),
                'tier' => $tier,
                'attempts' => $attempts,
                'limit' => $maxAttempts,
                'path' => $request->path(),
                'method' => $request->method(),
            ]);

            return response()->json([
                'error' => 'Too many requests',
                'message' => "Rate limit exceeded. Maximum {$maxAttempts} requests per {$decayMinutes} minute(s).",
                'retry_after' => $this->getRetryAfter($cache, $key),
            ], 429);
        }

        // Increment attempt counter
        if ($attempts === 0) {
            // First request in window - set with expiry
            $cache->put($key, 1, now()->addMinutes($decayMinutes));
        } else {
            // Increment existing counter
            $cache->increment($key);
        }

        $response = $next($request);

        // Add rate limit headers
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', max(0, $maxAttempts - $attempts - 1));
        $response->headers->set('X-RateLimit-Reset', $this->getResetTime($cache, $key));

        return $response;
    }

    /**
     * Generate a unique signature for this request.
     */
    protected function resolveRequestSignature(Request $request, string $tier): string
    {
        $ip = $request->ip();
        $route = $request->path();

        return sprintf(
            'rate_limit:%s:%s:%s',
            $tier,
            sha1($ip),
            sha1($route)
        );
    }

    /**
     * Get the number of seconds until the rate limit resets.
     */
    protected function getRetryAfter($cache, string $key): int
    {
        // Laravel's cache doesn't expose TTL directly, so we estimate
        // This could be improved with Redis PTTL command if needed
        return 60; // Default to 1 minute
    }

    /**
     * Get the Unix timestamp when the rate limit resets.
     */
    protected function getResetTime($cache, string $key): int
    {
        return now()->addMinutes(1)->timestamp;
    }
}
