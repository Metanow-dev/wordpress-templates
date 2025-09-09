#!/bin/bash
# Emergency fix for TEMPLATES_ROOT issue
# Run this on the server if deployment fails due to empty TEMPLATES_ROOT

set -e

APP_PATH="/var/www/vhosts/wp-templates.metanow.dev/httpdocs"
EXPECTED_TEMPLATES_ROOT="/var/www/vhosts/wp-templates.metanow.dev/httpdocs/templates"

cd "$APP_PATH"

echo "🚨 Emergency TEMPLATES_ROOT fix..."

# Check if .env exists
if [ ! -f .env ]; then
    echo "❌ No .env file found!"
    exit 1
fi

# Check what's in TEMPLATES_ROOT
CURRENT_TEMPLATES_ROOT=$(grep "TEMPLATES_ROOT=" .env | cut -d'=' -f2 || echo "")
echo "Current TEMPLATES_ROOT: '$CURRENT_TEMPLATES_ROOT'"

# If empty or wrong, fix it
if [ -z "$CURRENT_TEMPLATES_ROOT" ] || [ "$CURRENT_TEMPLATES_ROOT" = "" ]; then
    echo "📝 Fixing empty TEMPLATES_ROOT in .env..."
    
    # Remove any existing TEMPLATES_ROOT line
    sed -i '/^TEMPLATES_ROOT=/d' .env
    
    # Add correct TEMPLATES_ROOT
    sed -i '/^# Templates Configuration/a TEMPLATES_ROOT=/var/www/vhosts/wp-templates.metanow.dev/httpdocs/templates' .env
    
    echo "✅ Updated TEMPLATES_ROOT in .env"
fi

# Create the directory
echo "📁 Creating templates directory..."
mkdir -p "$EXPECTED_TEMPLATES_ROOT"
chmod 755 "$EXPECTED_TEMPLATES_ROOT"

# Set ownership
if id -u apache >/dev/null 2>&1; then
    chown apache:apache "$EXPECTED_TEMPLATES_ROOT"
elif id -u www-data >/dev/null 2>&1; then
    chown www-data:www-data "$EXPECTED_TEMPLATES_ROOT"
fi

echo "✅ Templates directory created: $EXPECTED_TEMPLATES_ROOT"

# Clear and refresh config
echo "🔄 Clearing config cache..."

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

$PHP_BIN artisan config:clear
$PHP_BIN artisan config:cache

# Test the configuration
echo "🧪 Testing configuration..."
TEMPLATES_ROOT_TEST=$($PHP_BIN artisan config:show templates.root 2>/dev/null | grep -o '"[^"]*"' | tr -d '"' || echo "")

if [ -n "$TEMPLATES_ROOT_TEST" ] && [ "$TEMPLATES_ROOT_TEST" != "" ]; then
    echo "✅ TEMPLATES_ROOT now configured as: $TEMPLATES_ROOT_TEST"
    
    # Test scanner
    echo "🔍 Testing scanner..."
    if $PHP_BIN artisan templates:scan --limit=1 >/dev/null 2>&1; then
        echo "✅ Scanner is working!"
    else
        echo "⚠️  Scanner test failed (this may be normal if no templates exist yet)"
    fi
else
    echo "❌ TEMPLATES_ROOT still empty after fix"
    echo "Debug: .env file content:"
    grep -A 5 -B 5 "TEMPLATES_ROOT" .env || echo "TEMPLATES_ROOT not found in .env"
fi

echo "🎉 Emergency fix completed!"