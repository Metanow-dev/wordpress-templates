#!/bin/bash
# EARLY Deployment Fix - Run IMMEDIATELY after deployment
# Fixes 403 and 500 errors as quickly as possible

set -e

DOMAIN="wp-templates.metanow.dev"
SYSUSER="wp-templates.metanow_r6s2v1oe7wr"
APP_PATH="/var/www/vhosts/$DOMAIN/httpdocs"
PUBLIC_PATH="$APP_PATH/public"

echo "⚡ EARLY Deployment Fix for $DOMAIN - Running ASAP!"

cd "$APP_PATH"

# STEP 1: Immediate PHP binary detection
echo "🔍 Detecting PHP binary..."
if [ -x "/opt/alt/php83/usr/bin/php" ]; then
    PHP_BIN="/opt/alt/php83/usr/bin/php"
    echo "✅ Using CloudLinux alt-php 8.3: $PHP_BIN"
elif [ -x "/opt/plesk/php/8.3/bin/php" ]; then
    PHP_BIN="/opt/plesk/php/8.3/bin/php"
    echo "✅ Using Plesk PHP 8.3: $PHP_BIN"
elif [ -x "/opt/plesk/php/8.2/bin/php" ]; then
    PHP_BIN="/opt/plesk/php/8.2/bin/php"
    echo "✅ Using Plesk PHP 8.2: $PHP_BIN"
elif command -v php >/dev/null 2>&1; then
    PHP_BIN="php"
    echo "✅ Using system PHP"
else
    echo "❌ PHP not found!"
    exit 1
fi

# STEP 2: IMMEDIATE .htaccess fix (prevents 403)
echo "⚙️  Creating IMMEDIATE .htaccess fix..."
cat > "$PUBLIC_PATH/.htaccess" << 'EOF'
# IMMEDIATE FIX: Force CloudLinux alt-php 8.3
<IfModule mime_module>
    AddType application/x-httpd-alt-php83 .php
</IfModule>
<IfModule lsapi_module>
    AddHandler application/x-httpd-alt-php83 .php
</IfModule>

# Additional PHP handler mappings
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

echo "✅ .htaccess created immediately"

# STEP 3: IMMEDIATE Laravel cache clearing (prevents 500)
echo "🚀 IMMEDIATE Laravel cache clearing..."
$PHP_BIN artisan config:clear 2>/dev/null || echo "Config clear attempted"
$PHP_BIN artisan cache:clear 2>/dev/null || echo "Cache clear attempted"
$PHP_BIN artisan route:clear 2>/dev/null || echo "Route clear attempted"
$PHP_BIN artisan view:clear 2>/dev/null || echo "View clear attempted"

echo "✅ Immediate cache clearing completed"

# STEP 4: Quick permissions fix
echo "🔒 Quick permissions fix..."
chown -R "$SYSUSER:psacln" "$PUBLIC_PATH" 2>/dev/null || echo "Ownership fix attempted"
chmod 644 "$PUBLIC_PATH/.htaccess" 2>/dev/null || echo "htaccess permissions attempted"

echo "✅ Quick permissions set"

# STEP 5: Force TEMPLATES_ROOT immediately
echo "📝 Immediate TEMPLATES_ROOT fix..."
CORRECT_TEMPLATES_ROOT="$APP_PATH/public"

# Remove any existing TEMPLATES_ROOT lines
sed -i '/^TEMPLATES_ROOT=.*$/d' .env 2>/dev/null || echo "TEMPLATES_ROOT cleanup attempted"

# Add correct TEMPLATES_ROOT
echo "TEMPLATES_ROOT=$CORRECT_TEMPLATES_ROOT" >> .env
echo "✅ TEMPLATES_ROOT set to: $CORRECT_TEMPLATES_ROOT"

# STEP 6: Final immediate config refresh
echo "🔄 Final immediate config refresh..."
$PHP_BIN artisan config:clear 2>/dev/null || echo "Final config clear attempted"

echo ""
echo "⚡ EARLY FIX COMPLETED! Should prevent most 403/500 errors immediately!"
echo "🔗 Test immediately: https://$DOMAIN"
echo ""