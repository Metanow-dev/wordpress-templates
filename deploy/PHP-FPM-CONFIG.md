# PHP-FPM Configuration for 350 WordPress Sites

This guide provides optimal PHP-FPM configuration to prevent memory exhaustion when hosting 350 WordPress sites under CloudLinux LVE constraints.

## Problem Summary

- **Total Sites**: 350 WordPress installations
- **Previous LVE Limit**: 1GB total memory
- **Previous PHP memory_limit**: 512MB per process
- **Result**: Only 2 concurrent requests could max out the limit → OOM kills

## Solution: Resource-Constrained Configuration

### 1. PHP-FPM Pool Configuration

Edit: `/etc/php/8.3/fpm/pool.d/www.conf`

```ini
; Process Manager Configuration
[www]

; User/Group
user = www-data
group = www-data

; Process management (CRITICAL for memory control)
; Use 'dynamic' instead of 'ondemand' for better predictability
pm = dynamic

; Maximum number of child processes (MOST IMPORTANT SETTING)
; Formula: (LVE Memory Limit) / (PHP memory_limit) = max safe processes
; Example: 2GB / 256MB = ~7-8 processes (use 5 for safety margin)
pm.max_children = 5

; Number of processes started on PHP-FPM start
pm.start_servers = 2

; Minimum number of idle processes
pm.min_spare_servers = 1

; Maximum number of idle processes
pm.max_spare_servers = 3

; Maximum requests per process before restart (prevents memory leaks)
pm.max_requests = 500

; Process idle timeout (kill idle processes after 10s)
pm.process_idle_timeout = 10s

; Maximum time to process request (prevent runaway scripts)
request_terminate_timeout = 30s

; Slow log (helps identify bottleneck scripts)
slowlog = /var/log/php-fpm-slow.log
request_slowlog_timeout = 5s

; Status page (for monitoring)
pm.status_path = /fpm-status

; Ping page (for health checks)
ping.path = /fpm-ping
ping.response = pong
```

### 2. PHP Configuration

Edit: `/etc/php/8.3/fpm/php.ini`

```ini
; Memory Management
; Reduce from 512M to prevent single process exhaustion
memory_limit = 256M

; Execution Time
; Shorter timeout to kill runaway scripts faster
max_execution_time = 30
max_input_time = 30

; Upload Limits (reduced to save memory)
upload_max_filesize = 10M
post_max_size = 10M

; Resource Limits
max_input_vars = 3000
max_input_nesting_level = 64

; Realpath Cache (improves performance with 350 sites)
realpath_cache_size = 4M
realpath_cache_ttl = 7200

; OPcache (critical for performance)
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 10000
opcache.revalidate_freq = 2
opcache.validate_timestamps = 1

; Error Reporting (production)
display_errors = Off
log_errors = On
error_log = /var/log/php-fpm-errors.log
```

### 3. Calculate Safe Limits

Use this formula to determine your settings:

```
Available Memory = LVE Limit OR (Total RAM - System Overhead)
PHP memory_limit = Available Memory / Desired Concurrent Requests
pm.max_children = Available Memory / PHP memory_limit (with 20% safety margin)
```

#### Examples:

**Scenario A: 2GB LVE Limit (after your increase)**
```
Available Memory: 2GB (2048MB)
Target: 6 concurrent requests
PHP memory_limit: 2048 / 6 = ~340MB → Use 256MB for safety
pm.max_children: 2048 / 256 = 8 → Use 5 (with safety margin)
```

**Scenario B: 4GB LVE Limit (recommended)**
```
Available Memory: 4GB (4096MB)
Target: 12 concurrent requests
PHP memory_limit: 4096 / 12 = ~340MB → Use 256MB
pm.max_children: 4096 / 256 = 16 → Use 12 (with safety margin)
```

**Scenario C: Conservative (1GB limit)**
```
Available Memory: 1GB (1024MB)
Target: 3 concurrent requests
PHP memory_limit: 1024 / 3 = ~340MB → Use 256MB
pm.max_children: 1024 / 256 = 4 → Use 3 (with safety margin)
```

### 4. Per-Site PHP Configuration (Optional)

If some WordPress sites are particularly large (like your 4.4GB `axelsons_spa`), consider per-site limits:

Create: `/var/www/vhosts/wp-templates.metanow.dev/httpdocs/templates/axelsons_spa/.user.ini`

```ini
memory_limit = 128M
max_execution_time = 20
post_max_size = 5M
upload_max_filesize = 5M
```

This reduces resource usage for heavy sites without affecting others.

### 5. Nginx Configuration (Additional Layer)

Add request limiting at Nginx level for extra protection:

Edit: `/etc/nginx/nginx.conf` or site config

