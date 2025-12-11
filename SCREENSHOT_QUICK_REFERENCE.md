# Screenshot Functionality - Quick Reference Card

**New Server:** https://megasandboxs.com/

## 🚀 Quick Start

```bash
# 1. Capture screenshot for one template
artisan-wp templates:screenshot --slug=template-name --force

# 2. Check if it worked
ls -la storage/app/public/screenshots/template-name*

# 3. Test in browser
curl -I https://megasandboxs.com/storage/screenshots/template-name.png
```

## 📋 Essential Commands

### Screenshot Capture
```bash
# Single template
artisan-wp templates:screenshot --slug=SLUG --force

# All new templates (without existing screenshots)
artisan-wp templates:screenshot --new-only

# All templates (force recapture)
artisan-wp templates:screenshot --force

# Skip problematic sites
artisan-wp templates:screenshot --skip-problematic

# Full-page screenshot
artisan-wp templates:screenshot --slug=SLUG --fullpage
```

### Maintenance
```bash
# Fix permissions
artisan-wp templates:fix-permissions

# Clean Chrome zombies
artisan-wp chrome:cleanup --force

# Scan templates
artisan-wp templates:scan

# Clear caches
artisan-wp cache:clear && artisan-wp config:clear
```

### Debugging
```bash
# Check Chrome
which google-chrome-stable
google-chrome-stable --version

# Check Node/Puppeteer
which node
npm list puppeteer

# Check PHP extensions
/opt/alt/php83/usr/bin/php -m | grep -E "imagick|gd"

# Check processes
ps aux | grep chrome

# View logs
tail -50 storage/logs/laravel.log
tail -50 /var/log/httpd/error_log
```

## 🔧 Common Issues & Quick Fixes

### Issue: "Chrome binary not found"
```bash
# Fix 1: Install Chrome
sudo dnf install -y google-chrome-stable

# Fix 2: Update .env
nano .env
# Add: CHROME_BINARY_PATH=/usr/bin/google-chrome-stable

# Fix 3: Clear config
artisan-wp config:clear
```

### Issue: "Permission denied" / 403 Error
```bash
# Quick fix
artisan-wp templates:fix-permissions

# Manual fix (replace USER with actual system user)
cd /var/www/vhosts/megasandboxs.com/httpdocs
chown -R USER:psacln storage/app/public/screenshots
chmod 755 storage/app/public/screenshots
find storage/app/public/screenshots -type f -exec chmod 644 {} \;
```

### Issue: "Imagick/GD not found"
```bash
# Install extensions
sudo dnf install -y alt-php83-php-imagick alt-php83-php-gd

# Verify
/opt/alt/php83/usr/bin/php -m | grep imagick

# Restart Apache
sudo systemctl restart httpd
```

### Issue: "Node not found" / Puppeteer error
```bash
# Install Node.js
sudo dnf module install -y nodejs:20

# Reinstall Puppeteer
cd /var/www/vhosts/megasandboxs.com/httpdocs
rm -rf node_modules package-lock.json
npm install
```

### Issue: Screenshot timeout
```bash
# Increase timeout in .env
nano .env
# Add/update:
SCREENSHOT_TIMEOUT=60000
SCREENSHOT_DELAY=5000

# Clear config
artisan-wp config:clear

# Try again with specific template
artisan-wp templates:screenshot --slug=PROBLEMATIC_SLUG --force
```

### Issue: Chrome zombie processes
```bash
# Check processes
ps aux | grep chrome | wc -l

# Kill zombies
artisan-wp chrome:cleanup --force

# Manual kill (if needed)
pkill -9 chrome

# Clean temp files
rm -rf /tmp/puppeteer_dev_chrome_profile-*
```

### Issue: Cron jobs not running
```bash
# Verify cron setup
crontab -l

# Test manual run
/usr/local/bin/artisan-wp templates:scan

# Check logs
tail -f storage/logs/cron-scan.log

# Ensure artisan-wp wrapper exists
cat /usr/local/bin/artisan-wp
```

