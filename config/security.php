<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for API and web routes to prevent abuse and
    | protect against DDoS attacks. Limits are enforced per IP address.
    |
    */

    'rate_limiting' => [
        // API endpoints rate limiting
        'api' => [
            'enabled' => env('API_RATE_LIMIT_ENABLED', true),
            'max_attempts' => env('API_RATE_LIMIT', 60), // requests per window
            'decay_minutes' => env('API_RATE_LIMIT_DECAY', 1), // time window in minutes
        ],

        // Web routes rate limiting
        'web' => [
            'enabled' => env('WEB_RATE_LIMIT_ENABLED', true),
            'max_attempts' => env('WEB_RATE_LIMIT', 1000),
            'decay_minutes' => env('WEB_RATE_LIMIT_DECAY', 1),
        ],

        // Strict rate limiting for resource-intensive operations
        'heavy' => [
            'enabled' => env('HEAVY_RATE_LIMIT_ENABLED', true),
            'max_attempts' => env('HEAVY_RATE_LIMIT', 10), // Very restrictive
            'decay_minutes' => env('HEAVY_RATE_LIMIT_DECAY', 5),
        ],

        // Cache driver for rate limiting (recommended: redis for production)
        'cache_driver' => env('RATE_LIMIT_CACHE_DRIVER', 'redis'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Memory Management Configuration
    |--------------------------------------------------------------------------
    |
    | Monitor and control memory usage to prevent OOM (Out of Memory) kills.
    | Circuit breaker pattern activates when thresholds are exceeded.
    |
    */

    'memory' => [
        // Enable memory monitoring
        'monitoring_enabled' => env('MEMORY_MONITORING_ENABLED', true),

        // Memory limit thresholds (percentage of PHP memory_limit)
        'warning_threshold' => env('MEMORY_WARNING_THRESHOLD', 70), // 70%
        'critical_threshold' => env('MEMORY_CRITICAL_THRESHOLD', 85), // 85%

        // Circuit breaker: reject requests when memory is critically high
        'circuit_breaker_enabled' => env('MEMORY_CIRCUIT_BREAKER_ENABLED', true),

        // Log memory usage for requests exceeding this threshold (MB)
        'log_threshold_mb' => env('MEMORY_LOG_THRESHOLD_MB', 128),
    ],

    /*
    |--------------------------------------------------------------------------
    | Request Size Limiting
    |--------------------------------------------------------------------------
    |
    | Limit request body size to prevent memory exhaustion from large payloads.
    |
    */

    'request' => [
        // Maximum request body size in megabytes
        'max_body_size_mb' => env('MAX_REQUEST_BODY_SIZE_MB', 10),

        // Maximum JSON payload depth to prevent deeply nested attacks
        'max_json_depth' => env('MAX_JSON_DEPTH', 512),

        // Routes to exclude from size checking (e.g., file upload endpoints)
        'excluded_routes' => [
            // Add route patterns here if needed
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Process Management
    |--------------------------------------------------------------------------
    |
    | Control long-running processes and prevent resource exhaustion.
    |
    */

    'process' => [
        // Maximum execution time for API requests (seconds)
        'api_max_execution_time' => env('API_MAX_EXECUTION_TIME', 30),

        // Maximum execution time for web requests (seconds)
        'web_max_execution_time' => env('WEB_MAX_EXECUTION_TIME', 60),

        // Kill Chrome/Puppeteer processes after this many seconds
        'screenshot_timeout' => env('SCREENSHOT_TIMEOUT', 30),

        // Maximum concurrent screenshot processes
        'max_concurrent_screenshots' => env('MAX_CONCURRENT_SCREENSHOTS', 2),

        // Auto-cleanup Chrome processes older than this (seconds)
        'chrome_process_max_age' => env('CHROME_PROCESS_MAX_AGE', 300), // 5 minutes

        // Auto-cleanup Puppeteer temp dirs older than this (seconds)
        'chrome_temp_dir_max_age' => env('CHROME_TEMP_DIR_MAX_AGE', 3600), // 1 hour
    ],

    /*
    |--------------------------------------------------------------------------
    | Response Headers
    |--------------------------------------------------------------------------
    |
    | Security headers to add to all responses.
    |
    */

    'headers' => [
        'X-Frame-Options' => 'SAMEORIGIN',
        'X-Content-Type-Options' => 'nosniff',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
    ],

    /*
    |--------------------------------------------------------------------------
    | Health Check Configuration
    |--------------------------------------------------------------------------
    |
    | Configure health check endpoint behavior.
    |
    */

    'health_check' => [
        // Check memory usage in health endpoint
        'check_memory' => env('HEALTH_CHECK_MEMORY', true),

        // Check database connectivity
        'check_database' => env('HEALTH_CHECK_DATABASE', true),

        // Check cache connectivity
        'check_cache' => env('HEALTH_CHECK_CACHE', true),
    ],

];
