# WordPress Templates - Complete Commands Reference

**Server:** https://megasandboxs.com/
**Document Root:** `/var/www/vhosts/megasandboxs.com/httpdocs`
**PHP Version:** 8.3 (Alt-PHP via CloudLinux)
**System User:** `megasandboxs.com_c9h1nddlyw`

---

## Table of Contents

1. [Command Execution Methods](#command-execution-methods)
2. [Template Management](#template-management)
3. [Screenshot Commands](#screenshot-commands)
4. [Database Maintenance](#database-maintenance)
5. [System Maintenance](#system-maintenance)
6. [Development & Testing](#development--testing)
7. [Cron Jobs](#cron-jobs)
8. [Environment Variables](#environment-variables)

---

## Command Execution Methods

### Method 1: Using the artisan-wp Wrapper (Recommended)

The `artisan-wp` wrapper automatically uses the correct PHP version and system user:

```bash
# Direct execution
artisan-wp templates:scan

# With options
artisan-wp templates:screenshot --force

# With multiple options
artisan-wp templates:gc --dry-run --clean-screenshots
```

### Method 2: Using Alt-PHP Directly

```bash
# As root or with sudo
/opt/alt/php83/usr/bin/php artisan templates:scan

# As the system user
su megasandboxs.com_c9h1nddlyw -s /bin/bash -c "php artisan templates:scan"
```

### Method 3: In Cron Jobs

```bash
# Always use full paths in cron
/opt/alt/php83/usr/bin/php /var/www/vhosts/megasandboxs.com/httpdocs/artisan templates:scan

# Or use the wrapper
/usr/local/bin/artisan-wp templates:scan
```

---

## Template Management

### Scan for WordPress Installations

**Purpose:** Discover WordPress sites in the templates directory and add them to the database.

```bash
# Scan default templates root (from .env)
artisan-wp templates:scan

# Scan specific directory
artisan-wp templates:scan --path=/custom/path

# Scan and skip garbage collection
artisan-wp templates:scan --no-gc

# Limit scan to first N templates (testing)
artisan-wp templates:scan --limit=10

# Scan and auto-capture screenshots for new templates
artisan-wp templates:scan --capture
```

**What it does:**
- Searches for WordPress installations with `wp-config.php`
- Extracts site metadata (title, description, tags)
- Looks for theme screenshots
- Automatically runs garbage collection (unless `--no-gc`)
- Auto-captures screenshots for new templates

**Output example:**
```
Scanning: /var/www/vhosts/megasandboxs.com/httpdocs
Found: example-site (Example Site Title)
  Categories: business, portfolio
  Tags: modern, responsive
  Theme screenshot: wp-content/themes/twentytwentyfour/screenshot.png
Scan complete. Indexed 45 sites.
Running garbage collection...
```

---

## Screenshot Commands

### Capture Screenshots

**Purpose:** Generate browser screenshots of WordPress template demos using Puppeteer + Chrome.

#### Basic Commands

```bash
# Capture screenshots for all new templates (no existing screenshot)
artisan-wp templates:screenshot --new-only

# Force recapture all templates
artisan-wp templates:screenshot --force

# Capture specific template
artisan-wp templates:screenshot --slug=example-site

# Force recapture specific template
artisan-wp templates:screenshot --slug=example-site --force

# Skip known problematic sites
artisan-wp templates:screenshot --skip-problematic

# Combine options
artisan-wp templates:screenshot --new-only --skip-problematic
```

#### Advanced Options

```bash
# Full-page screenshot (entire page, not just viewport)
artisan-wp templates:screenshot --fullpage

# Custom viewport dimensions
artisan-wp templates:screenshot --w=1920 --h=1080

# Full-page with custom dimensions
artisan-wp templates:screenshot --fullpage --w=1920 --h=1080

# Specific template with custom settings
artisan-wp templates:screenshot --slug=example-site --force --w=1600 --h=900
```

**What it does:**
- Launches headless Chrome browser
- Loads the WordPress site
- Dismisses cookie consent dialogs
- Waits for page to fully load
- Captures PNG screenshot
- Generates responsive WebP variants (480px, 768px, 1024px)
- Fixes file permissions automatically
- Updates database with screenshot URL

**Output example:**
```
example-site: capturing https://megasandboxs.com/example-site/ …
 → saved: https://megasandboxs.com/screenshots/example-site.png
another-site: captured screenshot exists, skip (use --force to recapture)
Done. Captured 1 screenshot(s).
```

### Generate Screenshot Variants

**Purpose:** Create responsive WebP/PNG variants for existing screenshots.

```bash
# Generate variants for all screenshots
artisan-wp templates:screenshot-variants --all

# Generate variants for specific template
artisan-wp templates:screenshot-variants --slug=example-site
```

**Output files:**
- `example-site.png` (original)
- `example-site-480.webp` (mobile)
- `example-site-768.webp` (tablet)
- `example-site-1024.webp` (desktop)

---

## Database Maintenance

### Garbage Collection

**Purpose:** Remove templates from database when their filesystem directories no longer exist.

#### Preview Mode (Safe)

```bash
# Preview what would be deleted (always run this first!)
artisan-wp templates:gc --dry-run

# Preview with screenshot cleanup
artisan-wp templates:gc --dry-run --clean-screenshots
```

**Output example:**
```
Scanning templates root: /var/www/vhosts/megasandboxs.com/httpdocs
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

#### Live Mode (Destructive)

```bash
# Remove orphaned templates from database only
artisan-wp templates:gc

# Remove orphaned templates AND their screenshot files
artisan-wp templates:gc --clean-screenshots
```

**What it does:**
- Checks each database template for existing filesystem directory
- Verifies `wp-config.php` exists
- Marks templates as orphaned if not found
- Optionally deletes screenshot files
- Prompts for confirmation before deletion

**When to use:**
- After manually deleting WordPress installations
- After bulk cleanup operations
- When database shows incorrect template counts
- Periodically for housekeeping

**Note:** The `templates:scan` command automatically runs garbage collection. Manual runs are only needed if you skip the auto-GC with `--no-gc`.

---

## System Maintenance

### Chrome Process Cleanup

**Purpose:** Kill zombie Chrome processes and clean up temporary files.

```bash
# Preview Chrome processes that would be cleaned
artisan-wp chrome:cleanup --dry-run

# Kill zombie Chrome processes
artisan-wp chrome:cleanup --force

# Detailed output
artisan-wp chrome:cleanup --force -vvv
```

**What it does:**
- Finds Chrome processes older than `CHROME_PROCESS_MAX_AGE` (default: 300s)
- Kills zombie processes
- Cleans up Puppeteer temp directories
- Reports freed resources

**When to use:**
- After screenshot capture sessions
- When server is slow/overloaded
- Before large screenshot batch jobs
- In cron every 15 minutes

### Fix Screenshot Permissions

**Purpose:** Correct file ownership and permissions for screenshots.

```bash
# Fix permissions for all screenshots
artisan-wp templates:fix-permissions

# Fix specific directory
artisan-wp templates:fix-permissions --path=screenshots/
```

**What it does:**
- Sets ownership to `SCREENSHOT_SYSUSER:SCREENSHOT_GROUP`
- Sets directories to 755
- Sets files to 644
- Ensures web server can serve files

**When to use:**
- After running commands as root
- When getting 403 Forbidden errors on screenshots
- After manual file operations

---

## Development & Testing

### Database Operations

```bash
# Run migrations
artisan-wp migrate

# Rollback migrations
artisan-wp migrate:rollback

# Fresh migration (drops all tables)
artisan-wp migrate:fresh

# Seed database with test data
artisan-wp db:seed
```

### Cache Management

```bash
# Clear all caches
artisan-wp cache:clear

# Clear config cache
artisan-wp config:clear

# Clear route cache
artisan-wp route:clear

# Clear view cache
artisan-wp view:clear

# Optimize for production
artisan-wp config:cache
artisan-wp route:cache
artisan-wp view:cache
artisan-wp optimize
```

### Queue Management

```bash
# Process queue jobs
artisan-wp queue:work

# Process with specific options
artisan-wp queue:work --tries=3 --timeout=90

# Clear failed jobs
artisan-wp queue:flush
```

### Laravel Tinker (Interactive Shell)

```bash
# Open Tinker REPL
artisan-wp tinker

# Execute single command
artisan-wp tinker --execute="App\Models\Template::count()"

# Execute multiple commands
artisan-wp tinker --execute="
\$t = App\Models\Template::first();
echo \$t->slug . PHP_EOL;
echo \$t->screenshot_url . PHP_EOL;
"
```

**Common Tinker commands:**
```php
// Count templates
App\Models\Template::count();

// Get template by slug
$t = App\Models\Template::where('slug', 'example-site')->first();

// Update screenshot URL
$t->screenshot_url = asset('screenshots/example-site.png');
$t->save();

// List all templates
App\Models\Template::pluck('slug', 'title');

// Delete template
App\Models\Template::where('slug', 'old-site')->delete();
```

### Logging & Debugging

```bash
# View Laravel logs
tail -f storage/logs/laravel.log

# View cron logs
tail -f storage/logs/cron-scan.log

# View Apache errors
tail -f /var/log/httpd/error_log

# View last 50 lines
tail -50 storage/logs/laravel.log

# Watch logs in real-time
tail -f storage/logs/laravel.log | grep ERROR
```

### System Health Checks

```bash
# Check PHP version
/opt/alt/php83/usr/bin/php -v

# Check PHP extensions
/opt/alt/php83/usr/bin/php -m | grep -E "imagick|gd|pdo|mysql"

# Check Chrome installation
which google-chrome-stable
google-chrome-stable --version

# Check Node.js
which node
node --version

# Check Puppeteer
npm list puppeteer

# Check disk space
df -h /var/www/vhosts/megasandboxs.com

# Check memory
free -h

# Check Chrome processes
ps aux | grep chrome | grep -v grep

# Count screenshot files
ls -1 screenshots/*.png | wc -l
```

---

## Cron Jobs

### Recommended Cron Schedule

Edit crontab:
```bash
crontab -e
```

Add these jobs:

```cron
# Environment variables
MAILTO=""
ARTISAN=/usr/local/bin/artisan-wp
LOGDIR=/var/www/vhosts/megasandboxs.com/httpdocs/storage/logs
PATH="/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin"

# Scan for new WordPress installations every 15 minutes
# (includes automatic garbage collection and screenshot capture)
*/15 * * * * $ARTISAN templates:scan >> $LOGDIR/cron-scan.log 2>&1

# Daily screenshot update for new templates (2 AM)
0 2 * * * $ARTISAN templates:screenshot --new-only --skip-problematic >> $LOGDIR/cron-screenshots.log 2>&1

# Weekly full screenshot refresh (Sundays at 3 AM)
0 3 * * 0 $ARTISAN templates:screenshot --force >> $LOGDIR/cron-screenshots-full.log 2>&1

# Chrome cleanup every 15 minutes
*/15 * * * * $ARTISAN chrome:cleanup --force >> $LOGDIR/chrome-cleanup.log 2>&1

# Weekly garbage collection with screenshot cleanup (Sundays at 4 AM)
0 4 * * 0 $ARTISAN templates:gc --clean-screenshots >> $LOGDIR/cron-gc.log 2>&1

# Monthly cache clear (1st of month at 5 AM)
0 5 1 * * $ARTISAN cache:clear >> $LOGDIR/cron-cache.log 2>&1
```

### Manual Cron Testing

```bash
# Test scan job manually
/usr/local/bin/artisan-wp templates:scan

# Test screenshot job manually
/usr/local/bin/artisan-wp templates:screenshot --new-only --skip-problematic

# Check cron logs
tail -50 /var/www/vhosts/megasandboxs.com/httpdocs/storage/logs/cron-scan.log
```

---

## Environment Variables

### Critical Variables

Located in `/var/www/vhosts/megasandboxs.com/httpdocs/.env`

#### Application
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://megasandboxs.com
APP_TIMEZONE=Europe/Berlin
```

#### Templates
```env
TEMPLATES_ROOT=/var/www/vhosts/megasandboxs.com/httpdocs
DEMO_URL_PATTERN=https://megasandboxs.com/{slug}/
```

#### Screenshots
```env
CHROME_BINARY_PATH=/usr/bin/google-chrome-stable
NODE_BINARY_PATH=/usr/bin/node
SCREENSHOT_TIMEOUT=30000
SCREENSHOT_DELAY=3500
SCREENSHOT_DELAY_MS=3000
SCREENSHOT_DELAY_PROBLEM_MS=2500
SCREENSHOT_FALLBACK_DELAY_MS=3500
SCREENSHOT_SYSUSER=megasandboxs.com_c9h1nddlyw
SCREENSHOT_GROUP=psacln
```

#### Memory Management
```env
SCREENSHOT_MEMORY_LIMIT_MB=256
SCREENSHOT_DELAY_BETWEEN_MS=2000
MAX_CONCURRENT_SCREENSHOTS=2
```

#### Chrome Process Management
```env
CHROME_PROCESS_MAX_AGE=300
CHROME_TEMP_DIR_MAX_AGE=3600
```

### Applying Environment Changes

After editing `.env`:
```bash
# Clear config cache
artisan-wp config:clear

# Or cache new config (production)
artisan-wp config:cache

# Verify changes
artisan-wp tinker --execute="echo config('templates.root');"
```

---

## Common Workflows

### Daily Operations

```bash
# Morning routine
artisan-wp templates:scan
artisan-wp templates:screenshot --new-only
artisan-wp chrome:cleanup --force
```

### After Bulk Template Changes

```bash
# 1. Scan for changes
artisan-wp templates:scan

# 2. Clean up orphaned entries
artisan-wp templates:gc --dry-run
artisan-wp templates:gc --clean-screenshots

# 3. Update screenshots
artisan-wp templates:screenshot --new-only

# 4. Clean up Chrome
artisan-wp chrome:cleanup --force
```

### When Screenshots Are Broken

```bash
# 1. Check screenshot files exist
ls -la screenshots/ | head -20

# 2. Fix permissions
artisan-wp templates:fix-permissions

# 3. Verify database URLs
artisan-wp tinker --execute="App\Models\Template::take(5)->get(['slug', 'screenshot_url'])"

# 4. Test single capture
artisan-wp templates:screenshot --slug=test-site --force

# 5. Check Chrome is working
google-chrome-stable --headless --disable-gpu --screenshot https://www.google.com
```

### Performance Issues

```bash
# 1. Check memory usage
free -h

# 2. Kill Chrome zombies
artisan-wp chrome:cleanup --force
pkill -9 chrome

# 3. Clear caches
artisan-wp cache:clear
artisan-wp config:clear

# 4. Check disk space
df -h

# 5. Review logs
tail -100 storage/logs/laravel.log | grep -i error
```

---

## Quick Reference Card

### Most Used Commands

```bash
# Scan and update
artisan-wp templates:scan

# Capture new screenshots
artisan-wp templates:screenshot --new-only

# Force recapture all
artisan-wp templates:screenshot --force

# Capture one template
artisan-wp templates:screenshot --slug=SLUG --force

# Clean database
artisan-wp templates:gc --dry-run
artisan-wp templates:gc --clean-screenshots

# Kill Chrome zombies
artisan-wp chrome:cleanup --force

# Fix permissions
artisan-wp templates:fix-permissions

# View logs
tail -f storage/logs/laravel.log
```

### Emergency Commands

```bash
# Stop everything
pkill -9 chrome
pkill -f "php.*artisan"

# Clean up completely
artisan-wp chrome:cleanup --force
rm -rf /tmp/puppeteer_dev_chrome_profile-*

# Reset screenshots
artisan-wp templates:screenshot --force

# Database backup
mysqldump -u wp_templates_user -p wp_templates > backup_$(date +%Y%m%d).sql
```

---

## Additional Documentation

- **[README.md](README.md)** - Project overview and setup
- **[MAINTENANCE.md](MAINTENANCE.md)** - Database maintenance guide
- **[MIGRATION_CHECKLIST.md](MIGRATION_CHECKLIST.md)** - Server migration guide
- **[SCREENSHOT_SETUP_GUIDE.md](SCREENSHOT_SETUP_GUIDE.md)** - Detailed screenshot setup
- **[SCREENSHOT_QUICK_REFERENCE.md](SCREENSHOT_QUICK_REFERENCE.md)** - Quick troubleshooting

---

## Getting Help

### Diagnostic Information

When reporting issues, collect this info:

```bash
# System info
uname -a
cat /etc/redhat-release

# PHP info
/opt/alt/php83/usr/bin/php -v
/opt/alt/php83/usr/bin/php -m

# Binary locations
which google-chrome-stable node npm php

# Process info
ps aux | grep chrome
ps aux | grep php.*artisan

# Disk & memory
df -h
free -h

# Recent errors
tail -50 storage/logs/laravel.log | grep ERROR
tail -50 /var/log/httpd/error_log | grep -i error

# Template count
artisan-wp tinker --execute="echo App\Models\Template::count();"

# Screenshot count
ls -1 screenshots/*.png | wc -l
```

---

**Document Version:** 1.0
**Last Updated:** December 11, 2025
**Server:** megasandboxs.com
