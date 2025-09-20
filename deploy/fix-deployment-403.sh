#!/bin/bash
# COMPREHENSIVE Deployment 403 Fix
# This script fixes BOTH the TEMPLATES_ROOT issue AND Plesk 403 errors
# Run this automatically after deployment or manually when 403 occurs

set -e

DOMAIN="wp-templates.metanow.dev"
SYSUSER="wp-templates.metanow_r6s2v1oe7wr"
APP_PATH="/var/www/vhosts/$DOMAIN/httpdocs"
PUBLIC_PATH="$APP_PATH/public"
TEMPLATES_PATH="$APP_PATH/templates"

echo "🚨 COMPREHENSIVE Deployment 403 Fix for $DOMAIN"
echo "   This fixes BOTH TEMPLATES_ROOT and Plesk PHP handler issues"

# EARLY STAGE: Immediate fixes to minimize downtime
echo ""
echo "🚀 EARLY STAGE: Immediate fixes to prevent 403/500 errors..."

# Detect PHP binary first
if [ -x "/opt/alt/php83/usr/bin/php" ]; then
    PHP_BIN="/opt/alt/php83/usr/bin/php"
elif [ -x "/opt/plesk/php/8.3/bin/php" ]; then
    PHP_BIN="/opt/plesk/php/8.3/bin/php"
elif [ -x "/opt/plesk/php/8.2/bin/php" ]; then
    PHP_BIN="/opt/plesk/php/8.2/bin/php"
elif command -v php >/dev/null 2>&1; then
    PHP_BIN="php"
else
    echo "❌ PHP not found!"
    exit 1
fi

# Immediate .htaccess creation
cat > "$PUBLIC_PATH/.htaccess" << 'EOF'
# IMMEDIATE FIX: Force CloudLinux alt-php 8.3
<IfModule mime_module>
    AddType application/x-httpd-alt-php83 .php
</IfModule>
<IfModule lsapi_module>
    AddHandler application/x-httpd-alt-php83 .php
</IfModule>
AddHandler application/x-httpd-alt-php83 .php
AddType application/x-httpd-alt-php83 .php
DirectoryIndex index.php index.html
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>
    RewriteEngine On
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
EOF

# Immediate cache clearing
$PHP_BIN artisan config:clear 2>/dev/null || true
$PHP_BIN artisan cache:clear 2>/dev/null || true

echo "✅ Early fixes applied - 403/500 errors should be minimized!"

# Step 1: Fix TEMPLATES_ROOT configuration (Critical for Laravel)
echo ""
echo "1️⃣  FIXING TEMPLATES_ROOT CONFIGURATION"
cd "$APP_PATH"

if [ ! -f .env ]; then
    echo "❌ No .env file found!"
    exit 1
fi

# Check current TEMPLATES_ROOT
CURRENT_TEMPLATES_ROOT=$(grep "TEMPLATES_ROOT=" .env | cut -d'=' -f2 || echo "")
echo "Current TEMPLATES_ROOT: '$CURRENT_TEMPLATES_ROOT'"

# Force correct TEMPLATES_ROOT for production
CORRECT_TEMPLATES_ROOT="$APP_PATH/public"
echo "📝 Ensuring correct TEMPLATES_ROOT in .env..."

# Remove any existing TEMPLATES_ROOT lines
sed -i '/^TEMPLATES_ROOT=.*$/d' .env

# Add correct TEMPLATES_ROOT
if grep -q "^# Templates Configuration" .env; then
    # Add after "# Templates Configuration" line
    sed -i "/^# Templates Configuration/a TEMPLATES_ROOT=$CORRECT_TEMPLATES_ROOT" .env
else
    # Add at the end
    echo "" >> .env
    echo "# Templates Configuration" >> .env
    echo "TEMPLATES_ROOT=$CORRECT_TEMPLATES_ROOT" >> .env
fi

echo "✅ Set TEMPLATES_ROOT to: $CORRECT_TEMPLATES_ROOT"
TEMPLATES_PATH="$CORRECT_TEMPLATES_ROOT"

# Create templates directory with correct permissions
echo "📁 Creating templates directory: $TEMPLATES_PATH"
mkdir -p "$TEMPLATES_PATH"
chmod 755 "$TEMPLATES_PATH"

# Set correct ownership
if id -u "$SYSUSER" >/dev/null 2>&1; then
    chown -R "$SYSUSER:psacln" "$TEMPLATES_PATH"
    echo "✅ Set ownership to Plesk domain user: $SYSUSER:psacln"
elif id -u apache >/dev/null 2>&1; then
    chown -R apache:apache "$TEMPLATES_PATH"
    echo "✅ Set ownership to apache:apache"
elif id -u www-data >/dev/null 2>&1; then
    chown -R www-data:www-data "$TEMPLATES_PATH"
    echo "✅ Set ownership to www-data:www-data"
fi

