#!/bin/bash
# COMPREHENSIVE Deployment Fix for megasandboxs.com
# This script fixes BOTH Laravel app and WordPress sites routing
# Run this automatically after deployment or manually when issues occur

set -e

DOMAIN="megasandboxs.com"
SYSUSER="megasandboxs.com_c9h1nddlyw"
APP_PATH="/var/www/vhosts/$DOMAIN/httpdocs"
PUBLIC_PATH="$APP_PATH/public"

echo "🚨 COMPREHENSIVE Deployment Fix for $DOMAIN"
echo "   Fixes Laravel app, permissions, and WordPress routing"

# EARLY STAGE: Immediate fixes to minimize downtime
echo ""
echo "🚀 EARLY STAGE: Immediate fixes to prevent 403/500 errors..."

# Detect PHP binary first
if [ -x "/opt/alt/php83/usr/bin/php" ]; then
    PHP_BIN="/opt/alt/php83/usr/bin/php"
elif [ -x "/opt/alt/php84/usr/bin/php" ]; then
    PHP_BIN="/opt/alt/php84/usr/bin/php"
elif [ -x "/opt/plesk/php/8.4/bin/php" ]; then
    PHP_BIN="/opt/plesk/php/8.4/bin/php"
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

echo "Using PHP: $PHP_BIN"

# Step 1: Fix ROOT .htaccess (critical for hybrid WordPress + Laravel setup)
echo ""
echo "1️⃣  FIXING ROOT .HTACCESS (WordPress + Laravel routing)"

cat > "$APP_PATH/.htaccess" << 'EOF'
<IfModule mod_rewrite.c>
    RewriteEngine On

    # Rewrite /build/* to /public/build/* for Laravel assets
    RewriteCond %{REQUEST_URI} ^/build/
    RewriteRule ^build/(.*)$ public/build/$1 [L]

    # Rewrite /img/* to /public/img/* for images
    RewriteCond %{REQUEST_URI} ^/img/
    RewriteRule ^img/(.*)$ public/img/$1 [L]

    # Rewrite /vendor/* to /public/vendor/* for vendor assets (Livewire, etc)
    RewriteCond %{REQUEST_URI} ^/vendor/
    RewriteRule ^vendor/(.*)$ public/vendor/$1 [L]

    # Rewrite /storage/* to /public/storage/* for storage symlink
    RewriteCond %{REQUEST_URI} ^/storage/
    RewriteRule ^storage/(.*)$ public/storage/$1 [L]

    # If the request is for an existing file or directory (e.g. /slug WordPress, assets)
    RewriteCond %{REQUEST_FILENAME} -f [OR]
    RewriteCond %{REQUEST_FILENAME} -d
    RewriteRule ^ - [L]

    # Otherwise, route everything through Laravel
    RewriteRule ^ index.php [L]
</IfModule>
EOF

chown $SYSUSER:psacln "$APP_PATH/.htaccess"
chmod 644 "$APP_PATH/.htaccess"
touch "$APP_PATH/.htaccess"  # Force LiteSpeed to reload the file
echo "✅ Root .htaccess created (WordPress sites + Laravel routing)"

# Step 2: Fix PUBLIC .htaccess (Laravel app)
echo ""
echo "2️⃣  FIXING PUBLIC .HTACCESS (Laravel app)"

cat > "$PUBLIC_PATH/.htaccess" << 'EOF'
# Laravel public directory .htaccess
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

chown $SYSUSER:psacln "$PUBLIC_PATH/.htaccess"
chmod 644 "$PUBLIC_PATH/.htaccess"
touch "$PUBLIC_PATH/.htaccess"  # Force LiteSpeed to reload the file
echo "✅ Public .htaccess created (Laravel framework routing)"

# Step 3: Fix file ownership and permissions
echo ""
echo "3️⃣  FIXING OWNERSHIP AND PERMISSIONS"

cd "$APP_PATH"

# Fix ownership of Laravel core directories
echo "   → Fixing Laravel core directories..."
chown -R $SYSUSER:psacln app/ bootstrap/ config/ database/ routes/ resources/ tests/ 2>/dev/null || true

# Fix ownership of public, storage, and vendor
echo "   → Fixing public, storage, vendor..."
chown -R $SYSUSER:psacln public/ storage/ vendor/ 2>/dev/null || true

# Fix ownership of deploy scripts
echo "   → Fixing deploy scripts..."
chown -R $SYSUSER:psacln deploy/ 2>/dev/null || true

# Fix ownership of root Laravel files AND root .htaccess/index.php (critical!)
echo "   → Fixing root files..."
chown $SYSUSER:psacln index.php .htaccess 2>/dev/null || true
chown $SYSUSER:psacln artisan .env composer.* package*.json phpunit.xml vite.config.js 2>/dev/null || true
chown $SYSUSER:psacln *.md *.sh .gitignore .gitattributes .editorconfig 2>/dev/null || true

# Fix ownership of .git directory
echo "   → Fixing .git directory..."
chown -R $SYSUSER:psacln .git/ 2>/dev/null || true

# Fix ownership of httpdocs directory itself (critical for web server access)
echo "   → Fixing httpdocs directory ownership..."
chown $SYSUSER:psacln /var/www/vhosts/$DOMAIN/httpdocs 2>/dev/null || true

