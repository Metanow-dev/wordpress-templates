# Screenshot Functionality Migration Checklist
**Target Server:** https://megasandboxs.com/

## Pre-Migration Tasks

- [ ] **Backup current server data**
  ```bash
  tar -czf wp-templates-backup-$(date +%Y%m%d).tar.gz /var/www/vhosts/wp-templates.metanow.dev/httpdocs
  ```

- [ ] **Export database**
  ```bash
  mysqldump -u wp_templates_user -p wp_templates > wp_templates_$(date +%Y%m%d).sql
  ```

- [ ] **Document current setup**
  - List all installed PHP extensions
  - Note all cron jobs
  - Save current .env file

## Phase 1: System Setup (New Server)

### 1.1 Install Chrome/Chromium
- [ ] Install Chrome:
  ```bash
  sudo dnf install -y google-chrome-stable
  ```
- [ ] Verify installation:
  ```bash
  google-chrome --version
  which google-chrome-stable
  ```
- [ ] Test Chrome runs:
  ```bash
  google-chrome-stable --headless --disable-gpu --dump-dom https://www.google.com
  ```

### 1.2 Install Node.js & NPM
- [ ] Check if installed:
  ```bash
  node --version  # Need v20+
  npm --version
  ```
- [ ] Install if needed:
  ```bash
  sudo dnf module install -y nodejs:20
  ```
- [ ] Verify:
  ```bash
  which node
  which npm
  ```

### 1.3 Install PHP Extensions
- [ ] Install alt-php extensions:
  ```bash
  sudo dnf install -y \
      alt-php83-php-imagick \
      alt-php83-php-gd \
      alt-php83-php-xml \
      alt-php83-php-mbstring \
      alt-php83-php-process
  ```
- [ ] Verify installation:
  ```bash
  /opt/alt/php83/usr/bin/php -m | grep -E "imagick|gd"
  ```
- [ ] Test Imagick WebP support:
  ```bash
  /opt/alt/php83/usr/bin/php -r "echo (in_array('WEBP', Imagick::queryFormats())) ? '✓ WebP supported' : '✗ WebP NOT supported';"
  ```

### 1.4 Install Chrome Dependencies
- [ ] Install required libraries:
  ```bash
  sudo dnf install -y \
      libX11 libXcomposite libXcursor libXdamage libXext libXi \
      libXtst cups-libs libXScrnSaver libXrandr alsa-lib \
      pango at-spi2-atk gtk3 nss mesa-libgbm
  ```

## Phase 2: Application Transfer

### 2.1 Transfer Files
- [ ] Upload application files to new server:
  ```bash
  # From old server
  rsync -avz --exclude 'node_modules' --exclude 'vendor' \
      /var/www/vhosts/wp-templates.metanow.dev/httpdocs/ \
      user@megasandboxs.com:/var/www/vhosts/megasandboxs.com/httpdocs/
  ```

### 2.2 Set Directory Structure
- [ ] **IMPORTANT:** Verify directory structure:
  ```
  /var/www/vhosts/megasandboxs.com/
  └── httpdocs/              ← Document root
      ├── app/
      ├── bootstrap/
      ├── config/
      ├── public/            ← Laravel public (NOT document root)
      │   └── index.php
      ├── storage/
      ├── template1/         ← WP installations (at httpdocs level)
      ├── template2/
      ├── .env
      └── artisan
  ```

### 2.3 Install Dependencies
- [ ] Install Composer dependencies:
  ```bash
  cd /var/www/vhosts/megasandboxs.com/httpdocs
  composer install --no-dev --optimize-autoloader
  ```
- [ ] Install NPM dependencies:
  ```bash
  npm install
  ```
- [ ] Build assets:
  ```bash
  npm run build
  ```

### 2.4 Configure Storage
- [ ] Create storage symlink:
  ```bash
  php artisan storage:link
  ```
- [ ] Create screenshots directory:
  ```bash
  mkdir -p storage/app/public/screenshots
  chmod 755 storage/app/public/screenshots
  ```
- [ ] **Find system user:**
  ```bash
  ls -la /var/www/vhosts/megasandboxs.com/httpdocs | head -1
  # Note the owner (e.g., megasandboxs_abc123def)
  ```
- [ ] Set ownership (replace USER):
  ```bash
  chown -R USER:psacln storage/app/public/screenshots
  ```

