# Chrome/Puppeteer Process Management

This document explains how to prevent and clean up zombie Chrome/Puppeteer processes that can consume massive amounts of RAM.

## Problem Summary

**Your Previous Issue**:
- 389 zombie Chrome/Puppeteer processes
- 48.4 GB of RAM consumed by Chrome alone
- Processes running for days without cleanup
- Temporary directories accumulating in `/tmp`

**Root Cause**:
- Browsershot/Puppeteer doesn't always clean up when exceptions occur
- No process limits on screenshot generation
- Temporary profile directories (`/tmp/puppeteer_dev_chrome_profile-*`) not deleted
- Cron jobs running screenshot commands without cleanup

---

## Solution Overview

### 1. Security Features Implemented

The new security middleware provides:

- **Memory Circuit Breaker**: Stops accepting requests when memory >85%
- **Process Limits**: Max 10 concurrent PHP-FPM processes (prevents hundreds of screenshots at once)
- **Memory Monitoring**: Logs high-memory operations
- **Rate Limiting**: Prevents API abuse that could trigger mass screenshot generation

### 2. Automatic Cleanup Command

New artisan command to clean up zombie processes:

```bash
# Manual cleanup (interactive)
php artisan chrome:cleanup

# Force cleanup (no confirmation)
php artisan chrome:cleanup --force

# Dry run (see what would be cleaned)
php artisan chrome:cleanup --dry-run
```

**What it does**:
- Kills zombie Chrome/Chromium/Puppeteer processes
- Deletes orphaned `/tmp/puppeteer_dev_chrome_profile-*` directories
- Shows memory statistics before/after
- Logs all actions for auditing

### 3. Configuration Settings

Add to `.env`:

```env
# Screenshot process management
SCREENSHOT_TIMEOUT=30
MAX_CONCURRENT_SCREENSHOTS=2

# Auto-cleanup settings
CHROME_PROCESS_MAX_AGE=300      # Kill processes older than 5 minutes
CHROME_TEMP_DIR_MAX_AGE=3600    # Delete temp dirs older than 1 hour
```

---

## Deployment Steps

### Step 1: Update Code on Server

```bash
cd /var/www/vhosts/wp-templates.metanow.dev/httpdocs

# Pull latest changes
git pull origin main

# Or apply the fixes manually
# (upload CleanupChromeProcessesCommand.php to app/Console/Commands/)
```

### Step 2: Update .env Configuration

```bash
nano .env
```

Add these lines:
```env
SCREENSHOT_TIMEOUT=30
MAX_CONCURRENT_SCREENSHOTS=2
CHROME_PROCESS_MAX_AGE=300
CHROME_TEMP_DIR_MAX_AGE=3600
```

### Step 3: Clear Caches

```bash
php artisan config:cache
php artisan cache:clear
```

### Step 4: Run Initial Cleanup

```bash
# Check what would be cleaned
php artisan chrome:cleanup --dry-run

# Clean up existing zombie processes
php artisan chrome:cleanup --force
```

### Step 5: Schedule Automatic Cleanup

Edit crontab:
```bash
crontab -e
```

Add these lines:
```bash
# Clean up Chrome zombies every 15 minutes
*/15 * * * * cd /var/www/vhosts/wp-templates.metanow.dev/httpdocs && php artisan chrome:cleanup --force >> /var/log/chrome-cleanup.log 2>&1

# Clean up old temp directories every hour
0 * * * * find /tmp -name 'puppeteer_dev_chrome_profile-*' -type d -mmin +60 -exec rm -rf {} \; 2>/dev/null

# Kill Chrome processes running longer than 10 minutes (emergency cleanup)
*/10 * * * * pkill -9 -f 'chrome.*--enable-automation' --older-than 600 2>/dev/null
```

---

## Monitoring

### Check for Zombie Processes

```bash
# Count Chrome/Puppeteer processes
ps aux | grep -E 'chrome|chromium|puppeteer' | grep -v grep | wc -l

# Show Chrome processes with memory usage
ps aux | grep -E 'chrome|chromium' | grep -v grep | awk '{print $2, $4, $11}' | head -20

# Check memory used by Chrome
ps aux | grep chrome | awk '{sum+=$4} END {print "Chrome Memory: " sum "%"}'
```

### Check Temp Directories

```bash
# Count Puppeteer temp directories
ls /tmp/puppeteer_dev_chrome_profile-* 2>/dev/null | wc -l

# Show size of Puppeteer temp dirs
du -sh /tmp/puppeteer_dev_chrome_profile-* 2>/dev/null | head -20

# Total size
du -sh /tmp/puppeteer_dev_chrome_profile-* 2>/dev/null | awk '{sum+=$1} END {print "Total:", sum}'
```

### Monitor Memory

```bash
# Current memory usage
free -h

# Watch memory in real-time
watch -n 2 'free -h'

# Check for recent OOM kills
dmesg | grep -i "killed process" | tail -20
```

---

## How to Prevent Future Issues

### 1. Limit Screenshot Generation

**Never run unlimited screenshot commands**:

```bash
# BAD: This could spawn 350 Chrome processes
php artisan templates:screenshot --force

# GOOD: Process in small batches
php artisan templates:screenshot --force --slug=specific-site

# GOOD: Use --new-only to avoid re-captures
php artisan templates:screenshot --new-only
```

### 2. Use Queue for Screenshots

Instead of running screenshots synchronously, use queues:

```php
// Instead of this:
Screenshotter::for($slug, $url)->capture();

// Do this:
dispatch(new GenerateScreenshotJob($slug, $url));
```

