# WordPress Templates - Maintenance Guide

This guide covers database maintenance and memory optimization features for the WordPress Templates Catalog.

## Table of Contents
- [Garbage Collection](#garbage-collection)
- [Memory Optimization](#memory-optimization)
- [Batch Screenshot Processing](#batch-screenshot-processing)
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

## Batch Screenshot Processing

### Overview

The batch screenshot command allows you to recapture screenshots for multiple templates at once, perfect for fixing 503 errors or updating specific sites.

### Use Cases

1. **Recapture Failed Screenshots**: Templates that had 503 errors during initial capture
2. **Update Specific Sites**: After WordPress updates or theme changes
3. **Quality Improvements**: Re-capture with different dimensions or settings
4. **Bulk Operations**: Process exported lists from database queries

### Command Syntax

```bash
php artisan templates:batch-screenshot [options]
```

**Options:**
- `--slugs=slug1,slug2,slug3` - Comma-separated list of template slugs
- `--file=path/to/file` - Read slugs from text or CSV file
- `--column=name` - CSV column to read (default: first column)
- `--force` - Force recapture even if screenshot exists
- `--fullpage` - Capture full page instead of viewport
- `--w=1200` - Viewport width (default: 1200)
- `--h=800` - Viewport height (default: 800)
- `--delay=3` - Seconds between captures (default: 3)
- `--continue-on-error` - Keep processing even if some captures fail

### Basic Usage

#### From Comma-Separated List

```bash
# Recapture specific templates
php artisan templates:batch-screenshot --slugs=site1,site2,site3 --force

# With custom dimensions
php artisan templates:batch-screenshot --slugs=site1,site2 --force --w=1920 --h=1080
```

#### From Text File

Create a text file with one slug per line:

```bash
# Create file
cat > failed_slugs.txt << EOF
template-with-503
another-timeout-site
problematic-wordpress
site-needing-update
EOF

# Process the list
php artisan templates:batch-screenshot --file=failed_slugs.txt --force
```

**Text File Format:**
```
template-one
template-two
template-three
# Comments are supported
template-four
```

Empty lines and lines starting with `#` are automatically skipped.

#### From CSV File

Export from your database or spreadsheet:

**Simple CSV (first column used automatically):**
```csv
slug,status,last_updated
template-one,failed,2024-01-15
template-two,failed,2024-01-16
template-three,failed,2024-01-17
```

```bash
# Process CSV (uses first column by default)
php artisan templates:batch-screenshot --file=export.csv --force
```

**CSV with Specific Column:**
```csv
id,template_slug,error_code,timestamp
1,my-site-one,503,2024-01-15
2,my-site-two,503,2024-01-16
3,my-site-three,503,2024-01-17
```

```bash
# Specify which column to use
php artisan templates:batch-screenshot --file=export.csv --column=template_slug --force

# Or use column index (0-based)
php artisan templates:batch-screenshot --file=export.csv --column=1 --force
```

**CSV Features:**
- Auto-detects header row
- Supports common delimiters (comma, semicolon, tab)
- Handles quoted fields with commas inside
- Skips empty rows automatically

### Advanced Usage

#### Continue on Error

By default, processing stops on first failure. Use `--continue-on-error` to process all templates:

```bash
php artisan templates:batch-screenshot \
  --file=slugs.txt \
  --force \
  --continue-on-error
```

This ensures all templates are attempted even if some fail.

#### Custom Delays

Adjust delay between captures based on server load:

```bash
# Faster processing (less safe)
php artisan templates:batch-screenshot --slugs=site1,site2 --force --delay=1

# Conservative (better for shared hosting)
php artisan templates:batch-screenshot --slugs=site1,site2 --force --delay=5
```

**Recommended Delays:**
- **Shared Hosting**: 5-10 seconds
- **VPS/Dedicated**: 2-3 seconds
- **High-Performance**: 1 second

#### Full-Page Screenshots

Capture entire page instead of viewport:

```bash
php artisan templates:batch-screenshot \
  --file=landing_pages.txt \
  --force \
  --fullpage \
  --w=1920
```

### Progress Tracking

The command provides real-time progress:

```
Batch Screenshot Capture
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Total templates: 5
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

[1/5] Processing: template-one
  ✓ Success
  Waiting 3s before next capture...

[2/5] Processing: template-two
  ✓ Success
  Waiting 3s before next capture...

[3/5] Processing: template-three
  ✗ Failed (exit code: 1)

...

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Batch Processing Complete
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Total processed:  5
Successful:       3
Failed:           2

Failed Slugs (for retry):
  • template-three
  • template-five

To retry failed slugs, use:
  php artisan templates:batch-screenshot --slugs=template-three,template-five --force
```

### Error Recovery

If some captures fail, the command provides retry instructions:

**Option 1: Direct Retry**
```bash
# Copy-paste the suggested command
php artisan templates:batch-screenshot --slugs=failed-site-1,failed-site-2 --force
```

**Option 2: Save to File**
```bash
# Save failed slugs
echo 'failed-site-1
failed-site-2' > retry.txt

# Retry from file
php artisan templates:batch-screenshot --file=retry.txt --force --delay=5
```

### Real-World Examples

#### Example 1: Fix 503 Errors from Server Migration

After migrating to a new server, some screenshots show 503 errors:

```bash
# Step 1: Identify failed templates (check logs or visually)
# Create list
cat > 503_errors.txt << EOF
ecommerce-store
fitness-pro
restaurant-deluxe
EOF

# Step 2: Recapture with conservative settings
php artisan templates:batch-screenshot \
  --file=503_errors.txt \
  --force \
  --delay=5 \
  --continue-on-error

# Step 3: Verify results
ls -lh screenshots/ | grep -E "(ecommerce|fitness|restaurant)"
```

#### Example 2: Database Export and Batch Update

Export from database and process:

```bash
# Step 1: Export from database (MySQL example)
mysql -u user -p database -e \
  "SELECT slug FROM templates WHERE screenshot_url LIKE '%503%' OR screenshot_url IS NULL" \
  > needs_capture.txt

# Step 2: Clean up MySQL output (remove header and formatting)
tail -n +2 needs_capture.txt | tr -d '\t' > clean_slugs.txt

# Step 3: Batch capture
php artisan templates:batch-screenshot \
  --file=clean_slugs.txt \
  --force \
  --continue-on-error
```

#### Example 3: CSV from Spreadsheet

Export quality review from Google Sheets:

**quality_review.csv:**
```csv
Template Name,Slug,Issue,Priority
Restaurant Template,restaurant-deluxe,Outdated screenshot,High
Fitness Site,fitness-pro,503 Error,High
Portfolio,portfolio-creative,Low quality,Medium
```

```bash
# Process high-priority issues
php artisan templates:batch-screenshot \
  --file=quality_review.csv \
  --column=Slug \
  --force \
  --w=1920 \
  --h=1080
```

#### Example 4: Parallel Processing (Advanced)

For very large batches on high-performance servers:

```bash
# Split file into chunks
split -l 10 large_list.txt chunk_

# Process chunks in parallel (use with caution!)
for chunk in chunk_*; do
  php artisan templates:batch-screenshot --file=$chunk --force &
done

# Wait for all to complete
wait

# Clean up
rm chunk_*
```

**⚠️ Warning**: Parallel processing can overwhelm your server. Only use on dedicated servers with sufficient resources.

### Best Practices

1. **Start Small**: Test with 2-3 templates before processing large batches
2. **Use --dry-run First**: Always preview what will be processed (if implementing)
3. **Monitor Memory**: Check server resources during batch processing
4. **Adjust Delays**: Increase delay if server shows signs of stress
5. **Continue on Error**: Use `--continue-on-error` for large batches
6. **Log Output**: Redirect output to file for large batches
   ```bash
   php artisan templates:batch-screenshot --file=large_list.txt --force > batch_log.txt 2>&1
   ```
7. **Schedule Off-Peak**: Run large batches during low-traffic periods

### Integration with Existing Commands

The batch command works alongside existing screenshot commands:

```bash
# Daily: Capture new templates only
php artisan templates:screenshot --new-only --skip-problematic

# Weekly: Batch recapture problem sites
php artisan templates:batch-screenshot --file=weekly_recapture.txt --force

# Monthly: Full refresh
php artisan templates:screenshot --force
```

### Performance Considerations

**Memory Usage:**
- Each template processed sequentially
- Automatic garbage collection between captures
- Memory limits from main screenshot command apply

**Time Estimation:**
- Base capture time: 5-15 seconds per template
- Plus delay time (default 3 seconds)
- Example: 50 templates × 10 seconds = ~8 minutes

**Server Load:**
- Each capture spawns Chrome process
- CPU and memory intensive
- Use longer delays on shared hosting

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