## Phase 3: Configuration

### 3.1 Update .env File
- [ ] Get system user name:
  ```bash
  SYSTEM_USER=$(ls -ld /var/www/vhosts/megasandboxs.com/httpdocs | awk '{print $3}')
  echo "System user: $SYSTEM_USER"
  ```
- [ ] Edit .env and update:
  ```env
  APP_URL=https://megasandboxs.com
  TEMPLATES_ROOT=/var/www/vhosts/megasandboxs.com/httpdocs
  DEMO_URL_PATTERN=https://megasandboxs.com/{slug}/
  CHROME_BINARY_PATH=/usr/bin/google-chrome-stable
  NODE_BINARY_PATH=/usr/bin/node
  SCREENSHOT_SYSUSER=<SYSTEM_USER_FROM_ABOVE>
  SCREENSHOT_GROUP=psacln
  ```
- [ ] Clear config cache:
  ```bash
  /opt/alt/php83/usr/bin/php artisan config:clear
  ```

### 3.2 Create artisan-wp Wrapper
- [ ] Create script:
  ```bash
  sudo nano /usr/local/bin/artisan-wp
  ```
- [ ] Add content (replace USER and megasandboxs.com):
  ```bash
  #!/bin/bash
  cd /var/www/vhosts/megasandboxs.com/httpdocs
  sudo -u SYSTEM_USER /opt/alt/php83/usr/bin/php artisan "$@"
  ```
- [ ] Make executable:
  ```bash
  sudo chmod +x /usr/local/bin/artisan-wp
  ```
- [ ] Test:
  ```bash
  artisan-wp --version
  ```

### 3.3 Configure Apache
- [ ] Open Plesk → Domains → megasandboxs.com → Apache & nginx Settings
- [ ] Add to "Additional directives for HTTP":
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
- [ ] Click "OK" to save

### 3.4 Configure Document Root
- [ ] Open Plesk → Domains → megasandboxs.com → Hosting Settings
- [ ] Set **Document root** to: `httpdocs`
- [ ] Enable PHP
- [ ] Select PHP version: 8.3 (alt-php)
- [ ] Click "OK"

### 3.5 Update .htaccess
- [ ] Edit `/var/www/vhosts/megasandboxs.com/httpdocs/public/.htaccess`
- [ ] Ensure it has alt-php handler at the top:
  ```apache
  <IfModule mime_module>
      AddType application/x-httpd-alt-php83 .php
  </IfModule>
  <IfModule lsapi_module>
      AddHandler application/x-httpd-alt-php83 .php
  </IfModule>
  ```

### 3.6 Restart Services
- [ ] Restart Apache:
  ```bash
  sudo systemctl restart httpd
  ```
- [ ] Restart PHP-FPM (if used):
  ```bash
  sudo systemctl restart php83-php-fpm
  ```

## Phase 4: Database Migration

### 4.1 Import Database
- [ ] Create database and user in Plesk
- [ ] Import database:
  ```bash
  mysql -u newuser -p newdatabase < wp_templates_backup.sql
  ```
- [ ] Update .env with new database credentials

### 4.2 Update WordPress URLs
- [ ] Update wp_options table for each WordPress installation:
  ```sql
  -- For each template, update URLs
  UPDATE wp_options SET option_value = 'https://megasandboxs.com/SLUG/'
  WHERE option_name IN ('siteurl', 'home');
  ```
- [ ] Or use WP-CLI:
  ```bash
  cd /var/www/vhosts/megasandboxs.com/httpdocs/SLUG
  wp search-replace 'https://wp-templates.metanow.dev/SLUG/' 'https://megasandboxs.com/SLUG/'
  ```

## Phase 5: Testing

### 5.1 Basic Tests
- [ ] Test application loads:
  ```bash
  curl -I https://megasandboxs.com/
  ```
- [ ] Test Laravel routes:
  ```bash
  artisan-wp route:list
  ```
- [ ] Test database connection:
  ```bash
  artisan-wp tinker
  # In tinker: DB::connection()->getPdo();
  ```

### 5.2 Screenshot Tests
- [ ] Test Chrome directly:
  ```bash
  google-chrome-stable --headless --disable-gpu --screenshot --window-size=1200,800 https://www.google.com
  ```