### Issue: Alt-PHP not being used
```bash
# Check which PHP is being used
which php
# Should show: /opt/alt/php83/usr/bin/php

# Always use full path in cron:
/opt/alt/php83/usr/bin/php artisan templates:screenshot
# OR use wrapper:
/usr/local/bin/artisan-wp templates:screenshot

# Verify Apache config has alt-php handler
# Check: Plesk → Apache & nginx Settings → Additional directives
```

## 📁 Important File Locations

### Configuration Files
```
.env                                    - Environment config
public/.htaccess                        - Alt-PHP handler
/usr/local/bin/artisan-wp              - Wrapper script
```

### Application Files
```
app/Support/Screenshotter.php          - Screenshot logic
app/Console/Commands/CaptureTemplateScreenshots.php
app/Console/Commands/CleanupChromeProcessesCommand.php
app/Console/Commands/FixScreenshotPermissions.php
```

### Output & Logs
```
storage/app/public/screenshots/        - Screenshot files
storage/logs/laravel.log               - Laravel logs
storage/logs/cron-scan.log             - Cron job logs
/var/log/chrome-cleanup.log            - Cleanup logs
/var/log/httpd/error_log               - Apache errors
```

## 🔍 System Checks

### Pre-flight Check (Run this first)
```bash
echo "=== System Check ==="
echo "Chrome: $(which google-chrome-stable)"
echo "Node: $(which node) - $(node --version 2>/dev/null)"
echo "Alt-PHP: $(which php)"
echo "PHP Version: $(/opt/alt/php83/usr/bin/php -v | head -1)"
echo "Imagick: $(/opt/alt/php83/usr/bin/php -m | grep imagick)"
echo "GD: $(/opt/alt/php83/usr/bin/php -m | grep gd)"
echo "Puppeteer: $(npm list puppeteer 2>/dev/null | grep puppeteer)"
echo "Chrome Processes: $(ps aux | grep chrome | grep -v grep | wc -l)"
echo "Screenshot Count: $(ls -1 storage/app/public/screenshots/*.png 2>/dev/null | wc -l)"
echo "Storage Symlink: $(test -L public/storage && echo '✓ OK' || echo '✗ MISSING')"
```

### Health Check
```bash
# Run this to diagnose issues
echo "=== Health Check ==="

# Test Chrome
google-chrome-stable --headless --disable-gpu --screenshot --window-size=1200,800 https://www.google.com && echo "✓ Chrome OK" || echo "✗ Chrome FAILED"

# Test Puppeteer
node -e "require('puppeteer').launch({headless:true}).then(b=>{console.log('✓ Puppeteer OK'); b.close();})" || echo "✗ Puppeteer FAILED"

# Test screenshot command
artisan-wp templates:screenshot --slug=test --force && echo "✓ Screenshot OK" || echo "✗ Screenshot FAILED"

# Check permissions
test -w storage/app/public/screenshots && echo "✓ Permissions OK" || echo "✗ Permissions DENIED"
```

## 📊 Monitoring

### Resource Usage
```bash
# Disk space
df -h /var/www/vhosts/megasandboxs.com

# Memory usage
free -h

# Chrome memory
ps aux | grep chrome | awk '{sum+=$6} END {print "Chrome using: " sum/1024 " MB"}'

# Screenshot storage size
du -sh storage/app/public/screenshots/
```

### Process Monitoring
```bash
# Chrome processes
watch -n 5 'ps aux | grep chrome | grep -v grep | wc -l'

# Latest screenshots
ls -lt storage/app/public/screenshots/*.png | head -5

# Recent errors
tail -20 storage/logs/laravel.log | grep ERROR
```

## 🔐 Security Checklist

- [ ] `.env` file permissions: `chmod 600 .env`
- [ ] Storage ownership correct: `chown -R USER:psacln storage/`
- [ ] Chrome runs with `--no-sandbox` (required but monitor processes)
- [ ] Screenshots publicly accessible but directory listing disabled
- [ ] Regular cleanup of Chrome zombies via cron
- [ ] Monitor disk space usage
- [ ] APP_DEBUG=false in production

## 🆘 Emergency Commands

