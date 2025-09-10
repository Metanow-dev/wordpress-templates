# WordPress Templates Cron Jobs Setup

## Commands Overview

### Template Scanning
```bash
cd /var/www/vhosts/wp-templates.metanow.dev/httpdocs && /opt/alt/php83/usr/bin/php artisan templates:scan --capture
```

### Screenshot Generation (Force mode)
```bash
cd /var/www/vhosts/wp-templates.metanow.dev/httpdocs && /opt/alt/php83/usr/bin/php artisan templates:screenshot --force
```

## Cron Configuration

Add these to your crontab (run `crontab -e` as the web user):

```cron
# WordPress Templates - Daily scan at 2:00 AM (includes AI classification)
0 2 * * * cd /var/www/vhosts/wp-templates.metanow.dev/httpdocs && /opt/alt/php83/usr/bin/php artisan templates:scan --capture >> /var/www/vhosts/wp-templates.metanow.dev/httpdocs/storage/logs/cron-scan.log 2>&1

# WordPress Templates - Daily screenshots at 2:30 AM  
30 2 * * * cd /var/www/vhosts/wp-templates.metanow.dev/httpdocs && /opt/alt/php83/usr/bin/php artisan templates:screenshot --force >> /var/www/vhosts/wp-templates.metanow.dev/httpdocs/storage/logs/cron-screenshots.log 2>&1

# WordPress Templates - Daily maintenance at 3:00 AM
0 3 * * * cd /var/www/vhosts/wp-templates.metanow.dev/httpdocs && /opt/alt/php83/usr/bin/php artisan config:cache && /opt/alt/php83/usr/bin/php artisan view:cache >> /var/www/vhosts/wp-templates.metanow.dev/httpdocs/storage/logs/cron-maintenance.log 2>&1
```

## Plesk Cron Jobs Setup (Alternative)

If you prefer using Plesk interface:

### Daily Scan Job
- **Command:** `/opt/alt/php83/usr/bin/php`
- **Arguments:** `artisan templates:scan --capture`
- **Run from:** `/var/www/vhosts/wp-templates.metanow.dev/httpdocs`
- **Schedule:** Daily at 2:00 AM
- **Output:** Redirect to `/var/www/vhosts/wp-templates.metanow.dev/httpdocs/storage/logs/cron-scan.log`

### Daily Screenshot Job  
- **Command:** `/opt/alt/php83/usr/bin/php`
- **Arguments:** `artisan templates:screenshot --force`
- **Run from:** `/var/www/vhosts/wp-templates.metanow.dev/httpdocs`
- **Schedule:** Daily at 2:30 AM
- **Output:** Redirect to `/var/www/vhosts/wp-templates.metanow.dev/httpdocs/storage/logs/cron-screenshots.log`

### Daily Maintenance Job
- **Command:** `/opt/alt/php83/usr/bin/php`
- **Arguments:** `artisan config:cache && /opt/alt/php83/usr/bin/php artisan view:cache`
- **Run from:** `/var/www/vhosts/wp-templates.metanow.dev/httpdocs`
- **Schedule:** Daily at 3:00 AM
- **Output:** Redirect to `/var/www/vhosts/wp-templates.metanow.dev/httpdocs/storage/logs/cron-maintenance.log`

## Manual Testing

Test the commands manually first:

```bash
# Test scanning
cd /var/www/vhosts/wp-templates.metanow.dev/httpdocs
/opt/alt/php83/usr/bin/php artisan templates:scan --capture

# Test screenshots
/opt/alt/php83/usr/bin/php artisan templates:screenshot --force

# Check logs
tail -f storage/logs/laravel.log
```

## Important Notes

1. **PHP Path:** Uses `/opt/alt/php83/usr/bin/php` to ensure PHP 8.3 compatibility
2. **Working Directory:** All commands run from the application root
3. **Logging:** Separate log files for each cron job type
4. **Permissions:** Ensure the web user has write access to storage/logs/
5. **AI Integration:** The scan command with `--capture` will automatically trigger n8n classification for new templates