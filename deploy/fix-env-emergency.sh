#!/bin/bash
# Emergency .env fix script for Plesk deployment
# Run this on the server if deployment is failing due to .env issues

set -e

APP_PATH="/var/www/vhosts/wp-templates.metanow.dev/httpdocs"
cd "$APP_PATH"

echo "🚨 Emergency .env configuration fix..."

# Backup existing .env if it exists
if [ -f .env ]; then
    echo "📦 Backing up existing .env..."
    cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
fi

# Create correct .env file
cat > .env << 'EOF'
# WordPress Templates Catalog - Production Environment
APP_NAME="WordPress Templates Catalog"
APP_ENV=production
APP_KEY=base64:6OwvBjyQFJFm84PDv89y9aywpILNcsmEO1kVErOpVWs=
APP_DEBUG=false
APP_URL=https://wp-templates.metanow.dev
APP_TIMEZONE=Europe/Berlin

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=wp_templates
DB_USERNAME=wp_templates_user
DB_PASSWORD="a9f7d$16M"

# Templates Configuration
TEMPLATES_ROOT=/var/www/vhosts/wp-templates.metanow.dev/httpdocs/templates
DEMO_URL_PATTERN=https://wp-templates.metanow.dev/{slug}/

# Screenshot System Configuration
CHROME_BINARY_PATH=/usr/bin/google-chrome-stable
NODE_BINARY_PATH=/usr/bin/node

# Screenshot settings
SCREENSHOT_TIMEOUT=30000
SCREENSHOT_WAIT_FOR_NETWORK_IDLE=true
SCREENSHOT_DELAY=700

# API Security
API_TOKEN=wp_templates_2024_secure_token_abc123def456ghi789jkl012mno345pqr678stu

# Cache Configuration
CACHE_DRIVER=file
CACHE_PREFIX=wptemplates

# Session Configuration
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false

# Queue Configuration
QUEUE_CONNECTION=sync

# Logging
LOG_CHANNEL=daily
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

# Security
BCRYPT_ROUNDS=12

# Localization
DEFAULT_LOCALE=en
FALLBACK_LOCALE=en

# Telescope (disable in production)
TELESCOPE_ENABLED=false

# Debugbar (disable in production)
DEBUGBAR_ENABLED=false

# Broadcasting
BROADCAST_DRIVER=log

# Filesystem
FILESYSTEM_DISK=local

# Performance
OPTIMIZE_IMAGES=true
COMPRESS_RESPONSES=true

# Rate Limiting
API_RATE_LIMIT=60
WEB_RATE_LIMIT=1000

# Browser Configuration
PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true
PUPPETEER_EXECUTABLE_PATH=/usr/bin/google-chrome-stable

# Mail Configuration (optional - for error notifications)
MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@wp-templates.metanow.dev
MAIL_FROM_NAME="${APP_NAME}"
EOF

echo "✅ Created production .env file"

# Test database connection
echo "🧪 Testing database connection..."

# Detect PHP binary
if [ -x "/opt/plesk/php/8.3/bin/php" ]; then
    PHP_BIN="/opt/plesk/php/8.3/bin/php"
elif [ -x "/opt/plesk/php/8.2/bin/php" ]; then
    PHP_BIN="/opt/plesk/php/8.2/bin/php"
elif command -v php >/dev/null 2>&1; then
    PHP_BIN="php"
else
    echo "❌ PHP not found!"
    exit 1
fi

# Clear config cache and test
$PHP_BIN artisan config:clear
if $PHP_BIN artisan migrate:status >/dev/null 2>&1; then
    echo "✅ Database connection working!"
else
    echo "❌ Database connection still failing. Check credentials."
    exit 1
fi

echo "🎉 Emergency fix completed! You can now retry deployment."