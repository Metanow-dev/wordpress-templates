<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class MemoryMonitorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('security.memory.monitoring_enabled', true)) {
            return $next($request);
        }

        // Record memory usage before request
        $memoryBefore = memory_get_usage(true);
        $peakBefore = memory_get_peak_usage(true);

        // Check if we should reject the request (circuit breaker)
        if ($this->shouldRejectRequest()) {
            Log::critical('Circuit breaker activated - memory critical', [
                'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
                'memory_limit' => ini_get('memory_limit'),
                'ip' => $request->ip(),
                'path' => $request->path(),
            ]);

            return response()->json([
                'error' => 'Service temporarily unavailable',
                'message' => 'Server is experiencing high load. Please try again in a few moments.',
                'retry_after' => 30,
            ], 503)->header('Retry-After', 30);
        }

        // Process the request
        $response = $next($request);

        // Measure memory usage after request
        $memoryAfter = memory_get_usage(true);
        $peakAfter = memory_get_peak_usage(true);
        $memoryUsed = $memoryAfter - $memoryBefore;
        $memoryUsedMB = round($memoryUsed / 1024 / 1024, 2);

        // Log if memory usage exceeds threshold
        $logThreshold = config('security.memory.log_threshold_mb', 128) * 1024 * 1024;
        if ($memoryUsed > $logThreshold) {
            Log::warning('High memory usage detected', [
                'memory_used_mb' => $memoryUsedMB,
                'memory_before_mb' => round($memoryBefore / 1024 / 1024, 2),
                'memory_after_mb' => round($memoryAfter / 1024 / 1024, 2),
                'peak_usage_mb' => round($peakAfter / 1024 / 1024, 2),
                'path' => $request->path(),
                'method' => $request->method(),
                'ip' => $request->ip(),
            ]);
        }

        // Add memory usage headers in non-production environments
        if (!app()->environment('production')) {
            $response->headers->set('X-Memory-Used-MB', $memoryUsedMB);
            $response->headers->set('X-Memory-Peak-MB', round($peakAfter / 1024 / 1024, 2));
        }

        // Trigger garbage collection if memory usage is high
        if ($this->getMemoryUsagePercentage() > config('security.memory.warning_threshold', 70)) {
            gc_collect_cycles();
        }

        return $response;
    }

    /**
     * Determine if the request should be rejected due to high memory usage.
     */
    protected function shouldRejectRequest(): bool
    {
        if (!config('security.memory.circuit_breaker_enabled', true)) {
            return false;
        }

        $usagePercentage = $this->getMemoryUsagePercentage();
        $criticalThreshold = config('security.memory.critical_threshold', 85);

        return $usagePercentage >= $criticalThreshold;
    }

    /**
     * Get current memory usage as a percentage of the PHP memory limit.
     */
    protected function getMemoryUsagePercentage(): float
    {
        $memoryLimit = $this->getMemoryLimitInBytes();
        if ($memoryLimit === -1) {
            return 0; // No limit set
        }

        $currentUsage = memory_get_usage(true);
        return ($currentUsage / $memoryLimit) * 100;
    }

    /**
     * Get PHP memory limit in bytes.
     */
    protected function getMemoryLimitInBytes(): int
    {
        $memoryLimit = ini_get('memory_limit');

        if ($memoryLimit === '-1') {
            return -1; // Unlimited
        }

        $unit = strtoupper(substr($memoryLimit, -1));
        $value = (int) substr($memoryLimit, 0, -1);

        return match ($unit) {
            'G' => $value * 1024 * 1024 * 1024,
            'M' => $value * 1024 * 1024,
            'K' => $value * 1024,
            default => (int) $memoryLimit,
        };
    }
}
