# Security & Performance Features

This document describes the security and performance features implemented to prevent memory exhaustion, resource abuse, and service disruptions in the WordPress Templates hosting platform.

## Table of Contents

1. [Overview](#overview)
2. [Rate Limiting](#rate-limiting)
3. [Memory Management](#memory-management)
4. [Request Size Limiting](#request-size-limiting)
5. [Queue-Based Processing](#queue-based-processing)
6. [Health Monitoring](#health-monitoring)
7. [Chrome/Puppeteer Process Management](#chromepuppeteer-process-management)
8. [Configuration](#configuration)
9. [Production Deployment](#production-deployment)
10. [Troubleshooting](#troubleshooting)

---

## Overview

This platform hosts 350+ WordPress sites and provides a catalog/management layer through Laravel. The following security measures prevent the memory exhaustion issues experienced in production:

### Problem Statement (Resolved)

**Previous Issue**: CloudLinux LVE Memory Limit Exhausted
- 350 WordPress sites hitting 1GB memory limit
- PHP processes consuming 200-400MB each
- Multiple OOM (Out of Memory) kills
- System CPU spikes to 100%
- Connection reset errors

**Solution**: Multi-layered security and resource management approach

---

## Rate Limiting

### Purpose
Prevent DDoS attacks and resource exhaustion by limiting the number of requests per IP address.

### Implementation

Three tiers of rate limiting:

1. **API Routes** (`rate.limit:api`)
   - Default: 60 requests per minute
   - Applied to: All API endpoints (except health checks)
   - Use case: AI classification, catalog queries

2. **Web Routes** (`rate.limit:web`)
   - Default: 1000 requests per minute
   - Applied to: Template gallery and detail pages
   - Use case: User browsing, searches

3. **Heavy Operations** (`rate.limit:heavy`)
   - Default: 10 requests per 5 minutes
   - Applied to: Screenshot generation, n8n webhook triggers
   - Use case: Resource-intensive operations

### Configuration

```env
# Enable/disable rate limiting
API_RATE_LIMIT_ENABLED=true
WEB_RATE_LIMIT_ENABLED=true
HEAVY_RATE_LIMIT_ENABLED=true

# Set limits (requests per window)
API_RATE_LIMIT=60
WEB_RATE_LIMIT=1000
HEAVY_RATE_LIMIT=10

# Time windows (minutes)
API_RATE_LIMIT_DECAY=1
WEB_RATE_LIMIT_DECAY=1
HEAVY_RATE_LIMIT_DECAY=5

# Cache driver for rate limiting (recommended: redis)
RATE_LIMIT_CACHE_DRIVER=redis
```

### Response Headers

When rate limiting is active, all responses include:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1735423200
```

### Error Response (429 Too Many Requests)

```json
{
  "error": "Too many requests",
  "message": "Rate limit exceeded. Maximum 60 requests per 1 minute(s).",
  "retry_after": 60
}
```

### Files

- Middleware: `app/Http/Middleware/RateLimitMiddleware.php`
- Config: `config/security.php` → `rate_limiting`
- Routes: Applied in `routes/api.php` and `routes/web.php`

---

## Memory Management

### Purpose
Monitor memory usage and activate circuit breaker to prevent OOM kills.

### Features

1. **Real-time Monitoring**
   - Tracks memory usage before and after each request
   - Logs high memory consumption (>128MB per request)
   - Triggers garbage collection when needed

2. **Circuit Breaker Pattern**
   - Rejects new requests when memory usage exceeds critical threshold
   - Prevents cascading failures
   - Returns 503 Service Unavailable with retry-after header

3. **Automatic Garbage Collection**
   - Triggers `gc_collect_cycles()` when memory exceeds warning threshold
   - Helps reclaim unused memory

### Configuration

```env
# Enable memory monitoring
MEMORY_MONITORING_ENABLED=true

# Thresholds (percentage of PHP memory_limit)
MEMORY_WARNING_THRESHOLD=70   # 70% - trigger GC
MEMORY_CRITICAL_THRESHOLD=85  # 85% - circuit breaker activates

# Enable circuit breaker
MEMORY_CIRCUIT_BREAKER_ENABLED=true

# Log requests exceeding this memory usage (MB)
MEMORY_LOG_THRESHOLD_MB=128
```

### Circuit Breaker Response (503 Service Unavailable)

```json
{
  "error": "Service temporarily unavailable",
  "message": "Server is experiencing high load. Please try again in a few moments.",
  "retry_after": 30
}
```

### Debug Headers (Non-Production Only)

```
X-Memory-Used-MB: 45.23
X-Memory-Peak-MB: 67.89
```

### Files

- Middleware: `app/Http/Middleware/MemoryMonitorMiddleware.php`
- Config: `config/security.php` → `memory`
- Applied globally in: `bootstrap/app.php`

---

## Request Size Limiting

### Purpose
Prevent memory exhaustion from large or deeply nested payloads.

### Features

1. **Body Size Limits**
   - Default maximum: 10MB
   - Checked via `Content-Length` header
   - Returns 413 Payload Too Large

2. **JSON Depth Limits**
   - Maximum nesting depth: 512 levels
   - Prevents deeply nested JSON attacks
   - Returns 400 Bad Request

### Configuration

```env
# Maximum request body size (MB)
MAX_REQUEST_BODY_SIZE_MB=10

# Maximum JSON nesting depth
MAX_JSON_DEPTH=512
```

### Error Responses

**Payload Too Large (413)**:
```json
{
  "error": "Payload too large",
  "message": "Request body size exceeds maximum allowed size of 10MB.",
  "content_length_mb": 15.34
}
```

**Invalid JSON Depth (400)**:
```json
{
  "error": "Invalid JSON structure",
  "message": "JSON payload exceeds maximum nesting depth of 512 levels."
}
```

### Files

- Middleware: `app/Http/Middleware/RequestSizeLimitMiddleware.php`
- Config: `config/security.php` → `request`
- Applied to: All API routes via `routes/api.php`

---

## Queue-Based Processing

### Purpose
Move heavy operations to background queue to prevent request timeout and memory issues.

### Implementation

**ClassifyTemplateJob** - AI classification via n8n webhook

Instead of synchronous HTTP calls that can timeout or hang, classification requests are queued:

```php
use App\Jobs\ClassifyTemplateJob;

// Dispatch to queue instead of blocking
ClassifyTemplateJob::dispatch($slug);
```

### Features

- **Retry Logic**: 3 attempts with exponential backoff
- **Timeout**: 120 seconds per job
- **Failure Handling**: Marks template as "needs review" on permanent failure
- **Logging**: Comprehensive logs for debugging

### Configuration

```env
# Queue connection (database recommended for simplicity)
QUEUE_CONNECTION=database

# Process management
API_MAX_EXECUTION_TIME=30
SCREENSHOT_TIMEOUT=30
MAX_CONCURRENT_SCREENSHOTS=2
```

### Running the Queue Worker

```bash
# Start queue worker
php artisan queue:work --tries=3 --timeout=120

# Or with supervisor (recommended for production)
php artisan queue:work database --sleep=3 --tries=3 --max-time=3600
```

### Files

- Job: `app/Jobs/ClassifyTemplateJob.php`
- Config: `config/security.php` → `process`

---

## Health Monitoring

### Purpose
Provide visibility into system health and resource usage.

### Endpoints

#### 1. Simple Health Check (for load balancers)

```
GET /api/health
```

**Response (200 OK)**:
```json
{
  "status": "ok",
  "timestamp": "2025-10-28T10:30:00.000000Z"
}
```

#### 2. Detailed Health Check

```
GET /api/health/detailed
```

**Response (200 OK - Healthy)**:
```json
{
  "status": "healthy",
  "checks": {
    "database": {
      "status": "healthy",
      "message": "Database connection established"
    },
    "cache": {
      "status": "healthy",
      "message": "Cache is operational",
      "driver": "redis"
    },
    "memory": {
      "status": "healthy",
      "usage_mb": 128.45,
      "peak_mb": 156.78,
      "limit_mb": 512,
      "usage_percent": 25.09
    },
    "disk": {
      "status": "healthy",
      "free_gb": 45.67,
      "total_gb": 100.00,
      "usage_percent": 54.33
    }
  },
  "app": {
    "name": "WP Templates",
    "environment": "production",
    "version": "1.0.0",
    "timestamp": "2025-10-28T10:30:00.000000Z",
    "uptime_seconds": null
  }
}
```

**Response (503 Service Unavailable - Unhealthy)**:
```json
{
  "status": "unhealthy",
  "checks": {
    "memory": {
      "status": "critical",
      "usage_mb": 485.32,
      "limit_mb": 512,
      "usage_percent": 94.79
    }
  }
}
```

### Status Levels

- **healthy**: All systems operational
- **degraded**: Some non-critical issues (warnings)
- **unhealthy**: Critical issues detected (503 status code)

### Configuration

```env
HEALTH_CHECK_MEMORY=true
HEALTH_CHECK_DATABASE=true
HEALTH_CHECK_CACHE=true
```

### Files

- Controller: `app/Http/Controllers/Api/HealthController.php`
- Routes: `routes/api.php`

---

## Chrome/Puppeteer Process Management

### Purpose
Prevent zombie Chrome/Puppeteer processes from accumulating and consuming massive amounts of RAM.

### The Problem

**Previous Production Issue**:
- 389 zombie Chrome/Puppeteer processes
- 48.4 GB RAM consumed by Chrome processes
- Processes running for days without cleanup
- Temporary directories accumulating in `/tmp`

### Solution

#### 1. Automatic Cleanup Command

```bash
# Clean up zombie Chrome processes and temp directories
php artisan chrome:cleanup --force

# Dry run to see what would be cleaned
php artisan chrome:cleanup --dry-run
```

**What it cleans**:
- Zombie Chrome/Chromium/Puppeteer processes
- Orphaned `/tmp/puppeteer_dev_chrome_profile-*` directories
- Shows memory statistics and logs all actions

#### 2. Process Limits

The memory circuit breaker and PHP-FPM limits prevent unlimited Chrome processes:
- Max 10 concurrent PHP-FPM processes
- Max 2 concurrent screenshot operations
- 30-second timeout per screenshot

#### 3. Scheduled Cleanup

Add to crontab for automatic cleanup:

```bash
# Clean up Chrome zombies every 15 minutes
*/15 * * * * cd /var/www/vhosts/wp-templates.metanow.dev/httpdocs && php artisan chrome:cleanup --force >> /var/log/chrome-cleanup.log 2>&1

# Emergency cleanup: kill processes older than 10 minutes
*/10 * * * * pkill -9 -f 'chrome.*--enable-automation' --older-than 600 2>/dev/null

# Clean old temp directories
0 * * * * find /tmp -name 'puppeteer_dev_chrome_profile-*' -type d -mmin +60 -exec rm -rf {} \; 2>/dev/null
```

### Configuration

```env
# Screenshot process management
SCREENSHOT_TIMEOUT=30
MAX_CONCURRENT_SCREENSHOTS=2

# Auto-cleanup thresholds
CHROME_PROCESS_MAX_AGE=300      # 5 minutes
CHROME_TEMP_DIR_MAX_AGE=3600    # 1 hour
```

### Monitoring Chrome Processes

```bash
# Count Chrome processes
ps aux | grep chrome | grep -v grep | wc -l

# Show Chrome memory usage
ps aux | grep chrome | awk '{sum+=$4} END {print "Chrome Memory: " sum "%"}'

# List Puppeteer temp directories
ls -d /tmp/puppeteer_dev_chrome_profile-* 2>/dev/null | wc -l
```

### Files

- Command: `app/Console/Commands/CleanupChromeProcessesCommand.php`
- Documentation: `CHROME-CLEANUP.md`
- Config: `config/security.php` → `process`

### **See CHROME-CLEANUP.md for complete documentation**

---

## Configuration

### Complete Security Configuration File

All security settings are centralized in `config/security.php`:

```php
<?php

return [
    'rate_limiting' => [
        'api' => [...],
        'web' => [...],
        'heavy' => [...],
    ],
    'memory' => [
        'monitoring_enabled' => true,
        'warning_threshold' => 70,
        'critical_threshold' => 85,
        ...
    ],
    'request' => [
        'max_body_size_mb' => 10,
        'max_json_depth' => 512,
        ...
    ],
    'process' => [...],
    'headers' => [...],
    'health_check' => [...],
];
```

### Middleware Registration

All security middleware is registered in `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'rate.limit' => \App\Http\Middleware\RateLimitMiddleware::class,
        'memory.monitor' => \App\Http\Middleware\MemoryMonitorMiddleware::class,
        'request.size.limit' => \App\Http\Middleware\RequestSizeLimitMiddleware::class,
    ]);

    // Apply memory monitoring globally
    $middleware->append(\App\Http\Middleware\MemoryMonitorMiddleware::class);
})
```

---

## Production Deployment

### Prerequisites

1. **Redis** (strongly recommended)
   ```bash
   # Install Redis
   sudo apt-get install redis-server

   # Start Redis
   sudo systemctl start redis-server
   sudo systemctl enable redis-server
   ```

2. **PHP Configuration** (adjust based on available memory)
   ```ini
   ; /etc/php/8.3/fpm/php.ini
   memory_limit = 256M  ; Reduced from 512M to prevent single process exhaustion
   max_execution_time = 30
   post_max_size = 10M
   upload_max_filesize = 10M
   ```

3. **PHP-FPM Pool Configuration**
   ```ini
   ; /etc/php/8.3/fpm/pool.d/www.conf
   pm = dynamic
   pm.max_children = 5  ; Limit concurrent PHP processes (was likely much higher)
   pm.start_servers = 2
   pm.min_spare_servers = 1
   pm.max_spare_servers = 3
   pm.max_requests = 500  ; Restart workers after 500 requests to prevent memory leaks
   ```

### Deployment Steps

1. **Update Environment Variables**
   ```bash
   cd /var/www/vhosts/wp-templates.metanow.dev/httpdocs
   nano .env
   ```

   Add/update these values:
   ```env
   CACHE_STORE=redis
   REDIS_HOST=127.0.0.1
   REDIS_PORT=6379

   RATE_LIMIT_CACHE_DRIVER=redis
   MEMORY_MONITORING_ENABLED=true
   MEMORY_CIRCUIT_BREAKER_ENABLED=true

   API_RATE_LIMIT=60
   WEB_RATE_LIMIT=500  # Reduced from 1000 for safety
   HEAVY_RATE_LIMIT=5  # Very conservative
   ```

2. **Clear Caches**
   ```bash
   ./vendor/bin/sail artisan config:cache
   ./vendor/bin/sail artisan route:cache
   ./vendor/bin/sail artisan view:cache
   ```

3. **Restart Services**
   ```bash
   sudo systemctl restart php8.3-fpm
   sudo systemctl restart nginx
   ```

4. **Verify Health**
   ```bash
   curl https://wp-templates.metanow.dev/api/health/detailed
   ```

### Monitoring

Set up monitoring alerts for:

1. **Memory Critical** - `/api/health/detailed` returns `status: "unhealthy"`
2. **Rate Limit Triggers** - Check logs for `Rate limit exceeded`
3. **Circuit Breaker Activations** - Check logs for `Circuit breaker activated`
4. **Job Failures** - Monitor `failed_jobs` table

### Log Locations

```bash
# Laravel logs
storage/logs/laravel.log

# PHP-FPM logs
/var/log/php8.3-fpm.log

# Nginx logs
/var/log/nginx/access.log
/var/log/nginx/error.log

# System logs (for OOM kills)
dmesg | grep -i "killed process"
journalctl -k | grep -i "out of memory"
```

---

## Troubleshooting

### Issue: 503 Service Unavailable

**Cause**: Circuit breaker activated due to high memory usage

**Solution**:
1. Check memory usage: `curl /api/health/detailed`
2. Review logs: `tail -f storage/logs/laravel.log | grep "Circuit breaker"`
3. Identify memory-hungry requests
4. Consider increasing PHP memory_limit or reducing concurrent processes

### Issue: 429 Too Many Requests

**Cause**: Rate limit exceeded

**Solution**:
1. Verify legitimate traffic vs attack
2. Whitelist known IPs if needed (modify middleware)
3. Adjust rate limits in `.env` if too restrictive

### Issue: Queue Jobs Not Processing

**Cause**: Queue worker not running

**Solution**:
```bash
# Check if queue worker is running
ps aux | grep "queue:work"

# Start queue worker
php artisan queue:work --tries=3

# Or restart with supervisor
sudo supervisorctl restart laravel-worker:*
```

### Issue: High Memory Usage Persists

**Cause**: Memory leak in WordPress sites or PHP-FPM configuration

**Solution**:
1. Identify problematic WordPress sites:
   ```bash
   tail -f storage/logs/laravel.log | grep "High memory usage"
   ```
2. Check PHP-FPM max_children: `pm.max_children = 5` (reduce if needed)
3. Enable PHP-FPM slow log to find bottlenecks
4. Consider isolating large WordPress sites to separate server

### Issue: Database Queries Slow

**Cause**: Unbounded queries or missing indexes

**Solution**:
1. Check stats endpoint response time
2. Add indexes to `templates` table:
   ```sql
   CREATE INDEX idx_primary_category ON templates(primary_category);
   CREATE INDEX idx_classification_source ON templates(classification_source);
   CREATE INDEX idx_needs_review ON templates(needs_review);
   ```
3. Enable query logging: `DB::enableQueryLog()`

---

## Testing

### Test Rate Limiting

```bash
# Test API rate limit (should hit limit after 60 requests)
for i in {1..70}; do
  curl -H "X-Api-Token: $API_TOKEN" \
       https://wp-templates.metanow.dev/api/catalog
  echo "Request $i"
done
```

### Test Memory Circuit Breaker

```bash
# Monitor health while generating screenshots (memory-intensive)
watch -n 2 'curl -s https://wp-templates.metanow.dev/api/health/detailed | jq .checks.memory'
```

### Test Request Size Limiting

```bash
# Create 15MB JSON payload (should be rejected)
dd if=/dev/urandom bs=1M count=15 | base64 | \
  curl -X POST \
       -H "Content-Type: application/json" \
       -H "X-Api-Token: $API_TOKEN" \
       -d @- \
       https://wp-templates.metanow.dev/api/templates/test/classification
```

---

## Security Checklist

- [ ] Redis installed and running
- [ ] PHP memory_limit reduced to 256M or less
- [ ] PHP-FPM max_children set to 5 or less
- [ ] Rate limiting enabled in `.env`
- [ ] Memory monitoring enabled
- [ ] Circuit breaker enabled
- [ ] Queue worker running (with supervisor)
- [ ] Health check endpoints accessible
- [ ] Monitoring/alerts configured
- [ ] Logs rotation configured
- [ ] Nginx rate limiting configured (optional additional layer)
- [ ] Firewall rules configured

---

## Additional Resources

- [Laravel Rate Limiting Documentation](https://laravel.com/docs/11.x/rate-limiting)
- [Laravel Queues Documentation](https://laravel.com/docs/11.x/queues)
- [PHP-FPM Configuration Best Practices](https://www.php.net/manual/en/install.fpm.configuration.php)
- [CloudLinux LVE Documentation](https://docs.cloudlinux.com/lve_manager/)

---

## Support

For issues or questions:

1. Check logs: `storage/logs/laravel.log`
2. Review health endpoint: `/api/health/detailed`
3. Check system resources: `htop`, `free -h`, `df -h`
4. Contact DevOps team with logs and health status

---

**Last Updated**: 2025-10-28
**Version**: 1.0.0