- [ ] Test Puppeteer:
  ```bash
  node -e "require('puppeteer').launch({headless:true}).then(b=>console.log('✓ Puppeteer works!') && b.close())"
  ```
- [ ] Test screenshot command:
  ```bash
  artisan-wp templates:screenshot --slug=test-template --force
  ```
- [ ] Verify screenshot created:
  ```bash
  ls -la storage/app/public/screenshots/test-template*
  ```
- [ ] Test browser access:
  ```
  https://megasandboxs.com/storage/screenshots/test-template.png
  ```

### 5.3 Permission Tests
- [ ] Check file ownership:
  ```bash
  ls -la storage/app/public/screenshots/ | head -10
  ```
- [ ] Run permission fix:
  ```bash
  artisan-wp templates:fix-permissions
  ```
- [ ] Verify permissions (should be 644 for files, 755 for dirs):
  ```bash
  stat storage/app/public/screenshots/test-template.png
  ```

### 5.4 Full System Test
- [ ] Scan templates:
  ```bash
  artisan-wp templates:scan
  ```
- [ ] Capture new screenshots:
  ```bash
  artisan-wp templates:screenshot --new-only
  ```
- [ ] Run Chrome cleanup:
  ```bash
  artisan-wp chrome:cleanup --force
  ```
- [ ] Check for errors:
  ```bash
  tail -50 storage/logs/laravel.log
  ```

## Phase 6: Cron Jobs

### 6.1 Setup Cron Environment
- [ ] Edit crontab:
  ```bash
  crontab -e
  ```
- [ ] Add environment variables at top:
  ```cron
  MAILTO=""
  ARTISAN=/usr/local/bin/artisan-wp
  LOGDIR=/var/www/vhosts/megasandboxs.com/httpdocs/storage/logs
  PATH="/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin"
  ```

### 6.2 Add Cron Jobs
- [ ] Add template scan jobs:
  ```cron
  0 12 * * * $ARTISAN templates:scan >> $LOGDIR/cron-scan.log 2>&1
  0 19 * * * $ARTISAN templates:scan >> $LOGDIR/cron-scan.log 2>&1
  ```
- [ ] Add Chrome cleanup job:
  ```cron
  */15 * * * * $ARTISAN chrome:cleanup --force >> /var/log/chrome-cleanup.log 2>&1
  ```

### 6.3 Test Cron Jobs
- [ ] Run manually:
  ```bash
  /usr/local/bin/artisan-wp templates:scan
  ```
- [ ] Check logs:
  ```bash
  tail -f storage/logs/cron-scan.log
  ```
- [ ] Verify cron is scheduled:
  ```bash
  crontab -l
  ```

## Phase 7: Monitoring & Validation

### 7.1 Create Log Monitoring
- [ ] Create log directory:
  ```bash
  mkdir -p /var/log/wp-templates
  ```
- [ ] Setup log rotation (optional):
  ```bash
  sudo nano /etc/logrotate.d/wp-templates
  ```
  ```
  /var/log/chrome-cleanup.log
  /var/www/vhosts/megasandboxs.com/httpdocs/storage/logs/*.log {
      daily
      missingok
      rotate 14
      compress
      delaycompress
      notifempty
      create 0640 SYSTEM_USER psacln
  }
  ```

### 7.2 Monitor System Resources
- [ ] Check disk space:
  ```bash
  df -h
  ```
- [ ] Check memory usage:
  ```bash
  free -h
  ```
- [ ] Monitor Chrome processes:
  ```bash
  watch 'ps aux | grep chrome | wc -l'
  ```

### 7.3 Verify All Functionality
- [ ] Test main application: https://megasandboxs.com/
- [ ] Test template demos: https://megasandboxs.com/template-name/
- [ ] Test screenshot loading: https://megasandboxs.com/storage/screenshots/
- [ ] Test admin functions (if any)
- [ ] Test API endpoints (if any)

## Phase 8: Post-Migration

### 8.1 DNS & SSL
- [ ] Update DNS records to point to new server
- [ ] Verify SSL certificate is valid
- [ ] Test HTTPS redirects work

### 8.2 Performance Optimization
- [ ] Enable OpCache:
  ```bash
  # Check if enabled
  /opt/alt/php83/usr/bin/php -i | grep opcache.enable
  ```
- [ ] Optimize composer autoloader:
  ```bash
  composer dump-autoload --optimize
  ```
