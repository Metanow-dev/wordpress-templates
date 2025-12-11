# WordPress Templates - Maintenance Guide

This guide covers database maintenance and memory optimization features for the WordPress Templates Catalog.

## Table of Contents
- [Garbage Collection](#garbage-collection)
- [Memory Optimization](#memory-optimization)
- [Troubleshooting](#troubleshooting)

---

## Garbage Collection

### Overview

The garbage collector maintains database integrity by identifying and removing templates that no longer exist in the filesystem.

### When Templates Become Orphaned

Templates become orphaned when:
- WordPress installations are manually deleted from the filesystem
- Template directories are moved or renamed
- `wp-config.php` files are removed
- Bulk cleanup operations remove multiple sites

Without garbage collection, these entries remain in the database, causing:
- Incorrect template counts
- Broken screenshot links
- Confusion about active templates
- Wasted database space

### Using the Garbage Collector

#### Preview Mode (Safe)

Always run in dry-run mode first to see what would be deleted:

```bash
php artisan templates:gc --dry-run
```

Output example:
```
Scanning templates root: /var/www/vhosts/megasandboxs.com/httpdocs/templates
DRY RUN MODE - No changes will be made

Found 150 templates in database
  ORPHANED: old-site-123 (directory not found or no wp-config.php)
  ORPHANED: test-template (directory not found or no wp-config.php)
  ORPHANED: removed-site (directory not found or no wp-config.php)

Results:
  Valid templates: 147
  Orphaned templates: 3

Run without --dry-run to actually delete these templates.
```

#### Delete Orphaned Templates

Remove orphaned templates from database:

```bash
php artisan templates:gc
```

You'll be asked to confirm:
```
Delete 3 orphaned template(s) from database? (yes/no) [yes]:
```

#### Clean Screenshots Too

Remove both database entries AND associated screenshot files:

```bash
php artisan templates:gc --clean-screenshots
```

This will delete:
- Database records
- Main screenshot: `screenshots/{slug}.png`
- Responsive variants: `screenshots/{slug}-{480,768,1024}.{webp,png}`

### Automatic Cleanup

The `templates:scan` command now automatically runs garbage collection after scanning:

```bash
# Scans for templates AND cleans up orphaned entries
php artisan templates:scan
```

To disable automatic cleanup:

```bash
php artisan templates:scan --no-gc
```

### Recommended Schedule

**Manual Review (Monthly):**
```bash
# Check for orphaned templates
php artisan templates:gc --dry-run
```

**Automatic Cleanup:**
The scanner already handles this automatically. No separate cron job needed.

**After Bulk Operations:**
```bash
# After deleting multiple WordPress sites
php artisan templates:gc --clean-screenshots
```

---

## Memory Optimization

### Overview

The screenshot capture system has been optimized to prevent memory exhaustion and 503 errors on shared hosting environments.

### The Problem

Browser-based screenshot capture (using Chrome/Chromium) is memory-intensive:
- Each Chrome instance uses 100-200MB RAM
- Processing multiple screenshots in sequence accumulates memory
- Without cleanup, PHP memory limit is exceeded
- Results in 503 Service Unavailable errors

### Memory Management Features

#### 1. Automatic Garbage Collection

PHP's garbage collector is triggered:
- Before each screenshot capture
- After each screenshot capture
- After any capture errors

This ensures memory is freed between operations.

#### 2. Memory Monitoring

Before each capture, the system checks current memory usage:

```php
if ($currentMemoryMB > $memoryLimit) {
    // Force cleanup and wait
    gc_collect_cycles();
    sleep(2);
}
```

#### 3. Capture Delays

Configurable delays between captures prevent server overload:

```env
SCREENSHOT_DELAY_BETWEEN_MS=3000  # 3 seconds between captures
```

This gives the system time to:
- Free browser processes
- Release memory
- Process other requests

#### 4. Error Recovery

After any screenshot error:
- Garbage collection is forced
- 1-second pause before continuing
- Prevents error cascade

### Configuration

Add to `.env` (production):

```env
# Memory limit before forcing cleanup (MB)
SCREENSHOT_MEMORY_LIMIT_MB=256

# Delay between screenshot captures (milliseconds)
SCREENSHOT_DELAY_BETWEEN_MS=3000
```

### Recommended Settings by Environment

#### Shared Hosting (Limited Resources)
```env
SCREENSHOT_MEMORY_LIMIT_MB=256
SCREENSHOT_DELAY_BETWEEN_MS=3000
```
- Conservative memory threshold
- Longer delays to reduce server load
- Use `--skip-problematic` flag

#### VPS/Dedicated Server
```env
SCREENSHOT_MEMORY_LIMIT_MB=512
SCREENSHOT_DELAY_BETWEEN_MS=2000
```
- Higher memory threshold
- Moderate delays
- Better performance

#### High-Memory Server
```env
SCREENSHOT_MEMORY_LIMIT_MB=1024
SCREENSHOT_DELAY_BETWEEN_MS=1000
```
- Maximum memory threshold
- Minimal delays
- Fast processing

### Best Practices

#### Use New-Only Flag
Only capture screenshots for new templates:

```bash
php artisan templates:screenshot --new-only
```

Benefits:
- Processes only templates without captured screenshots
- Skips unnecessary recaptures
- Reduces total processing time
- Lower memory usage

#### Skip Problematic Sites
Some sites timeout or hang during capture:

```bash
php artisan templates:screenshot --skip-problematic
```

Automatically skips known problematic sites to prevent:
- Long timeouts
- Memory accumulation
- Failed batch processing

#### Process in Batches
For large numbers of templates:

```bash
# Process first 10
php artisan templates:screenshot --new-only --limit=10

# Then next 10 after a break
php artisan templates:screenshot --new-only --limit=10 --offset=10
```

Note: Current implementation doesn't have limit/offset, but processes sequentially with built-in delays.

### Monitoring Memory Usage

Check memory during screenshot capture:

```bash
# Start capture in background
php artisan templates:screenshot --new-only &

# Monitor memory
watch -n 1 "ps aux | grep 'artisan templates:screenshot' | grep -v grep"
```

Or check logs for memory warnings:
```bash
tail -f storage/logs/laravel.log | grep -i memory
```

---

## Troubleshooting

### 503 Errors During Screenshot Capture

**Symptoms:**
- Web server returns 503 Service Unavailable
- Screenshot command stops mid-process
- Server becomes unresponsive

**Solutions:**

1. **Reduce memory limit:**
   ```env
   SCREENSHOT_MEMORY_LIMIT_MB=128  # Lower threshold
   ```

2. **Increase delays:**
   ```env
   SCREENSHOT_DELAY_BETWEEN_MS=5000  # 5 seconds
   ```

3. **Use new-only flag:**
   ```bash
   php artisan templates:screenshot --new-only --skip-problematic
   ```

4. **Increase PHP memory limit:**
   Edit `php.ini`:
   ```ini
   memory_limit = 512M
   ```

### Orphaned Templates Not Detected

**Symptoms:**
- Manual deletion doesn't clean database
- Template count doesn't match filesystem

**Solutions:**

1. **Run garbage collector manually:**
   ```bash
   php artisan templates:gc --dry-run  # Preview first
   php artisan templates:gc            # Then delete
   ```

2. **Check templates root path:**
   ```bash
   php artisan tinker
   >>> config('templates.root')
   ```

3. **Verify wp-config.php exists:**
   The scanner looks for:
   - `{slug}/public/wp-config.php`
   - `{slug}/httpdocs/wp-config.php`
   - `{slug}/wp-config.php`

### Screenshot Files Not Deleted

**Symptoms:**
- Database cleaned but files remain
- Orphaned screenshot files

**Solutions:**

1. **Use clean-screenshots flag:**
   ```bash
   php artisan templates:gc --clean-screenshots
   ```

2. **Manual cleanup:**
   ```bash
   # List orphaned screenshots
   cd screenshots/
   ls -la

   # Remove specific file
   rm slug-name.png slug-name-*.{png,webp}
   ```

### Memory Still Accumulating

**Symptoms:**
- Memory limit reached despite settings
- System still crashes

**Solutions:**

1. **Restart PHP-FPM between batches:**
   ```bash
   # Capture batch
   php artisan templates:screenshot --new-only

   # Restart PHP-FPM
   sudo systemctl restart php-fpm

   # Continue with next batch
   ```

2. **Use system cron with timeout:**
   ```bash
   # Cron with 30-minute timeout
   */30 * * * * timeout 25m php artisan templates:screenshot --new-only
   ```

3. **Process one at a time:**
   ```bash
   # Get list of templates needing screenshots
   php artisan tinker
   >>> Template::whereNull('screenshot_url')->pluck('slug')->each(function($slug) {
       shell_exec("php artisan templates:screenshot --slug={$slug}");
       sleep(10);
   });
   ```

---

## Production Recommendations

### Daily Maintenance
```bash
# Morning: Scan for new templates (includes auto-cleanup)
0 2 * * * cd /var/www/vhosts/your-domain.com/httpdocs && php artisan templates:scan

# Capture new screenshots only
0 3 * * * cd /var/www/vhosts/your-domain.com/httpdocs && php artisan templates:screenshot --new-only --skip-problematic
```

### Weekly Maintenance
```bash
# Sunday: Full screenshot refresh
0 3 * * 0 cd /var/www/vhosts/your-domain.com/httpdocs && php artisan templates:screenshot --force

# Sunday: Manual GC check (optional, scan does this)
0 4 * * 0 cd /var/www/vhosts/your-domain.com/httpdocs && php artisan templates:gc --dry-run
```

### Monthly Review
```bash
# Check for orphaned templates
php artisan templates:gc --dry-run

# Review screenshot quality
ls -lh screenshots/ | tail -20

# Check database stats
php artisan tinker
>>> Template::count()
>>> Template::whereNotNull('screenshot_url')->count()
```

---

## Summary

### Key Improvements

1. **Garbage Collection**: Automatically removes orphaned templates
2. **Memory Optimization**: Prevents 503 errors during screenshot capture
3. **Smart Processing**: Only captures new screenshots when needed
4. **Automatic Cleanup**: Scanner integrates garbage collection

### Essential Commands

```bash
# Preview orphaned templates
php artisan templates:gc --dry-run

# Clean database and files
php artisan templates:gc --clean-screenshots

# Capture new screenshots only (memory efficient)
php artisan templates:screenshot --new-only --skip-problematic

# Full scan with auto-cleanup
php artisan templates:scan
```

### Configuration

```env
# Memory management
SCREENSHOT_MEMORY_LIMIT_MB=256
SCREENSHOT_DELAY_BETWEEN_MS=3000
```

---

For more information, see:
- [README.md](README.md) - Main documentation
- [SCREENSHOT_SETUP_GUIDE.md](SCREENSHOT_SETUP_GUIDE.md) - Screenshot system setup
- [MIGRATION_CHECKLIST.md](MIGRATION_CHECKLIST.md) - Server migration guide