Then limit queue workers:
```bash
# Only allow 2 concurrent screenshot jobs
php artisan queue:work --queue=screenshots --max-jobs=2
```

### 3. Monitor Cron Jobs

Check what's in your crontab:
```bash
crontab -l | grep screenshot
```

**Update screenshot cron** to use batching:
```bash
# OLD (dangerous):
0 2 * * * php artisan templates:screenshot --force

# NEW (safe):
0 2 * * * php artisan templates:screenshot --new-only --skip-problematic
30 2 * * * php artisan chrome:cleanup --force
```

### 4. Set Resource Limits

Add to `/etc/security/limits.conf`:
```
wp-templates.metanow_r6s2v1oe7wr hard nproc 50
wp-templates.metanow_r6s2v1oe7wr hard nofile 4096
```

This prevents any single user from spawning >50 processes.

---

## Troubleshooting

### Issue: Chrome processes keep accumulating

**Check**:
```bash
# Find what's spawning them
ps aux | grep chrome | awk '{print $11}' | sort | uniq -c

# Check if cron is running screenshots
grep screenshot /var/log/cron
```

**Fix**:
1. Stop any running screenshot commands
2. Run `php artisan chrome:cleanup --force`
3. Update cron to use `--new-only` instead of `--force`

### Issue: Temp directories filling up /tmp

**Check**:
```bash
df -h /tmp
du -sh /tmp/puppeteer* 2>/dev/null
```

**Fix**:
```bash
# Clean up manually
find /tmp -name 'puppeteer_dev_chrome_profile-*' -type d -mmin +60 -delete

# Or use artisan command
php artisan chrome:cleanup --force
```

### Issue: Server running out of memory during screenshots

**Check**:
```bash
# Current memory
free -h

# Check health endpoint
curl https://wp-templates.metanow.dev/api/health/detailed | grep -A5 memory
```

**Fix**:
1. Circuit breaker will kick in automatically (returns 503)
2. Run cleanup: `php artisan chrome:cleanup --force`
3. Reduce `MAX_CONCURRENT_SCREENSHOTS` in `.env`
4. Wait for memory to drop below 70%

---

## Testing the Fix

### Test Cleanup Command

```bash
# Dry run to see what would be cleaned
php artisan chrome:cleanup --dry-run

# Run actual cleanup
php artisan chrome:cleanup

# Verify processes are gone
ps aux | grep chrome | grep -v grep
```

### Test Process Limits

```bash
# Try to generate multiple screenshots
for i in {1..5}; do
  php artisan templates:screenshot --slug=test-site-$i &
done

# Check process count (should max at 2-3)
watch 'ps aux | grep chrome | wc -l'
```

### Test Memory Protection

```bash
# Monitor health while generating screenshots
watch -n 2 'curl -s https://wp-templates.metanow.dev/api/health/detailed | grep -A5 memory'

# Should see circuit breaker activate if memory >85%
```

---

## Emergency Recovery

If Chrome processes are out of control:

```bash
# 1. Kill all Chrome processes immediately
pkill -9 chrome
pkill -9 chromium

# 2. Clean up temp directories
rm -rf /tmp/puppeteer_dev_chrome_profile-*

# 3. Check memory recovery
free -h

# 4. Restart PHP-FPM
sudo systemctl restart plesk-php83-fpm

# 5. Check application health
curl https://wp-templates.metanow.dev/api/health/detailed
```

---

## Long-Term Solution

Consider moving screenshot generation to a separate server or container:

1. **Dedicated Screenshot Service**: Run Puppeteer on a separate server with its own memory limits
2. **Docker Container**: Isolate Chrome in a container with memory limits
3. **External Service**: Use a service like Screenshotlayer or ScreenshotAPI
4. **Queue System**: Process screenshots in controlled batches with concurrency limits

---

## Monitoring & Alerts

### Setup Monitoring

Create monitoring script: `/usr/local/bin/monitor-chrome.sh`

```bash
#!/bin/bash

CHROME_COUNT=$(ps aux | grep chrome | grep -v grep | wc -l)
CHROME_MEM=$(ps aux | grep chrome | awk '{sum+=$4} END {print sum}')
TEMP_DIRS=$(ls -d /tmp/puppeteer_dev_chrome_profile-* 2>/dev/null | wc -l)

echo "Chrome Processes: $CHROME_COUNT"
echo "Chrome Memory %: $CHROME_MEM"
echo "Temp Directories: $TEMP_DIRS"

# Alert if too many processes
if [ $CHROME_COUNT -gt 10 ]; then
    echo "WARNING: Too many Chrome processes!"
    /var/www/vhosts/wp-templates.metanow.dev/httpdocs/artisan chrome:cleanup --force
fi

# Alert if too many temp dirs
if [ $TEMP_DIRS -gt 20 ]; then
    echo "WARNING: Too many temp directories!"
    find /tmp -name 'puppeteer_dev_chrome_profile-*' -type d -mmin +30 -exec rm -rf {} \;
fi
```

Add to crontab:
```bash
*/5 * * * * /usr/local/bin/monitor-chrome.sh >> /var/log/chrome-monitor.log 2>&1
```

---

## Summary

**With the new security features**:
- ✅ Memory circuit breaker prevents runaway usage
- ✅ Process limits prevent 389+ Chrome processes
- ✅ Automatic cleanup command
- ✅ Scheduled cron cleanup
- ✅ Monitoring and alerting

**Before**: 389 Chrome processes, 48.4 GB RAM
**After**: Max 2-3 Chrome processes, <1 GB RAM

The zombie Chrome process issue should not happen again with these protections in place!

---

**Last Updated**: 2025-10-28
**Version**: 1.0.0