### Stop all screenshots immediately
```bash
# Kill all Chrome processes
pkill -9 chrome

# Stop all artisan commands
pkill -f "php.*artisan"

# Clean temp files
rm -rf /tmp/puppeteer_dev_chrome_profile-*
```

### Reset screenshot system
```bash
# 1. Stop everything
pkill -9 chrome

# 2. Clear temp files
rm -rf /tmp/puppeteer_dev_chrome_profile-*

# 3. Reinstall Puppeteer
cd /var/www/vhosts/megasandboxs.com/httpdocs
rm -rf node_modules package-lock.json
npm install

# 4. Clear Laravel caches
artisan-wp cache:clear
artisan-wp config:clear

# 5. Fix permissions
artisan-wp templates:fix-permissions

# 6. Test
artisan-wp templates:screenshot --slug=test --force
```

## 📝 Environment Variables Reference

**Critical .env settings:**
```env
APP_URL=https://megasandboxs.com
TEMPLATES_ROOT=/var/www/vhosts/megasandboxs.com/httpdocs
DEMO_URL_PATTERN=https://megasandboxs.com/{slug}/

CHROME_BINARY_PATH=/usr/bin/google-chrome-stable
NODE_BINARY_PATH=/usr/bin/node

SCREENSHOT_TIMEOUT=30000
SCREENSHOT_DELAY=3500
SCREENSHOT_SYSUSER=megasandboxs_USER
SCREENSHOT_GROUP=psacln
```

**Find system user:**
```bash
ls -ld /var/www/vhosts/megasandboxs.com/httpdocs | awk '{print $3}'
```

## 🎯 Testing Workflow

### Test single template end-to-end
```bash
# 1. Capture
artisan-wp templates:screenshot --slug=test-template --force

# 2. Verify files created
ls -la storage/app/public/screenshots/test-template*
# Should show:
# test-template.png
# test-template-480.webp
# test-template-768.webp
# test-template-1024.webp

# 3. Check permissions
stat storage/app/public/screenshots/test-template.png
# Should show: 644, owned by system user

# 4. Test browser access
curl -I https://megasandboxs.com/storage/screenshots/test-template.png
# Should show: 200 OK

# 5. Check logs for errors
tail -20 storage/logs/laravel.log
```

## 💡 Tips & Tricks

### Speed up screenshots
- Use `--skip-problematic` flag for known slow sites
- Reduce `SCREENSHOT_DELAY` for fast-loading sites
- Use `--new-only` flag to avoid recapturing existing screenshots

### Batch operations
```bash
# Capture specific templates only
for slug in template1 template2 template3; do
    artisan-wp templates:screenshot --slug=$slug --force
done

# Capture all except problematic ones
artisan-wp templates:screenshot --skip-problematic --new-only
```

### Monitor in real-time
```bash
# Watch logs while capturing
tail -f storage/logs/laravel.log &
artisan-wp templates:screenshot --new-only
```

### Schedule during off-hours
```cron
# Run at 2 AM when server is less busy
0 2 * * * $ARTISAN templates:screenshot --new-only >> $LOGDIR/nightly-screenshots.log 2>&1
```

## 📞 Getting Help

### Check these first:
1. Review `storage/logs/laravel.log`
2. Check Apache logs: `/var/log/httpd/error_log`
3. Verify all dependencies installed
4. Ensure alt-php is being used
5. Check file permissions

### Useful diagnostic info to collect:
```bash
# System info
uname -a
cat /etc/redhat-release

# PHP info
/opt/alt/php83/usr/bin/php -v
/opt/alt/php83/usr/bin/php -m

# Binary locations
which google-chrome-stable node npm

# Process info
ps aux | grep chrome
ps aux | grep php.*artisan

# Disk space
df -h

# Recent errors
tail -50 storage/logs/laravel.log | grep ERROR
tail -50 /var/log/httpd/error_log | grep -i error
```

---

**Keep this reference handy for quick troubleshooting!**

**Full documentation:** See `SCREENSHOT_SETUP_GUIDE.md`
**Migration checklist:** See `MIGRATION_CHECKLIST.md`