```nginx
http {
    # Limit request rate per IP
    limit_req_zone $binary_remote_addr zone=wp_limit:10m rate=30r/m;
    limit_req_status 429;

    # Limit concurrent connections per IP
    limit_conn_zone $binary_remote_addr zone=conn_limit:10m;

    # Connection timeouts
    client_body_timeout 12s;
    client_header_timeout 12s;
    keepalive_timeout 15s;
    send_timeout 10s;

    # Buffer sizes (prevent large request memory allocation)
    client_body_buffer_size 10K;
    client_header_buffer_size 1k;
    client_max_body_size 10M;
    large_client_header_buffers 2 1k;
}

server {
    # Apply limits to WordPress routes
    location ~ ^/[^/]+/ {
        limit_req zone=wp_limit burst=10 nodelay;
        limit_conn conn_limit 5;

        # Your existing WordPress proxy configuration
        # ...
    }
}
```

### 6. Monitoring Configuration

Add monitoring to detect issues early:

#### A. PHP-FPM Status Page

Already configured in pool config above. Access via:
```bash
curl http://localhost/fpm-status
```

Returns:
```
pool:                 www
process manager:      dynamic
start time:           28/Oct/2025:10:00:00 +0000
start since:          1200
accepted conn:        1500
listen queue:         0
max listen queue:     0
listen queue len:     0
idle processes:       2
active processes:     3
total processes:      5
max active processes: 5
max children reached: 0
slow requests:        0
```

**Key Metrics to Monitor**:
- `max children reached`: Should be 0 (if >0, increase pm.max_children OR reduce traffic)
- `listen queue`: Should be 0 (if >0, requests are queuing → need more children OR reduce traffic)
- `slow requests`: Identify bottleneck scripts

#### B. Automated Monitoring Script

Create: `/usr/local/bin/monitor-php-fpm.sh`

```bash
#!/bin/bash

# Check if max_children is being hit
STATUS=$(curl -s http://localhost/fpm-status)
MAX_CHILDREN_REACHED=$(echo "$STATUS" | grep "max children reached" | awk '{print $4}')

if [ "$MAX_CHILDREN_REACHED" -gt 0 ]; then
    echo "WARNING: PHP-FPM max_children limit reached $MAX_CHILDREN_REACHED times"
    echo "Current status:"
    echo "$STATUS"

    # Send alert (configure your notification method)
    # mail -s "PHP-FPM Alert" admin@example.com <<< "$STATUS"
fi

# Check memory usage
MEMORY_USAGE=$(free | grep Mem | awk '{printf "%.0f", $3/$2 * 100}')
if [ "$MEMORY_USAGE" -gt 85 ]; then
    echo "WARNING: Memory usage at ${MEMORY_USAGE}%"
fi
```

Add to crontab:
```bash
# Check every 5 minutes
*/5 * * * * /usr/local/bin/monitor-php-fpm.sh >> /var/log/php-fpm-monitor.log 2>&1
```

### 7. CloudLinux LVE Configuration (if applicable)

If using CloudLinux, adjust LVE limits for your hosting account:

```bash
# View current LVE limits
lvectl list

# Set memory limit (example: 2GB)
lvectl set 10003 --pmem=2048M --vmem=2048M

# Set CPU limit (example: 200% = 2 cores)
lvectl set 10003 --cpu=200

# Set IO limit
lvectl set 10003 --io=1024

# Apply changes
lvectl apply all
```

**Recommended LVE Settings for 350 Sites**:
- **PMEM**: 2GB minimum, 4GB recommended
- **VMEM**: Same as PMEM
- **CPU**: 200-400% (2-4 cores)
- **IO**: 1024 KB/s
- **NPROC**: 100 (processes)
- **EP** (Entry Processes): 20-30

### 8. WordPress Optimization (Per Site)

For each of the 350 WordPress sites, implement:

#### A. Object Caching (Critical!)

Install Redis Object Cache plugin on all sites:

```bash
# Install plugin in each site
cd /var/www/vhosts/wp-templates.metanow.dev/httpdocs/templates
for site in */; do
    cd "$site"
    if [ -f "public/wp-config.php" ] || [ -f "httpdocs/wp-config.php" ]; then
        # Determine docroot
        docroot="public"
        [ ! -f "$docroot/wp-config.php" ] && docroot="httpdocs"

        wp --path="$docroot" plugin install redis-cache --activate 2>/dev/null || true
        wp --path="$docroot" redis enable 2>/dev/null || true
    fi
    cd ..
done
```

#### B. Disable Unused Features

Add to each `wp-config.php`:

```php
// Reduce memory per WordPress request
define('WP_MEMORY_LIMIT', '128M');
define('WP_MAX_MEMORY_LIMIT', '256M');

// Disable resource-intensive features
define('WP_POST_REVISIONS', 5);
define('AUTOSAVE_INTERVAL', 300);
define('WP_CRON_LOCK_TIMEOUT', 60);

// Disable if not needed
define('DISALLOW_FILE_EDIT', true);
```

#### C. Identify Resource Hogs

Create a script to identify problematic sites:

```bash
#!/bin/bash
# /usr/local/bin/find-heavy-sites.sh

echo "WordPress Site Size Report"
echo "==========================="
cd /var/www/vhosts/wp-templates.metanow.dev/httpdocs/templates

for site in */; do
    size=$(du -sh "$site" 2>/dev/null | awk '{print $1}')
    echo "$size - $site"
done | sort -rh | head -20
```