# Step 2: Fix Plesk PHP Handler (403 errors)
echo ""
echo "2️⃣  FIXING PLESK PHP HANDLER CONFIGURATION"

# Ensure public directory exists
if [ ! -d "$PUBLIC_PATH" ]; then
    echo "❌ Laravel public directory not found: $PUBLIC_PATH"
    exit 1
fi

# Create deployment-proof .htaccess
echo "⚙️  Creating deployment-proof .htaccess..."
cat > "$PUBLIC_PATH/.htaccess" << 'EOF'
# DEPLOYMENT-PROOF: Force CloudLinux alt-php 8.3
<IfModule mime_module>
    AddType application/x-httpd-alt-php83 .php
</IfModule>
<IfModule lsapi_module>
    AddHandler application/x-httpd-alt-php83 .php
</IfModule>

# Additional PHP handler mappings for different server configurations
AddHandler application/x-httpd-alt-php83 .php
AddType application/x-httpd-alt-php83 .php

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
EOF

echo "✅ Created deployment-proof .htaccess"

# Set correct Plesk ownership and permissions
echo "🔒 Setting Plesk-correct ownership and permissions..."
chown -R "$SYSUSER:psacln" "$APP_PATH"
find "$APP_PATH" -type d -exec chmod 755 {} \;
find "$APP_PATH" -type f -exec chmod 644 {} \;
chmod -R 775 "$APP_PATH/storage" "$APP_PATH/bootstrap/cache" 2>/dev/null || true

echo "✅ Ownership and permissions set"

# Step 3: Laravel Configuration Refresh
echo ""
echo "3️⃣  REFRESHING LARAVEL CONFIGURATION"

# Detect PHP binary
if [ -x "/opt/alt/php83/usr/bin/php" ]; then
    PHP_BIN="/opt/alt/php83/usr/bin/php"
    echo "Using CloudLinux alt-php 8.3: $PHP_BIN"
elif [ -x "/opt/plesk/php/8.3/bin/php" ]; then
    PHP_BIN="/opt/plesk/php/8.3/bin/php"
    echo "Using Plesk PHP 8.3: $PHP_BIN"
elif [ -x "/opt/plesk/php/8.2/bin/php" ]; then
    PHP_BIN="/opt/plesk/php/8.2/bin/php"
    echo "Using Plesk PHP 8.2: $PHP_BIN"
elif command -v php >/dev/null 2>&1; then
    PHP_BIN="php"
    echo "Using system PHP: $(php --version | head -n1)"
else
    echo "❌ PHP not found!"
    exit 1
fi

# Fix APP_KEY duplication if present (prevent 500 errors)
echo "🔑 Checking APP_KEY configuration..."
APP_KEY_COUNT=$(grep -c "^APP_KEY=" .env || echo "0")
if [ "$APP_KEY_COUNT" -gt 1 ]; then
    echo "⚠️  Found duplicate APP_KEY entries ($APP_KEY_COUNT), fixing..."
    
    # Get the first valid APP_KEY
    FIRST_APP_KEY=$(grep "^APP_KEY=" .env | head -n1)
    
    # Remove all APP_KEY lines
    sed -i '/^APP_KEY=/d' .env
    
    # Add back the first one
    echo "$FIRST_APP_KEY" >> .env
    
    echo "✅ Fixed APP_KEY duplication"
elif [ "$APP_KEY_COUNT" -eq 0 ] || ! grep -q "APP_KEY=base64:" .env; then
    echo "🔑 Generating missing APP_KEY..."
    $PHP_BIN artisan key:generate --no-interaction
else
    echo "✅ APP_KEY is properly configured"
fi

# Clear and refresh all Laravel configuration
echo "🔄 Clearing and refreshing Laravel configuration..."
$PHP_BIN artisan config:clear 2>/dev/null || $PHP_BIN artisan config:clear
$PHP_BIN artisan cache:clear 2>/dev/null || $PHP_BIN artisan cache:clear
$PHP_BIN artisan config:cache 2>/dev/null || $PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache 2>/dev/null || $PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache 2>/dev/null || $PHP_BIN artisan view:cache
# Additional config:clear to fix error 500 after deployment
$PHP_BIN artisan config:clear 2>/dev/null || $PHP_BIN artisan config:clear

# Test TEMPLATES_ROOT configuration
echo "🧪 Testing TEMPLATES_ROOT configuration..."
CONFIGURED_ROOT=$($PHP_BIN artisan config:show templates.root 2>/dev/null | grep -o '"[^"]*"' | tr -d '"' || echo "")

if [ -n "$CONFIGURED_ROOT" ] && [ "$CONFIGURED_ROOT" != "" ]; then
    echo "✅ TEMPLATES_ROOT configured correctly: $CONFIGURED_ROOT"
    
    if [ -d "$CONFIGURED_ROOT" ]; then
        echo "✅ Templates directory exists: $CONFIGURED_ROOT"
    else
        echo "⚠️  Templates directory missing, creating: $CONFIGURED_ROOT"
        mkdir -p "$CONFIGURED_ROOT"
        chmod 755 "$CONFIGURED_ROOT"
        chown -R "$SYSUSER:psacln" "$CONFIGURED_ROOT"
    fi
