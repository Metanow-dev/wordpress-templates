#!/bin/bash
# Fix Plesk 403 error for Laravel application
# This script automates the PHP handler mapping fix

set -e

DOMAIN="wp-templates.metanow.dev"
APP_PATH="/var/www/vhosts/$DOMAIN/httpdocs"
PUBLIC_PATH="$APP_PATH/public"

echo "🚨 Fixing Plesk 403 error for $DOMAIN..."

# Step 1: Ensure public directory exists and has correct structure
echo "📁 Setting up Laravel public directory..."
cd "$APP_PATH"

if [ ! -d "public" ]; then
    echo "❌ Public directory not found! Laravel may not be properly deployed."
    exit 1
fi

# Step 2: Create/update .htaccess with correct PHP handler mapping
echo "⚙️  Configuring PHP handler mapping..."
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

# Step 3: Set correct permissions and ownership
echo "🔒 Setting permissions and ownership..."

# Detect the correct web user
WEB_USER=""
if id -u apache >/dev/null 2>&1; then
    WEB_USER="apache:apache"
elif id -u www-data >/dev/null 2>&1; then
    WEB_USER="www-data:www-data"
elif id -u nginx >/dev/null 2>&1; then
    WEB_USER="nginx:nginx"
else
    # Try to detect from the domain path ownership
    DOMAIN_OWNER=$(stat -c "%U" "/var/www/vhosts/$DOMAIN" 2>/dev/null || echo "")
    if [ -n "$DOMAIN_OWNER" ] && id -u "$DOMAIN_OWNER" >/dev/null 2>&1; then
        WEB_USER="$DOMAIN_OWNER:$DOMAIN_OWNER"
        echo "📍 Detected domain owner: $DOMAIN_OWNER"
    fi
fi

if [ -n "$WEB_USER" ]; then
    echo "👤 Setting ownership to: $WEB_USER"
    chown -R $WEB_USER "$APP_PATH"
else
    echo "⚠️  Could not detect web user, skipping chown"
fi

# Set directory and file permissions
find "$APP_PATH" -type d -exec chmod 755 {} \;
find "$APP_PATH" -type f -exec chmod 644 {} \;
chmod -R 775 "$APP_PATH/storage" "$APP_PATH/bootstrap/cache" 2>/dev/null || true

echo "✅ Permissions set"

# Step 4: Create test files to verify setup
echo "🧪 Creating test files..."

# Test static file
echo "OK" > "$PUBLIC_PATH/health.txt"

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

echo "✅ Test files created"

# Step 5: Instructions for Plesk UI configuration
echo ""
echo "🎯 MANUAL PLESK CONFIGURATION REQUIRED:"
echo ""
echo "1. Go to Plesk → Websites & Domains → $DOMAIN → Hosting Settings"
echo "   Set Document root to: httpdocs/public"
echo ""
echo "2. Go to Plesk → Websites & Domains → $DOMAIN → PHP Settings" 
echo "   Set PHP version to: LSPHP 8.3 (alt-php) FastCGI"
echo ""
echo "3. Go to Plesk → Websites & Domains → $DOMAIN → Apache & nginx Settings"
echo "   Add to Additional Apache directives (both HTTP and HTTPS):"
echo ""
echo '<Directory "/var/www/vhosts/'$DOMAIN'/httpdocs/public">'
echo '    AllowOverride All'
echo '    Options -Indexes +SymLinksIfOwnerMatch'
echo '    Require all granted'
echo '</Directory>'
echo 'DirectoryIndex index.php index.html'
echo ""
echo "4. After making changes, run:"
echo "   plesk repair web -y"
echo "   systemctl restart lshttpd"
echo ""

# Step 6: Test URLs
echo "🔗 TEST THESE URLS AFTER PLESK CONFIGURATION:"
echo "   Static file: https://$DOMAIN/health.txt (should show 'OK')"
echo "   PHP test: https://$DOMAIN/test.php (should show PHP info)"
echo "   Laravel app: https://$DOMAIN/ (should load Laravel)"
echo ""

echo "🎉 Script completed! Please follow the manual Plesk steps above."