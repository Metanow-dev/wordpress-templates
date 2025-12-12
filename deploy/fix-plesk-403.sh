#!/bin/bash
# DEPLOYMENT-PROOF Fix for Plesk 403 error
# This script uses the correct Plesk domain user and survives deployments

set -e

DOMAIN="wp-templates.metanow.dev"
SYSUSER="wp-templates.metanow_r6s2v1oe7wr"  # Plesk domain system user
APP_PATH="/var/www/vhosts/$DOMAIN/httpdocs"
PUBLIC_PATH="$APP_PATH/public"

echo "🚨 Applying deployment-proof Plesk 403 fix for $DOMAIN..."

# Step 1: Ensure public directory exists and has correct structure
echo "📁 Setting up Laravel public directory..."
cd "$APP_PATH"

if [ ! -d "public" ]; then
    echo "❌ Public directory not found! Laravel may not be properly deployed."
    exit 1
fi

# Step 2: Create/update .htaccess with correct PHP handler mapping (fallback)
# Note: Primary fix should be in Apache vhost directives, this is backup
echo "⚙️  Configuring .htaccess as fallback..."
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

# Step 3: Set CORRECT Plesk ownership (deployment-proof)
echo "🔒 Setting Plesk-correct permissions and ownership..."

# FORCE correct Plesk ownership (domain system user : psacln)
echo "👤 Setting ownership to: $SYSUSER:psacln"
chown -R "$SYSUSER:psacln" "$APP_PATH"

# Set proper permissions
echo "📁 Setting directory permissions..."
find "$APP_PATH" -type d -exec chmod 755 {} \;
find "$APP_PATH" -type f -exec chmod 644 {} \;

# Laravel writable directories  
echo "✍️  Setting Laravel writable permissions..."
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

# Step 5: DEPLOYMENT-PROOF Plesk configuration instructions  
echo ""
echo "🎯 CRITICAL: DEPLOYMENT-PROOF PLESK CONFIGURATION REQUIRED:"
echo ""
echo "⚡ MAKE THIS SURVIVE DEPLOYMENTS by adding to Apache vhost:"
echo ""
echo "1. Plesk → Websites & Domains → $DOMAIN → Hosting Settings"
echo "   Document root: httpdocs/public"
echo ""
echo "2. Plesk → Websites & Domains → $DOMAIN → PHP Settings"
echo "   PHP version: LSPHP 8.3 (alt-php) FastCGI"
echo ""
echo "3. 🚨 MOST IMPORTANT - Plesk → Apache & nginx Settings"
echo "   Add to BOTH HTTP and HTTPS Additional Apache directives:"
echo ""
echo "# Force CloudLinux alt-php 8.3 for this vhost (deployment-proof)"
echo "AddType application/x-httpd-alt-php83 .php"
echo "<IfModule lsapi_module>"
echo "    AddHandler application/x-httpd-alt-php83 .php"
echo "</IfModule>"
echo ""
echo "# Laravel public/ access"
echo '<Directory "/var/www/vhosts/'$DOMAIN'/httpdocs/public">'
echo "    AllowOverride All"
echo "    Options -Indexes +SymLinksIfOwnerMatch"
echo "    Require all granted"
echo "</Directory>"
echo ""
echo "DirectoryIndex index.php index.html"
echo ""
echo "4. Apply changes:"
echo "   plesk repair web -y && systemctl restart lshttpd"
echo ""

# Step 6: Test URLs
echo "🔗 TEST THESE URLS AFTER PLESK CONFIGURATION:"
echo "   Static file: https://$DOMAIN/health.txt (should show 'OK')"
echo "   PHP test: https://$DOMAIN/test.php (should show PHP info)"
echo "   Laravel app: https://$DOMAIN/ (should load Laravel)"
echo ""

echo "🎉 Script completed! Please follow the manual Plesk steps above."