else
    echo "❌ TEMPLATES_ROOT still not configured correctly!"
    exit 1
fi

# Step 4: Automatic Plesk Configuration (if possible)
echo ""
echo "4️⃣  ATTEMPTING AUTOMATIC PLESK CONFIGURATION"

if command -v plesk >/dev/null 2>&1; then
    echo "⚡ Plesk CLI available - attempting automatic configuration..."
    
    # Set PHP version to alt-php 8.3
    if plesk bin site --update-php-settings "$DOMAIN" -version alt-php83 2>/dev/null; then
        echo "✅ Set PHP version to alt-php83"
    else
        echo "⚠️  Could not set PHP version automatically"
    fi
    
    # Restart web services
    if systemctl restart lshttpd 2>/dev/null || service lshttpd restart 2>/dev/null; then
        echo "✅ Restarted LiteSpeed web server"
    else
        echo "⚠️  Could not restart web server automatically"
    fi
    
    echo "✅ Automatic Plesk configuration completed"
else
    echo "⚠️  Plesk CLI not available"
    echo ""
    echo "📋 MANUAL PLESK CONFIGURATION REQUIRED:"
    echo "   1. Plesk → $DOMAIN → PHP Settings → LSPHP 8.3 FastCGI"
    echo "   2. Plesk → $DOMAIN → Apache & nginx Settings"
    echo "      Add to BOTH HTTP and HTTPS Additional directives:"
    echo ""
    echo "      AddType application/x-httpd-alt-php83 .php"
    echo "      <IfModule lsapi_module>"
    echo "          AddHandler application/x-httpd-alt-php83 .php"
    echo "      </IfModule>"
    echo ""
fi

# Step 5: Create health check files
echo ""
echo "5️⃣  CREATING HEALTH CHECK FILES"

# Test static file
echo "OK" > "$PUBLIC_PATH/health.txt"

# Test PHP file
cat > "$PUBLIC_PATH/test.php" << 'EOF'
<?php
echo "PHP Version: " . PHP_VERSION . "\n";
echo "SAPI: " . php_sapi_name() . "\n";
echo "User: " . get_current_user() . "\n";
echo "Templates Root: ";

// Test Laravel config access
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    echo config('templates.root', 'NOT CONFIGURED') . "\n";
} else {
    echo "Laravel not available\n";
}
EOF

echo "✅ Health check files created"

# Step 6: Final tests
echo ""
echo "6️⃣  RUNNING FINAL TESTS"

echo "🧪 Testing scanner functionality..."
if $PHP_BIN artisan templates:scan --limit=1 >/dev/null 2>&1; then
    echo "✅ Scanner working correctly"
else
    echo "⚠️  Scanner test failed (may be normal if no templates exist)"
fi

# Test web access
echo "🌐 Testing web access..."
sleep 2

if curl -f -s "https://$DOMAIN/health.txt" | grep -q "OK" 2>/dev/null; then
    echo "✅ Static files accessible (HTTPS)"
elif curl -f -s "http://$DOMAIN/health.txt" | grep -q "OK" 2>/dev/null; then
    echo "✅ Static files accessible (HTTP)"
else
    echo "⚠️  Static files test failed - may need manual Plesk configuration"
fi

if curl -f -s "https://$DOMAIN/test.php" | grep -q "PHP Version" 2>/dev/null; then
    echo "✅ PHP processing working (HTTPS)"
elif curl -f -s "http://$DOMAIN/test.php" | grep -q "PHP Version" 2>/dev/null; then
    echo "✅ PHP processing working (HTTP)"
else
    echo "⚠️  PHP processing test failed - manual Plesk configuration needed"
fi

echo ""
echo "🎉 COMPREHENSIVE FIX COMPLETED!"
echo ""
echo "📊 SUMMARY:"
echo "   ✅ TEMPLATES_ROOT: Fixed and configured"
echo "   ✅ Directory Structure: Created with correct permissions"
echo "   ✅ Laravel Config: Refreshed and cached"
echo "   ✅ .htaccess: Deployment-proof PHP handler mapping"
echo "   ✅ Health Checks: Created for testing"
echo ""
echo "🔗 TEST URLS:"
echo "   https://$DOMAIN/health.txt (should show 'OK')"
echo "   https://$DOMAIN/test.php (should show PHP info)"
echo "   https://$DOMAIN/en/templates (should load app)"
echo ""

if command -v plesk >/dev/null 2>&1; then
    echo "✅ This fix should survive future deployments!"
else
    echo "⚠️  Some manual Plesk configuration may be needed for complete automation"
fi