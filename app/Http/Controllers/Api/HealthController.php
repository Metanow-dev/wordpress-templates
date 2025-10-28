<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    /**
     * Comprehensive health check endpoint
     */
    public function check(): JsonResponse
    {
        $checks = [];
        $overallStatus = 'healthy';

        // Check database connectivity
        if (config('security.health_check.check_database', true)) {
            try {
                DB::connection()->getPdo();
                $checks['database'] = [
                    'status' => 'healthy',
                    'message' => 'Database connection established',
                ];
            } catch (\Exception $e) {
                $checks['database'] = [
                    'status' => 'unhealthy',
                    'message' => 'Database connection failed: ' . $e->getMessage(),
                ];
                $overallStatus = 'unhealthy';
            }
        }

        // Check cache connectivity
        if (config('security.health_check.check_cache', true)) {
            try {
                $testKey = 'health_check_' . time();
                Cache::put($testKey, 'test', 10);
                $value = Cache::get($testKey);
                Cache::forget($testKey);

                if ($value === 'test') {
                    $checks['cache'] = [
                        'status' => 'healthy',
                        'message' => 'Cache is operational',
                        'driver' => config('cache.default'),
                    ];
                } else {
                    throw new \Exception('Cache read/write test failed');
                }
            } catch (\Exception $e) {
                $checks['cache'] = [
                    'status' => 'unhealthy',
                    'message' => 'Cache error: ' . $e->getMessage(),
                ];
                $overallStatus = 'degraded';
            }
        }

        // Check memory usage
        if (config('security.health_check.check_memory', true)) {
            $memoryLimit = $this->getMemoryLimitInBytes();
            $memoryUsage = memory_get_usage(true);
            $memoryPeak = memory_get_peak_usage(true);
            $memoryUsagePercent = $memoryLimit > 0 ? ($memoryUsage / $memoryLimit) * 100 : 0;

            $memoryStatus = 'healthy';
            if ($memoryUsagePercent >= config('security.memory.critical_threshold', 85)) {
                $memoryStatus = 'critical';
                $overallStatus = 'unhealthy';
            } elseif ($memoryUsagePercent >= config('security.memory.warning_threshold', 70)) {
                $memoryStatus = 'warning';
                if ($overallStatus === 'healthy') {
                    $overallStatus = 'degraded';
                }
            }

            $checks['memory'] = [
                'status' => $memoryStatus,
                'usage_mb' => round($memoryUsage / 1024 / 1024, 2),
                'peak_mb' => round($memoryPeak / 1024 / 1024, 2),
                'limit_mb' => $memoryLimit > 0 ? round($memoryLimit / 1024 / 1024, 2) : 'unlimited',
                'usage_percent' => round($memoryUsagePercent, 2),
            ];
        }

        // Check disk space
        $diskFree = disk_free_space(storage_path());
        $diskTotal = disk_total_space(storage_path());
        $diskUsagePercent = ($diskTotal - $diskFree) / $diskTotal * 100;

        $diskStatus = 'healthy';
        if ($diskUsagePercent >= 95) {
            $diskStatus = 'critical';
            $overallStatus = 'unhealthy';
        } elseif ($diskUsagePercent >= 85) {
            $diskStatus = 'warning';
            if ($overallStatus === 'healthy') {
                $overallStatus = 'degraded';
            }
        }

        $checks['disk'] = [
            'status' => $diskStatus,
            'free_gb' => round($diskFree / 1024 / 1024 / 1024, 2),
            'total_gb' => round($diskTotal / 1024 / 1024 / 1024, 2),
            'usage_percent' => round($diskUsagePercent, 2),
        ];

        // Add application info
        $appInfo = [
            'name' => config('app.name'),
            'environment' => config('app.env'),
            'version' => '1.0.0',
            'timestamp' => now()->toISOString(),
            'uptime_seconds' => $this->getUptime(),
        ];

        $statusCode = match ($overallStatus) {
            'healthy' => 200,
            'degraded' => 200, // Still operational
            'unhealthy' => 503,
            default => 500,
        };

        return response()->json([
            'status' => $overallStatus,
            'checks' => $checks,
            'app' => $appInfo,
        ], $statusCode);
    }

    /**
     * Simple health check for load balancers
     */
    public function simple(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Get PHP memory limit in bytes
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

    /**
     * Get application uptime in seconds (simplified version)
     */
    protected function getUptime(): ?int
    {
        // This is a simplified version - in production you'd track this with a file or cache
        return null;
    }
}
