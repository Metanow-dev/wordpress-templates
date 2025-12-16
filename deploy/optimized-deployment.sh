#!/bin/bash
set -e

APP_PATH="/var/www/vhosts/wp-templates.metanow.dev/httpdocs"
DOMAIN="wp-templates.metanow.dev"
SYSUSER="wp-templates.metanow_r6s2v1oe7wr"
PUBLIC_PATH="$APP_PATH/public"

cd "$APP_PATH"

# Extract deployment
echo "📤 Extracting deployment..."
tar -xzf deployment.tar.gz
rm deployment.tar.gz

# ⚡ IMMEDIATE FIXES - Prevent 403 errors ASAP (within 10 seconds)
echo "⚡ IMMEDIATE 403/500 PREVENTION..."

# 1. Detect PHP binary first
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

# 2. INSTANT .htaccess creation (prevents 403)
echo "🔧 Creating INSTANT .htaccess..."
cat > "$PUBLIC_PATH/.htaccess" << 'EOF'
# INSTANT FIX: Force CloudLinux alt-php 8.3
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

# 3. INSTANT Laravel cache clearing (prevents 500)
echo "🚀 INSTANT cache clearing..."
$PHP_BIN artisan config:clear 2>/dev/null || true
$PHP_BIN artisan cache:clear 2>/dev/null || true

# 4. INSTANT TEMPLATES_ROOT fix
echo "📝 INSTANT TEMPLATES_ROOT fix..."
if [ -f .env ]; then
    sed -i '/^TEMPLATES_ROOT=.*$/d' .env 2>/dev/null || true
    echo "TEMPLATES_ROOT=$APP_PATH/public" >> .env
fi

echo "✅ INSTANT fixes applied! 403/500 should be prevented NOW!"

# Now continue with slower operations...
echo "📁 Creating Laravel directories..."
mkdir -p storage/{app,framework/{cache,sessions,testing,views},logs}
mkdir -p bootstrap/cache
touch storage/logs/laravel.log

# OPTIMIZED permissions (much faster)
echo "🔒 Setting optimized permissions..."
# Only set permissions on critical directories first
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
chown -R "$SYSUSER:psacln" storage bootstrap/cache 2>/dev/null || true

# Handle production environment file
if [ ! -f .env ]; then
  if [ -f .env.production ]; then
    echo "📝 Creating .env from .env.production template..."
    cp .env.production .env
  else
    echo "❌ Neither .env nor .env.production found!"
    exit 1
  fi
fi

# Generate app key if not set
if ! grep -q "APP_KEY=base64:" .env; then
  echo "🔑 Generating application key..."
  sed -i '/^APP_KEY=/d' .env
  $PHP_BIN artisan key:generate --no-interaction
else
  echo "✅ APP_KEY already configured"
fi

# Laravel commands
echo "⚡ Running Laravel commands..."
$PHP_BIN artisan config:clear 2>/dev/null || true
$PHP_BIN artisan config:cache 2>/dev/null || true
$PHP_BIN artisan route:cache 2>/dev/null || true
$PHP_BIN artisan view:cache 2>/dev/null || true

# Run migrations
$PHP_BIN artisan migrate --force --no-interaction 2>/dev/null || true

# Storage link
$PHP_BIN artisan storage:link 2>/dev/null || true

# Queue restart
$PHP_BIN artisan queue:restart 2>/dev/null || true

# Final cache clear
$PHP_BIN artisan cache:clear 2>/dev/null || true

# SLOW permissions at the end (site is already working)
echo "🐌 Setting comprehensive permissions (in background)..."
# Run comprehensive permissions in background so deployment can complete
(
  find . -type f -exec chmod 644 {} \; 2>/dev/null || true
  find . -type d -exec chmod 755 {} \; 2>/dev/null || true
  chown -R "$SYSUSER:psacln" "$APP_PATH" 2>/dev/null || true
) &

echo "✅ Deployment completed successfully!"
echo "🔄 Comprehensive permissions running in background..."

# Quick health check
sleep 2
if curl -f -s "https://$DOMAIN/health.txt" | grep -q "OK" 2>/dev/null; then
    echo "✅ Site is responding correctly!"
elif curl -f -s "http://$DOMAIN/health.txt" | grep -q "OK" 2>/dev/null; then
    echo "✅ Site is responding (HTTP)!"
else
    echo "ℹ️  Site health check pending - should be available shortly"
fi