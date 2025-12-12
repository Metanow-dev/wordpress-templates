#!/bin/bash
# Quick fix for Plesk 403 error - only fixes core Laravel files
# This is faster than fix-plesk-403.sh as it doesn't traverse all subdirectories

set -e

DOMAIN="megasandboxs.com"
SYSUSER="megasandboxs.com_c9h1nddlyw"  # Plesk domain system user
APP_PATH="/var/www/vhosts/$DOMAIN/httpdocs"
PUBLIC_PATH="$APP_PATH/public"

echo "🚀 Quick fix for Plesk 403 error on $DOMAIN..."

# Step 1: Ensure public directory exists
echo "📁 Checking Laravel public directory..."
cd "$APP_PATH"

if [ ! -d "public" ]; then
    echo "❌ Public directory not found!"
    exit 1
fi

# Step 2: Create/update .htaccess with correct PHP handler mapping
echo "⚙️  Configuring .htaccess..."
cat > "$PUBLIC_PATH/.htaccess" << 'EOF'
# Force CloudLinux alt-php 8.3 for this vhost
<IfModule mime_module>
    AddType application/x-httpd-alt-php83 .php
</IfModule>
<IfModule lsapi_module>
    AddHandler application/x-httpd-alt-php83 .php
</IfModule>

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

echo "✅ Created .htaccess with PHP handler mapping"

# Step 3: Set CORRECT Plesk ownership on core directories only
echo "🔒 Setting Plesk-correct ownership on core directories..."

# Set ownership on critical directories only (not recursing into subdirs)
chown "$SYSUSER:psacln" "$APP_PATH"
chown "$SYSUSER:psacln" "$PUBLIC_PATH"
chown "$SYSUSER:psacln" "$PUBLIC_PATH/index.php"
chown "$SYSUSER:psacln" "$PUBLIC_PATH/.htaccess"

# Fix Laravel core directories
echo "📁 Fixing Laravel core directories..."
for dir in app bootstrap config database public resources routes storage vendor artisan .env; do
    if [ -e "$APP_PATH/$dir" ]; then
        chown -R "$SYSUSER:psacln" "$APP_PATH/$dir"
    fi
done

# Set proper permissions on critical files
echo "📄 Setting permissions on critical files..."
chmod 755 "$APP_PATH"
chmod 755 "$PUBLIC_PATH"
chmod 644 "$PUBLIC_PATH/index.php"
chmod 644 "$PUBLIC_PATH/.htaccess"
[ -f "$APP_PATH/.env" ] && chmod 644 "$APP_PATH/.env"
[ -f "$APP_PATH/artisan" ] && chmod 755 "$APP_PATH/artisan"

# Laravel writable directories
echo "✍️  Setting Laravel writable permissions..."
if [ -d "$APP_PATH/storage" ]; then
    chmod -R 775 "$APP_PATH/storage"
fi
if [ -d "$APP_PATH/bootstrap/cache" ]; then
    chmod -R 775 "$APP_PATH/bootstrap/cache"
fi

echo "✅ Permissions set"

# Step 4: Create test files
echo "🧪 Creating test files..."

# Test static file
echo "OK" > "$PUBLIC_PATH/health.txt"
chown "$SYSUSER:psacln" "$PUBLIC_PATH/health.txt"
chmod 644 "$PUBLIC_PATH/health.txt"

# Test PHP file
cat > "$PUBLIC_PATH/test.php" << 'EOF'
<?php
echo "PHP Version: " . PHP_VERSION . "\n";
echo "SAPI: " . php_sapi_name() . "\n";
echo "User: " . get_current_user() . "\n";
echo "Working Directory: " . getcwd() . "\n";
echo "Laravel Public Path: " . __DIR__ . "\n";

if (file_exists(__DIR__ . '/../.env')) {
    echo ".env file: EXISTS\n";
} else {
    echo ".env file: MISSING\n";
}

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    echo "Composer autoload: EXISTS\n";
} else {
    echo "Composer autoload: MISSING\n";
}
EOF

chown "$SYSUSER:psacln" "$PUBLIC_PATH/test.php"
chmod 644 "$PUBLIC_PATH/test.php"

echo "✅ Test files created"

echo ""
echo "✅ Quick fix completed!"
echo ""
echo "🔗 TEST THESE URLS:"
echo "   Static file: https://$DOMAIN/health.txt"
echo "   PHP test: https://$DOMAIN/test.php"
echo "   Laravel app: https://$DOMAIN/"
echo ""
echo "⚠️  If still getting 403, you may need to configure Apache directives in Plesk:"
echo "   Plesk → Websites & Domains → $DOMAIN → Apache & nginx Settings"
echo "   Add to Additional Apache directives section (see fix-plesk-403.sh for details)"
