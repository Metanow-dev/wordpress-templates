#!/bin/bash
# Post-deployment setup script for WordPress Templates
# Run this after deployment to ensure proper directory structure

set -e

APP_PATH="${APP_PATH:-/var/www/vhosts/wp-templates.metanow.dev/httpdocs}"
TEMPLATES_DIR="$APP_PATH/templates"

echo "🚀 Running post-deployment setup..."

# Create templates directory if it doesn't exist
if [ ! -d "$TEMPLATES_DIR" ]; then
    echo "📁 Creating templates directory: $TEMPLATES_DIR"
    mkdir -p "$TEMPLATES_DIR"
    
    # Set correct permissions
    if id -u apache >/dev/null 2>&1; then
        chown apache:apache "$TEMPLATES_DIR"
    elif id -u www-data >/dev/null 2>&1; then
        chown www-data:www-data "$TEMPLATES_DIR"
    fi
    chmod 755 "$TEMPLATES_DIR"
    echo "✅ Templates directory created and configured"
else
    echo "✅ Templates directory already exists"
fi

# Test scanner configuration
echo "🔍 Testing scanner configuration..."
cd "$APP_PATH"

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

# Test configuration
if $PHP_BIN artisan config:show templates.root >/dev/null 2>&1; then
    CONFIGURED_ROOT=$($PHP_BIN artisan config:show templates.root | grep -o '"[^"]*"' | tr -d '"')
    echo "📍 Templates root configured as: $CONFIGURED_ROOT"
    
    if [ -d "$CONFIGURED_ROOT" ]; then
        echo "✅ Templates root directory exists"
    else
        echo "⚠️  Templates root directory does not exist: $CONFIGURED_ROOT"
        echo "   Creating directory..."
        mkdir -p "$CONFIGURED_ROOT"
        chmod 755 "$CONFIGURED_ROOT"
        echo "✅ Created templates root directory"
    fi
else
    echo "⚠️  Could not read templates configuration"
fi

# Test basic scanner functionality
echo "🧪 Testing scanner functionality..."
if $PHP_BIN artisan templates:scan --limit=1 >/dev/null 2>&1; then
    echo "✅ Scanner is working correctly"
else
    echo "⚠️  Scanner test failed - this may be normal if no templates exist yet"
fi

echo "🎉 Post-deployment setup completed!"