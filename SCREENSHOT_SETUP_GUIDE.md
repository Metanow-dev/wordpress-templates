# Screenshot Functionality Setup Guide

**Migration Target:** https://megasandboxs.com/
**Date:** December 2025
**Current Server (Reference):** https://wp-templates.metanow.dev/

## Table of Contents
1. [Overview](#overview)
2. [How It Works](#how-it-works)
3. [Why Alt-PHP is Required](#why-alt-php-is-required)
4. [Dependencies](#dependencies)
5. [Configuration](#configuration)
6. [Step-by-Step Setup](#step-by-step-setup)
7. [Troubleshooting](#troubleshooting)
8. [Testing](#testing)

---

## Overview

The screenshot functionality captures browser screenshots of WordPress template demos using:
- **Laravel Command:** `templates:screenshot`
- **Technology Stack:** Spatie Browsershot + Puppeteer + Chrome/Chromium
- **Image Processing:** Imagick/GD for WebP variant generation
- **Output:** PNG screenshots + responsive WebP variants (480w, 768w, 1024w)

### Key Files:
- `app/Console/Commands/CaptureTemplateScreenshots.php` - Main artisan command
- `app/Support/Screenshotter.php` - Screenshot capture logic
- `app/Console/Commands/CleanupChromeProcessesCommand.php` - Zombie process cleanup
- `/usr/local/bin/artisan-wp` - Wrapper script with proper PHP path

---

## How It Works

### Architecture Overview

```
Laravel Artisan Command (PHP)
    ↓
Spatie Browsershot (PHP Library)
    ↓
Node.js + Puppeteer (JavaScript)
    ↓
Chrome/Chromium Browser (Binary)
    ↓
Screenshot Captured (PNG)
    ↓
Imagick/GD Processing
    ↓
Responsive WebP Variants Generated
    ↓
Permissions Fixed (chown/chmod)
```

### Process Flow:

1. **Command Execution:**
   ```bash
   php artisan templates:screenshot --slug=example-template
   ```

2. **Browsershot Initialization:**
   - Loads URL with Chrome/Chromium via Puppeteer
   - Injects cookie consent dismissal scripts
   - Waits for page load (network idle or DOM ready)
   - Captures viewport or full-page screenshot

3. **Image Generation:**
   - Saves main PNG screenshot
   - Creates 3 responsive WebP variants (480px, 768px, 1024px width)
   - Falls back to PNG if WebP encoding fails

4. **Permission Management:**
   - Sets ownership to domain system user (prevents 403 errors)
   - Applies proper chmod (755 for dirs, 644 for files)

5. **Cleanup:**
   - Removes zombie Chrome processes
   - Cleans up temp Puppeteer directories

---

## Why Alt-PHP is Required

### The Problem

Plesk CloudLinux uses **alt-php** (CloudLinux PHP) which is isolated from system PHP. When running artisan commands:

❌ **Standard PHP:**
```bash
php artisan templates:screenshot
# Uses: /usr/bin/php (system PHP, may be wrong version/config)
```

✅ **Alt-PHP (Required):**
```bash
/opt/alt/php83/usr/bin/php artisan templates:screenshot
# Uses: CloudLinux PHP 8.3 with proper extensions and configuration
```

### Why This Matters:

1. **Extension Availability:**
   - Alt-PHP has Imagick, GD, and other required extensions
   - System PHP may not have these extensions installed

2. **Configuration:**
   - Alt-PHP uses domain-specific `php.ini` settings
   - Memory limits, timeouts, and other settings are optimized

3. **File Permissions:**
   - Alt-PHP runs as the domain system user
   - Prevents ownership/permission issues with generated files

4. **Cron Jobs:**
   - Scheduled tasks must use alt-php path
   - Otherwise screenshots fail silently in cron

### The Solution: artisan-wp Wrapper

**File:** `/usr/local/bin/artisan-wp`

```bash
#!/bin/bash
cd /var/www/vhosts/DOMAIN/httpdocs
sudo -u SYSTEM_USER /opt/alt/php83/usr/bin/php artisan "$@"
```

**Usage:**
```bash
# Instead of: php artisan templates:screenshot
# Use: artisan-wp templates:screenshot
```

**In Crontab:**
```cron
0 12 * * * /usr/local/bin/artisan-wp templates:scan >> /path/to/logs/cron-scan.log 2>&1
```

---

## Dependencies

### 1. System Packages

```bash
# Chrome/Chromium browser
sudo dnf install -y google-chrome-stable
# OR
sudo dnf install -y chromium chromium-headless

# Node.js and npm
sudo dnf module install -y nodejs:20
sudo dnf install -y npm

# Additional Chrome dependencies
sudo dnf install -y \
    libX11 libXcomposite libXcursor libXdamage libXext libXi \
    libXtst cups-libs libXScrnSaver libXrandr alsa-lib \
    pango at-spi2-atk gtk3 nss mesa-libgbm
```

### 2. PHP Extensions (Alt-PHP)

```bash
# Install required PHP 8.3 extensions
sudo dnf install -y \
    alt-php83-php-imagick \
    alt-php83-php-gd \
    alt-php83-php-xml \
    alt-php83-php-mbstring \
    alt-php83-php-process
```

**Verify Installation:**
```bash
/opt/alt/php83/usr/bin/php -m | grep -E "imagick|gd"
# Should show:
# gd
# imagick
```

### 3. Node.js Packages

```bash
cd /var/www/vhosts/DOMAIN/httpdocs
npm install
# This installs puppeteer from package.json
```

**Puppeteer will download Chrome automatically** during installation (version 24.19.0 as per package.json).

### 4. Composer Packages

```bash
cd /var/www/vhosts/DOMAIN/httpdocs
composer install --no-dev --optimize-autoloader
```

**Key packages:**
- `spatie/browsershot` - Screenshot capture
- `spatie/image` - Image processing

---

## Configuration

### 1. Environment Variables (.env)

**Update for new server:**

```env
# Application
APP_URL=https://megasandboxs.com

# Templates Root (IMPORTANT: Changed from /public to /httpdocs)
TEMPLATES_ROOT=/var/www/vhosts/megasandboxs.com/httpdocs
DEMO_URL_PATTERN=https://megasandboxs.com/{slug}/

# Chrome/Node Binary Paths
CHROME_BINARY_PATH=/usr/bin/google-chrome-stable
NODE_BINARY_PATH=/usr/bin/node

# Screenshot Configuration
SCREENSHOT_TIMEOUT=30000
SCREENSHOT_WAIT_FOR_NETWORK_IDLE=true
SCREENSHOT_DELAY=3500
SCREENSHOT_SYSUSER=megasandboxs_USER  # Replace with actual system user
SCREENSHOT_GROUP=psacln
CHROME_PROCESS_MAX_AGE=300
CHROME_TEMP_DIR_MAX_AGE=3600

# Optional: Override Browsershot binary paths
# BROWSERSHOT_CHROME_BINARY=/usr/bin/google-chrome-stable
# BROWSERSHOT_NODE_BINARY=/usr/bin/node
```

**Find System User:**
```bash
ls -la /var/www/vhosts/megasandboxs.com/httpdocs | head -1
# Look at the owner column (e.g., megasandboxs_abc123def)
```

### 2. Apache Configuration

**Location:** Plesk → Domains → megasandboxs.com → Apache & nginx Settings

**Add to Additional directives:**

```apache
# Force CloudLinux alt-php 8.3 for this vhost
AddType application/x-httpd-alt-php83 .php
<IfModule lsapi_module>
    AddHandler application/x-httpd-alt-php83 .php
</IfModule>

# Laravel httpdocs directory access
<Directory "/var/www/vhosts/megasandboxs.com/httpdocs">
    AllowOverride All
    Options -Indexes +SymLinksIfOwnerMatch
    Require all granted
</Directory>

DirectoryIndex index.php index.html
```

**Why this matters:**
- Forces Apache to use alt-php 8.3 for web requests
- Ensures consistency with CLI commands
- Provides proper directory access for Laravel

### 3. .htaccess Configuration

**File:** `/var/www/vhosts/megasandboxs.com/httpdocs/public/.htaccess`

```apache
# Force CloudLinux alt-php 8.3 for this vhost
<IfModule mime_module>
    AddType application/x-httpd-alt-php83 .php
</IfModule>
<IfModule lsapi_module>
    AddHandler application/x-httpd-alt-php83 .php
</IfModule>

DirectoryIndex index.php index.html

# Laravel Framework Routing
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

### 4. Document Root Configuration

**IMPORTANT:** Document root structure has changed:

**Old Server (wp-templates.metanow.dev):**
```
/var/www/vhosts/wp-templates.metanow.dev/
├── httpdocs/
│   ├── public/           ← Document root (Laravel public/)
│   │   ├── index.php
│   │   ├── template1/    ← WP installations
│   │   ├── template2/
│   │   └── ...
│   ├── app/
│   ├── config/
│   └── ...
```

**New Server (megasandboxs.com):**
```
/var/www/vhosts/megasandboxs.com/
├── httpdocs/             ← Document root (contains both Laravel + WP)
│   ├── public/           ← Laravel public/ (NOT document root!)
│   │   └── index.php
│   ├── template1/        ← WP installations at httpdocs level
│   ├── template2/
│   ├── app/
│   ├── config/
│   └── ...
```

**Configure Document Root:**
1. Go to: Plesk → Domains → megasandboxs.com → Hosting Settings
2. Set **Document root** to: `httpdocs`
3. Save changes

**Update Laravel index.php:**

If Laravel's `public/index.php` needs to be at the root, you may need to adjust paths in `httpdocs/index.php`:

```php
<?php
// Adjust paths since we're one level up from public/
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
// ... rest of index.php
```

**OR** keep Laravel's `public/` structure and use `.htaccess` rewrite rules to route Laravel requests.

---

## Step-by-Step Setup

### Phase 1: System Dependencies

```bash
# 1. Install Chrome/Chromium
sudo dnf install -y google-chrome-stable

# 2. Verify Chrome installation
google-chrome --version
# OR
/usr/bin/google-chrome-stable --version

# 3. Check Node.js
node --version  # Should be v20+
npm --version

# 4. Install if missing
sudo dnf module install -y nodejs:20
```

### Phase 2: PHP Extensions

```bash
# 1. Install alt-php extensions
sudo dnf install -y \
    alt-php83-php-imagick \
    alt-php83-php-gd \
    alt-php83-php-xml \
    alt-php83-php-mbstring \
    alt-php83-php-process

# 2. Verify installation
/opt/alt/php83/usr/bin/php -m | grep -E "imagick|gd"

# 3. Check Imagick WebP support
/opt/alt/php83/usr/bin/php -r "echo (in_array('WEBP', Imagick::queryFormats())) ? 'WebP supported' : 'WebP NOT supported';"
```

### Phase 3: Application Setup

```bash
# 1. Navigate to project
cd /var/www/vhosts/megasandboxs.com/httpdocs

# 2. Install Composer dependencies
composer install --no-dev --optimize-autoloader

# 3. Install Node.js dependencies
npm install

# 4. Build assets (if needed)
npm run build

# 5. Create storage symlink
php artisan storage:link

# 6. Create screenshots directory
mkdir -p storage/app/public/screenshots
chmod 755 storage/app/public/screenshots

# 7. Set ownership (replace USER with actual system user)
chown -R megasandboxs_USER:psacln storage/app/public/screenshots

# 8. Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Phase 4: Create artisan-wp Wrapper

```bash
# 1. Create wrapper script
sudo nano /usr/local/bin/artisan-wp

# 2. Add content (replace USER and DOMAIN):
#!/bin/bash
cd /var/www/vhosts/megasandboxs.com/httpdocs
sudo -u megasandboxs_USER /opt/alt/php83/usr/bin/php artisan "$@"

# 3. Make executable
sudo chmod +x /usr/local/bin/artisan-wp

# 4. Test wrapper
artisan-wp --version
# Should show Laravel version
```

### Phase 5: Configure Environment

```bash
# 1. Edit .env file
nano .env

# 2. Update these values:
APP_URL=https://megasandboxs.com
TEMPLATES_ROOT=/var/www/vhosts/megasandboxs.com/httpdocs
DEMO_URL_PATTERN=https://megasandboxs.com/{slug}/
CHROME_BINARY_PATH=/usr/bin/google-chrome-stable
NODE_BINARY_PATH=/usr/bin/node
SCREENSHOT_SYSUSER=megasandboxs_USER
SCREENSHOT_GROUP=psacln

# 3. Save and exit

# 4. Clear config cache
artisan-wp config:clear
```

### Phase 6: Apache Configuration

**Via Plesk Panel:**

1. **Apache & nginx Settings:**
   - Plesk → Domains → megasandboxs.com → Apache & nginx Settings
   - Add to "Additional directives for HTTP":
   ```apache
   AddType application/x-httpd-alt-php83 .php
   <IfModule lsapi_module>
       AddHandler application/x-httpd-alt-php83 .php
   </IfModule>

   <Directory "/var/www/vhosts/megasandboxs.com/httpdocs">
       AllowOverride All
       Options -Indexes +SymLinksIfOwnerMatch
       Require all granted
   </Directory>

   DirectoryIndex index.php index.html
   ```
   - Click "OK"

2. **Hosting Settings:**
   - Plesk → Domains → megasandboxs.com → Hosting Settings
   - Set **Document root** to: `httpdocs`
   - Click "OK"

3. **Restart Apache:**
   ```bash
   sudo systemctl restart httpd
   ```

### Phase 7: Setup Cron Jobs

**Via Plesk Panel:**

1. Plesk → Tools & Settings → Scheduled Tasks (or Domain → Scheduled Tasks)
2. Add environment variables at the top:
   ```cron
   MAILTO=""
   ARTISAN=/usr/local/bin/artisan-wp
   LOGDIR=/var/www/vhosts/megasandboxs.com/httpdocs/storage/logs
   PATH="/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin"
   ```

3. Add cron jobs:
   ```cron
   # Template scan (runs twice daily)
   0 12 * * * $ARTISAN templates:scan >> $LOGDIR/cron-scan.log 2>&1
   0 19 * * * $ARTISAN templates:scan >> $LOGDIR/cron-scan.log 2>&1

   # Chrome cleanup (every 15 minutes)
   */15 * * * * $ARTISAN chrome:cleanup --force >> /var/log/chrome-cleanup.log 2>&1
   ```

**Via Command Line:**

```bash
crontab -e

# Add these lines:
MAILTO=""
ARTISAN=/usr/local/bin/artisan-wp
LOGDIR=/var/www/vhosts/megasandboxs.com/httpdocs/storage/logs
PATH="/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin"

0 12 * * * $ARTISAN templates:scan >> $LOGDIR/cron-scan.log 2>&1
0 19 * * * $ARTISAN templates:scan >> $LOGDIR/cron-scan.log 2>&1
*/15 * * * * $ARTISAN chrome:cleanup --force >> /var/log/chrome-cleanup.log 2>&1
```

---

## Troubleshooting

### Issue 1: Chrome/Chromium Not Found

**Error:**
```
Chrome binary not found
```

**Solution:**
```bash
# Find Chrome binary
which google-chrome google-chrome-stable chromium chromium-browser

# Update .env
CHROME_BINARY_PATH=/usr/bin/google-chrome-stable

# OR install Chrome
sudo dnf install -y google-chrome-stable

# Clear config
artisan-wp config:clear
```

### Issue 2: Node.js/Puppeteer Not Found

**Error:**
```
node: not found
puppeteer: not found
```

**Solution:**
```bash
# Check Node.js
which node
node --version

# Install if missing
sudo dnf module install -y nodejs:20

# Reinstall puppeteer
cd /var/www/vhosts/megasandboxs.com/httpdocs
rm -rf node_modules package-lock.json
npm install
```

### Issue 3: Permission Denied (403 Errors)

**Error:**
```
Failed to load screenshot: 403 Forbidden
```

**Solution:**
```bash
# Fix ownership
cd /var/www/vhosts/megasandboxs.com/httpdocs
chown -R megasandboxs_USER:psacln storage/app/public/screenshots

# Fix permissions
chmod 755 storage/app/public/screenshots
find storage/app/public/screenshots -type f -exec chmod 644 {} \;
find storage/app/public/screenshots -type d -exec chmod 755 {} \;

# Run Laravel permission fix command
artisan-wp templates:fix-permissions
```

### Issue 4: Imagick/GD Not Available

**Error:**
```
No image library available
Call to undefined function imagewebp()
```

**Solution:**
```bash
# Install extensions
sudo dnf install -y alt-php83-php-imagick alt-php83-php-gd

# Verify
/opt/alt/php83/usr/bin/php -m | grep -E "imagick|gd"

# Check WebP support
/opt/alt/php83/usr/bin/php -r "var_dump(extension_loaded('imagick'));"
/opt/alt/php83/usr/bin/php -r "var_dump(function_exists('imagewebp'));"

# Restart Apache
sudo systemctl restart httpd
```

### Issue 5: Timeout During Screenshot Capture

**Error:**
```
Timeout while capturing screenshot
Navigation timeout of 30000 ms exceeded
```

**Solution:**

1. **Increase timeout in .env:**
   ```env
   SCREENSHOT_TIMEOUT=60000
   SCREENSHOT_DELAY=5000
   ```

2. **Use --skip-problematic flag:**
   ```bash
   artisan-wp templates:screenshot --skip-problematic
   ```

3. **Capture specific template with longer delay:**
   ```bash
   artisan-wp templates:screenshot --slug=problematic-template
   ```

### Issue 6: Chrome Zombie Processes

**Symptoms:**
- Server runs out of memory
- Many chrome processes running
- Screenshots fail randomly

**Solution:**
```bash
# Manual cleanup
artisan-wp chrome:cleanup --force

# Check zombie processes
ps aux | grep chrome

# Kill all Chrome processes (careful!)
pkill -9 chrome

# Clean temp directories
rm -rf /tmp/puppeteer_dev_chrome_profile-*

# Ensure cron job is running
crontab -l | grep chrome:cleanup
```

### Issue 7: SELinux Blocking Chrome

**Error:**
```
Chrome failed to start: cannot open shared object file
```

**Solution:**
```bash
# Check SELinux status
getenforce

# Temporarily disable (testing only)
sudo setenforce 0

# Permanent fix: Add SELinux policies
sudo setsebool -P httpd_execmem 1
sudo setsebool -P httpd_can_network_connect 1

# OR disable SELinux (not recommended for production)
sudo nano /etc/selinux/config
# Set: SELINUX=disabled
```

### Issue 8: Alt-PHP Not Being Used

**Symptoms:**
- Commands work manually but fail in cron
- Missing extensions errors
- Permission issues

**Solution:**
```bash
# Verify alt-php is being used
/opt/alt/php83/usr/bin/php -v

# Update cron to use full path
# DON'T: php artisan templates:screenshot
# DO: /opt/alt/php83/usr/bin/php artisan templates:screenshot
# OR: artisan-wp templates:screenshot

# Check which PHP is in PATH
which php
# Should show /opt/alt/php83/usr/bin/php

# Update .htaccess and Apache config as shown above
```

---

## Testing

### Test 1: Manual Screenshot Capture

```bash
# Test single template
artisan-wp templates:screenshot --slug=test-template --force

# Check logs
tail -f storage/logs/laravel.log

# Verify screenshot created
ls -la storage/app/public/screenshots/test-template*
```

**Expected output:**
```
storage/app/public/screenshots/
├── test-template.png
├── test-template-480.webp
├── test-template-768.webp
└── test-template-1024.webp
```

### Test 2: Check Browser Access

```bash
# Visit screenshot URL in browser
https://megasandboxs.com/storage/screenshots/test-template.png

# Should display the screenshot, NOT 404 or 403
```

### Test 3: Verify Permissions

```bash
# Check ownership
ls -la storage/app/public/screenshots/ | head -10

# All files should be owned by: megasandboxs_USER:psacln
# Directories: drwxr-xr-x (755)
# Files: -rw-r--r-- (644)
```

### Test 4: Test Chrome Cleanup

```bash
# Run cleanup
artisan-wp chrome:cleanup --dry-run

# Check for zombie processes
ps aux | grep chrome

# Run actual cleanup
artisan-wp chrome:cleanup --force
```

### Test 5: Cron Job Test

```bash
# Run cron command manually
/usr/local/bin/artisan-wp templates:scan

# Check cron logs
tail -f storage/logs/cron-scan.log

# Verify cron is scheduled
crontab -l
```

### Test 6: Full System Test

```bash
# 1. Clear all caches
artisan-wp cache:clear
artisan-wp config:clear

# 2. Scan templates
artisan-wp templates:scan

# 3. Capture screenshots for new templates
artisan-wp templates:screenshot --new-only

# 4. Check for errors
artisan-wp chrome:cleanup --dry-run

# 5. Fix permissions
artisan-wp templates:fix-permissions

# 6. Verify in browser
# Visit: https://megasandboxs.com/
# Screenshots should load properly
```

---

## Key Differences: Old vs New Server

| Aspect | Old Server | New Server |
|--------|-----------|------------|
| **Domain** | wp-templates.metanow.dev | megasandboxs.com |
| **Document Root** | `httpdocs/public/` | `httpdocs/` |
| **WP Location** | `public/{slug}/` | `httpdocs/{slug}/` |
| **Laravel Public** | Document root | Subdirectory |
| **TEMPLATES_ROOT** | `.../httpdocs/public` | `.../httpdocs` |
| **DEMO_URL_PATTERN** | `.../{slug}/` | `.../{slug}/` |
| **Structure** | Laravel-first | Flat (Laravel + WP at same level) |

**Migration Checklist:**
- [ ] Update APP_URL in .env
- [ ] Update TEMPLATES_ROOT (remove `/public`)
- [ ] Update DEMO_URL_PATTERN
- [ ] Update document root in Plesk
- [ ] Update .htaccess paths if needed
- [ ] Update artisan-wp script paths
- [ ] Update cron job paths
- [ ] Test all URLs work correctly

---

## Useful Commands Reference

```bash
# Screenshot Commands
artisan-wp templates:screenshot                    # Capture all templates
artisan-wp templates:screenshot --slug=example     # Capture specific template
artisan-wp templates:screenshot --force            # Force recapture all
artisan-wp templates:screenshot --new-only         # Only capture new templates
artisan-wp templates:screenshot --skip-problematic # Skip known problematic sites
artisan-wp templates:screenshot --fullpage         # Full-page screenshot

# Maintenance Commands
artisan-wp chrome:cleanup --force                  # Kill zombie Chrome processes
artisan-wp chrome:cleanup --dry-run                # Show what would be cleaned
artisan-wp templates:fix-permissions               # Fix screenshot permissions
artisan-wp templates:scan                          # Scan for new templates

# Cache Commands
artisan-wp cache:clear                             # Clear application cache
artisan-wp config:clear                            # Clear config cache
artisan-wp route:clear                             # Clear route cache
artisan-wp view:clear                              # Clear view cache

# Debug Commands
/opt/alt/php83/usr/bin/php -v                      # Check PHP version
/opt/alt/php83/usr/bin/php -m                      # List PHP modules
which google-chrome                                # Find Chrome binary
node --version                                     # Check Node version
npm list puppeteer                                 # Check Puppeteer version
ps aux | grep chrome                               # Find Chrome processes
ls -la storage/app/public/screenshots/             # List screenshots
tail -f storage/logs/laravel.log                   # Watch Laravel logs
```

---

## Common Error Messages & Solutions

| Error | Cause | Solution |
|-------|-------|----------|
| `Chrome binary not found` | Chrome not installed or wrong path | Install Chrome, update CHROME_BINARY_PATH |
| `node: not found` | Node.js not installed | `sudo dnf module install nodejs:20` |
| `Class 'Imagick' not found` | Imagick extension missing | `sudo dnf install alt-php83-php-imagick` |
| `Permission denied` | Wrong file ownership | `artisan-wp templates:fix-permissions` |
| `Navigation timeout` | Slow page load | Increase SCREENSHOT_TIMEOUT in .env |
| `503 Service Unavailable` | Alt-PHP not configured | Update Apache config, add alt-php handler |
| `Storage symlink missing` | storage/app/public not linked | `php artisan storage:link` |
| `Chrome failed to start` | Missing dependencies or SELinux | Install Chrome deps, check SELinux |

---

## Performance Optimization

### 1. Chrome Process Limits

Add to `.env`:
```env
CHROME_PROCESS_MAX_AGE=300
CHROME_TEMP_DIR_MAX_AGE=3600
```

### 2. Screenshot Timeouts

Adjust based on site complexity:
```env
# Fast sites
SCREENSHOT_TIMEOUT=15000
SCREENSHOT_DELAY=2000

# Slow/heavy sites
SCREENSHOT_TIMEOUT=60000
SCREENSHOT_DELAY=5000
```

### 3. Concurrent Captures

Use Laravel queues for parallel processing:
```bash
# Enable queue in .env
QUEUE_CONNECTION=database

# Run queue worker
artisan-wp queue:work --tries=3 --timeout=120
```

### 4. Disk Space Management

```bash
# Clean old temp files
find /tmp -name "puppeteer_dev_chrome_profile-*" -mtime +1 -exec rm -rf {} \;

# Monitor screenshot storage
du -sh storage/app/public/screenshots/

# Clean old screenshots (if needed)
# Be careful with this!
find storage/app/public/screenshots/ -type f -mtime +30 -delete
```

---

## Security Considerations

### 1. Chrome Sandboxing

Chrome runs with `--no-sandbox` flag for compatibility. This is **necessary** in shared hosting but has security implications:

- Chrome processes run with limited privileges
- Use `chrome:cleanup` regularly to kill zombie processes
- Monitor server resources

### 2. File Permissions

- Screenshots are publicly accessible via `/storage/screenshots/`
- Ensure proper ownership prevents unauthorized modifications
- Use `.htaccess` to prevent directory listing

### 3. Resource Limits

```bash
# Monitor Chrome memory usage
ps aux | grep chrome | awk '{sum+=$6} END {print sum/1024 " MB"}'

# Set PHP memory limit in .env
MEMORY_LIMIT=512M
```

---

## Support & Maintenance

### Regular Maintenance Tasks

**Daily:**
- Monitor Chrome cleanup cron job
- Check screenshot capture success rate

**Weekly:**
- Review error logs: `storage/logs/laravel.log`
- Check disk space: `df -h`
- Verify cron jobs running: `crontab -l`

**Monthly:**
- Update dependencies: `composer update`, `npm update`
- Review and clean old screenshots
- Test screenshot capture on sample templates

### Monitoring Commands

```bash
# Check system resources
free -h
df -h
top -bn1 | head -20

# Check Chrome processes
ps aux | grep chrome | wc -l

# Check screenshot count
ls -1 storage/app/public/screenshots/ | wc -l

# Check latest screenshot
ls -lt storage/app/public/screenshots/ | head -5
```

---

## Conclusion

This guide provides comprehensive instructions for setting up screenshot functionality on the new server. The key differences from the old server are:

1. **Document root structure** has changed (WP at `httpdocs/` level, not `public/`)
2. **Alt-PHP must be explicitly configured** in Apache, .htaccess, and cron jobs
3. **artisan-wp wrapper script** simplifies command execution
4. **All paths must be updated** to reflect new domain and structure

Follow the step-by-step setup guide carefully, test each phase, and refer to the troubleshooting section for common issues.

**For additional support:**
- Check Laravel logs: `storage/logs/laravel.log`
- Check cron logs: `storage/logs/cron-scan.log`
- Review Chrome cleanup logs: `/var/log/chrome-cleanup.log`