Run monthly and consider:
- Moving sites >2GB to separate server
- Cleaning up unused media files
- Implementing CDN for large sites

### 9. Restart Services

After making configuration changes:

```bash
# Test PHP-FPM configuration
sudo php-fpm8.3 -t

# Restart PHP-FPM
sudo systemctl restart php8.3-fpm

# Verify it's running
sudo systemctl status php8.3-fpm

# Restart Nginx
sudo nginx -t && sudo systemctl restart nginx

# Monitor for errors
sudo tail -f /var/log/php8.3-fpm.log
sudo tail -f /var/log/nginx/error.log
```

### 10. Validation Checklist

After deployment, verify:

```bash
# 1. Check PHP-FPM is using new config
ps aux | grep php-fpm
# Should show max 5 processes (pm.max_children = 5)

# 2. Check memory_limit
php -i | grep memory_limit
# Should show: memory_limit => 256M

# 3. Check max_execution_time
php -i | grep max_execution_time
# Should show: max_execution_time => 30

# 4. Monitor PHP-FPM status
watch -n 2 'curl -s http://localhost/fpm-status'

# 5. Monitor system memory
watch -n 2 'free -h'

# 6. Check for OOM kills
dmesg | grep -i "killed process" | tail -20
journalctl -k | grep -i "out of memory" | tail -20

# 7. Test health endpoint
curl https://wp-templates.metanow.dev/api/health/detailed | jq
```

### 11. Emergency Procedures

If OOM kills occur again:

```bash
# 1. Immediately reduce max_children
sudo nano /etc/php/8.3/fpm/pool.d/www.conf
# Set: pm.max_children = 3
sudo systemctl restart php8.3-fpm

# 2. Identify current memory usage
ps aux --sort=-%mem | head -20

# 3. Kill runaway PHP processes
sudo killall -9 php-fpm8.3
sudo systemctl restart php8.3-fpm

# 4. Check for specific problematic site
sudo tail -100 /var/log/php-fpm-slow.log
# Isolate that site or reduce its resources

# 5. Enable emergency rate limiting in Nginx
sudo nano /etc/nginx/sites-available/wp-templates
# Add: limit_req zone=wp_limit burst=5;
sudo systemctl reload nginx
```

---

## Quick Reference Card

Print and keep this handy:

```
╔════════════════════════════════════════════════════════════╗
║         PHP-FPM Memory Management Quick Reference          ║
╠════════════════════════════════════════════════════════════╣
║ Current Settings (Recommended)                             ║
║ • PHP memory_limit: 256M                                   ║
║ • pm.max_children: 5                                       ║
║ • LVE PMEM: 2GB                                            ║
║ • Max concurrent requests: ~5-7                            ║
╠════════════════════════════════════════════════════════════╣
║ Key Files                                                  ║
║ • Pool: /etc/php/8.3/fpm/pool.d/www.conf                   ║
║ • PHP: /etc/php/8.3/fpm/php.ini                            ║
║ • Logs: /var/log/php8.3-fpm.log                            ║
║ • Slow: /var/log/php-fpm-slow.log                          ║
╠════════════════════════════════════════════════════════════╣
║ Commands                                                   ║
║ • Restart: systemctl restart php8.3-fpm                    ║
║ • Status: curl http://localhost/fpm-status                 ║
║ • Test: php-fpm8.3 -t                                      ║
║ • Monitor: watch 'ps aux | grep php-fpm | wc -l'           ║
╠════════════════════════════════════════════════════════════╣
║ Alert Thresholds                                           ║
║ • Memory >85%: CRITICAL                                    ║
║ • max_children_reached >0: WARNING                         ║
║ • listen_queue >0: WARNING                                 ║
║ • slow_requests >100: INVESTIGATE                          ║
╠════════════════════════════════════════════════════════════╣
║ Emergency Actions                                          ║
║ 1. Reduce pm.max_children to 3                             ║
║ 2. Restart PHP-FPM                                         ║
║ 3. Check slow log for problematic sites                    ║
║ 4. Enable Nginx rate limiting                              ║
╚════════════════════════════════════════════════════════════╝
```

---

## Additional Recommendations

1. **Consider Separate Server for Large Sites**
   - Sites >2GB (axelsons_spa, caravanscampers, gpautomation)
   - Isolate to prevent affecting smaller sites

2. **Implement CDN**
   - Offload static assets (images, CSS, JS)
   - Reduces PHP-FPM load significantly
   - Cloudflare (free), AWS CloudFront, or others

3. **Database Optimization**
   - Ensure MySQL query cache is enabled
   - Add indexes to frequently queried tables
   - Consider read replicas for high-traffic sites

4. **Upgrade Plan**
   - Current: 350 sites on 2GB LVE
   - Target: 4GB LVE for headroom
   - Alternative: Split to 2 servers (175 sites each)

---

**Last Updated**: 2025-10-28
**Tested On**: PHP 8.3, Nginx 1.24, CloudLinux 8