# Set correct permissions for storage and cache
echo "   → Setting storage/cache permissions..."
chmod -R 775 storage/ bootstrap/cache/ 2>/dev/null || true

# Set correct permissions for public files
echo "   → Setting public directory permissions..."
chmod 755 public/
chmod 644 public/index.php public/.htaccess 2>/dev/null || true

# Make artisan and scripts executable
chmod +x artisan fix-permissions.sh 2>/dev/null || true

echo "✅ Ownership and permissions fixed"

# Step 4: Laravel configuration refresh
echo ""
echo "4️⃣  REFRESHING LARAVEL CONFIGURATION"

# Immediate cache clearing
$PHP_BIN artisan config:clear 2>/dev/null || true
$PHP_BIN artisan cache:clear 2>/dev/null || true

# Check for .env file
if [ ! -f .env ]; then
    if [ -f .env.production ]; then
        echo "📝 Creating .env from .env.production template..."
        cp .env.production .env
        chown $SYSUSER:psacln .env
    else
        echo "⚠️  No .env file found!"
    fi
fi

# Generate app key if not set (prevent duplicates)
if [ -f .env ]; then
    if ! grep -q "APP_KEY=base64:" .env; then
        echo "🔑 Generating application key..."
        sed -i '/^APP_KEY=/d' .env
        $PHP_BIN artisan key:generate --no-interaction
    else
        echo "✅ APP_KEY already configured"
    fi
fi

# Clear and refresh all Laravel configuration
echo "🔄 Clearing and refreshing Laravel configuration..."
$PHP_BIN artisan config:clear 2>/dev/null || true
$PHP_BIN artisan cache:clear 2>/dev/null || true
$PHP_BIN artisan config:cache 2>/dev/null || true
$PHP_BIN artisan route:cache 2>/dev/null || true
$PHP_BIN artisan view:cache 2>/dev/null || true

echo "✅ Laravel configuration refreshed"

# Step 5: Create Laravel directories
echo ""
echo "5️⃣  ENSURING LARAVEL DIRECTORY STRUCTURE"

mkdir -p storage/{app,framework/{cache,sessions,testing,views},logs} 2>/dev/null || true
mkdir -p bootstrap/cache 2>/dev/null || true
mkdir -p public/img/logo 2>/dev/null || true
touch storage/logs/laravel.log 2>/dev/null || true

chown -R $SYSUSER:psacln storage/ bootstrap/cache/ 2>/dev/null || true
chmod -R 775 storage/ bootstrap/cache/ 2>/dev/null || true

echo "✅ Directory structure verified"

# Step 6: Run migrations and storage link
echo ""
echo "6️⃣  RUNNING LARAVEL SETUP COMMANDS"

$PHP_BIN artisan migrate --force --no-interaction 2>/dev/null || echo "⚠️  Migrations skipped"
$PHP_BIN artisan storage:link 2>/dev/null || echo "⚠️  Storage link skipped"
$PHP_BIN artisan queue:restart 2>/dev/null || echo "⚠️  Queue restart skipped"

echo "✅ Laravel setup commands completed"

# Step 7: Health check files
echo ""
echo "7️⃣  CREATING HEALTH CHECK FILES"

echo "OK" > "$PUBLIC_PATH/health.txt"
chown $SYSUSER:psacln "$PUBLIC_PATH/health.txt"
chmod 644 "$PUBLIC_PATH/health.txt"

cat > "$PUBLIC_PATH/test.php" << 'PHPEOF'
<?php
echo "PHP Version: " . PHP_VERSION . "\n";
echo "SAPI: " . php_sapi_name() . "\n";
echo "User: " . get_current_user() . "\n";
PHPEOF

chown $SYSUSER:psacln "$PUBLIC_PATH/test.php"
chmod 644 "$PUBLIC_PATH/test.php"

echo "✅ Health check files created"

# Step 8: Final tests
echo ""
echo "8️⃣  RUNNING FINAL TESTS"

echo "🧪 Testing configuration..."
if $PHP_BIN artisan about >/dev/null 2>&1; then
    echo "✅ Laravel application working"
else
    echo "⚠️  Laravel test failed - may need manual check"
fi

echo ""
echo "🎉 COMPREHENSIVE FIX COMPLETED!"
echo ""
echo "📊 SUMMARY:"
echo "   ✅ Root .htaccess: WordPress + Laravel routing configured"
echo "   ✅ Public .htaccess: Laravel framework routing configured"
echo "   ✅ Ownership: All files set to $SYSUSER:psacln"
echo "   ✅ Permissions: Storage and cache writable, files secure"
echo "   ✅ Laravel Config: Refreshed and cached"
echo "   ✅ Health Checks: Created for testing"
echo ""
echo "🔗 TEST URLS:"
echo "   https://$DOMAIN/en/templates (Laravel app)"
echo "   https://$DOMAIN/health.txt (should show 'OK')"
echo "   https://$DOMAIN/test.php (should show PHP info)"
echo ""
echo "📝 Note: WordPress sites in httpdocs/ remain untouched and protected by .gitignore"
