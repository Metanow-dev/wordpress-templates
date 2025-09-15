#!/bin/bash

# WordPress Templates - Cron Jobs Setup Script
# Run this script to set up automated template scanning and screenshot generation

echo "Setting up WordPress Templates cron jobs..."

# Get the current user (should be the web user)
WEB_USER=$(whoami)
APP_PATH="/var/www/vhosts/wp-templates.metanow.dev/httpdocs"
PHP_PATH="/opt/alt/php83/usr/bin/php"

echo "Web User: $WEB_USER"
echo "App Path: $APP_PATH"
echo "PHP Path: $PHP_PATH"

# Create logs directory if it doesn't exist
mkdir -p "$APP_PATH/storage/logs"

# Create cron jobs
echo "Creating cron jobs..."

# Backup existing crontab
crontab -l > /tmp/crontab_backup_$(date +%Y%m%d_%H%M%S) 2>/dev/null || echo "No existing crontab found"

# Create new cron jobs
(crontab -l 2>/dev/null || echo "") | grep -v "WordPress Templates" > /tmp/new_crontab

cat >> /tmp/new_crontab << EOF

# WordPress Templates - Daily Automated Jobs
# Daily scan at 2:00 AM (includes AI classification and screenshots for new templates)
0 2 * * * cd $APP_PATH && $PHP_PATH artisan config:clear && $PHP_PATH artisan cache:clear && $PHP_PATH artisan templates:scan >> $APP_PATH/storage/logs/cron-scan.log 2>&1

# Daily maintenance at 3:00 AM
0 3 * * * cd $APP_PATH && $PHP_PATH artisan config:cache && $PHP_PATH artisan view:cache >> $APP_PATH/storage/logs/cron-maintenance.log 2>&1

EOF

# Install the new crontab
crontab /tmp/new_crontab

echo "✓ Cron jobs installed successfully!"

# Show current crontab
echo ""
echo "Current cron jobs:"
crontab -l | grep -A 10 "WordPress Templates"

# Test commands
echo ""
echo "Testing commands..."

echo "1. Testing template scan..."
cd "$APP_PATH" && $PHP_PATH artisan templates:scan --help > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo "✓ Template scan command works"
else
    echo "✗ Template scan command failed"
fi

echo "2. Testing screenshot command..."
cd "$APP_PATH" && $PHP_PATH artisan templates:screenshot --help > /dev/null 2>&1
if [ $? -eq 0 ]; then
    echo "✓ Screenshot command works"
else
    echo "✗ Screenshot command failed"
fi

echo "3. Testing PHP and Laravel..."
cd "$APP_PATH" && $PHP_PATH artisan --version
if [ $? -eq 0 ]; then
    echo "✓ Laravel and PHP working correctly"
else
    echo "✗ Laravel or PHP configuration issue"
fi

# Set proper permissions for log files
echo ""
echo "Setting log permissions..."
touch "$APP_PATH/storage/logs/cron-scan.log"
touch "$APP_PATH/storage/logs/cron-screenshots.log" 
touch "$APP_PATH/storage/logs/cron-maintenance.log"
chmod 644 "$APP_PATH/storage/logs/cron-*.log"

echo ""
echo "🎉 Setup complete!"
echo ""
echo "Monitor logs with:"
echo "  tail -f $APP_PATH/storage/logs/cron-scan.log"
echo "  tail -f $APP_PATH/storage/logs/cron-screenshots.log"
echo "  tail -f $APP_PATH/storage/logs/cron-maintenance.log"
echo ""
echo "Manual test commands:"
echo "  cd $APP_PATH && $PHP_PATH artisan templates:scan --capture"
echo "  cd $APP_PATH && $PHP_PATH artisan templates:screenshot --force"

# Cleanup
rm /tmp/new_crontab