- [ ] Cache Laravel config:
  ```bash
  artisan-wp config:cache
  artisan-wp route:cache
  artisan-wp view:cache
  ```

### 8.3 Security Hardening
- [ ] Disable debug mode:
  ```env
  APP_DEBUG=false
  ```
- [ ] Secure .env file:
  ```bash
  chmod 600 .env
  ```
- [ ] Setup firewall rules (if needed)
- [ ] Review file permissions:
  ```bash
  find . -type f -exec chmod 644 {} \;
  find . -type d -exec chmod 755 {} \;
  chmod -R 775 storage bootstrap/cache
  ```

### 8.4 Backup Setup
- [ ] Setup automated backups in Plesk
- [ ] Test backup restoration
- [ ] Document backup schedule

## Phase 9: Decommission Old Server

### 9.1 Verification Period
- [ ] Run both servers in parallel for 1-2 weeks
- [ ] Monitor for any issues
- [ ] Compare screenshot outputs
- [ ] Verify all cron jobs running correctly

### 9.2 Final Cutover
- [ ] Stop cron jobs on old server
- [ ] Redirect old domain to new (if applicable)
- [ ] Archive old server data
- [ ] Document any remaining differences

## Troubleshooting Checklist

If screenshots fail, check these in order:

1. **Chrome Installation**
   - [ ] `which google-chrome-stable`
   - [ ] `google-chrome-stable --version`

2. **Node.js/Puppeteer**
   - [ ] `which node`
   - [ ] `npm list puppeteer`

3. **PHP Extensions**
   - [ ] `/opt/alt/php83/usr/bin/php -m | grep imagick`
   - [ ] `/opt/alt/php83/usr/bin/php -m | grep gd`

4. **Permissions**
   - [ ] `ls -la storage/app/public/screenshots/`
   - [ ] Owner should match system user
   - [ ] Permissions: 755 for dirs, 644 for files

5. **Configuration**
   - [ ] `.env` has correct paths
   - [ ] Apache has alt-php handler
   - [ ] `.htaccess` has alt-php handler
   - [ ] `artisan-wp` wrapper exists and is executable

6. **Processes**
   - [ ] `ps aux | grep chrome` (should be minimal)
   - [ ] `artisan-wp chrome:cleanup --dry-run`

7. **Logs**
   - [ ] `tail -100 storage/logs/laravel.log`
   - [ ] `tail -100 storage/logs/cron-scan.log`
   - [ ] `/var/log/httpd/error_log`

## Quick Command Reference

```bash
# Screenshot commands
artisan-wp templates:screenshot --slug=SLUG --force
artisan-wp templates:screenshot --new-only
artisan-wp chrome:cleanup --force

# Testing
artisan-wp tinker
curl -I https://megasandboxs.com/
ls -la storage/app/public/screenshots/

# Logs
tail -f storage/logs/laravel.log
tail -f storage/logs/cron-scan.log
tail -f /var/log/httpd/error_log

# Permissions
artisan-wp templates:fix-permissions
chown -R USER:psacln storage/app/public/screenshots

# Cache
artisan-wp cache:clear
artisan-wp config:clear
artisan-wp route:clear
```

## Success Criteria

✅ All items checked means migration is complete:

- [ ] Application loads at https://megasandboxs.com/
- [ ] All WordPress templates accessible
- [ ] Screenshots can be captured manually
- [ ] Screenshots display in browser (no 403/404)
- [ ] Cron jobs running and logging properly
- [ ] No Chrome zombie processes accumulating
- [ ] All PHP extensions working (imagick, gd)
- [ ] Alt-PHP being used (not system PHP)
- [ ] File permissions correct
- [ ] Database connected and working
- [ ] SSL certificate valid
- [ ] Error logs clean (no critical errors)

## Rollback Plan

If migration fails:

1. **Immediate rollback:**
   - Point DNS back to old server
   - Restore old .env on old server
   - Resume cron jobs on old server

2. **Data preservation:**
   - Keep backup of new server attempt
   - Document what failed
   - Review logs before cleanup

3. **Re-attempt:**
   - Fix identified issues
   - Test in staging if available
   - Follow checklist more carefully

---

**Document Version:** 1.0
**Last Updated:** December 2025
**For Support:** See SCREENSHOT_SETUP_GUIDE.md
