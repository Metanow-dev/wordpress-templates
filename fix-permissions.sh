#!/bin/bash
#
# Fix Laravel File Permissions After Git Pull
# Run this after every git pull to fix 403 errors
#
# Usage: ./fix-permissions.sh
#

echo "🔧 Fixing Laravel file permissions..."

# Set the correct user and group for your Plesk setup
USER="megasandboxs.com_c9h1nddlyw"
GROUP="psacln"

# Fix ownership of Laravel core directories
echo "   → Fixing Laravel core directories..."
chown -R $USER:$GROUP app/ bootstrap/ config/ database/ routes/ resources/ tests/ 2>/dev/null

# Fix ownership of public, storage, and vendor
echo "   → Fixing public, storage, vendor..."
chown -R $USER:$GROUP public/ storage/ vendor/ 2>/dev/null

# Fix ownership of deploy scripts
echo "   → Fixing deploy scripts..."
chown -R $USER:$GROUP deploy/ 2>/dev/null

# Fix ownership of root Laravel files AND root .htaccess/index.php (critical!)
echo "   → Fixing root files..."
chown $USER:$GROUP index.php .htaccess 2>/dev/null  # Root Laravel bootstrap files
chown $USER:$GROUP artisan .env composer.* package*.json phpunit.xml vite.config.js 2>/dev/null
chown $USER:$GROUP *.md *.sh .gitignore .gitattributes .editorconfig 2>/dev/null

# Fix ownership of Git directory and GitHub workflows
echo "   → Fixing .git and .github directories..."
chown -R $USER:$GROUP .git/ .github/ 2>/dev/null

# Fix ownership of httpdocs directory itself (critical for web server access)
echo "   → Fixing httpdocs directory ownership..."
chown $USER:$GROUP /var/www/vhosts/megasandboxs.com/httpdocs 2>/dev/null

# Set correct permissions for storage and cache
echo "   → Setting storage/cache permissions..."
chmod -R 775 storage/ bootstrap/cache/ 2>/dev/null

# Set correct permissions for public files
echo "   → Setting public directory permissions..."
chmod 755 public/
chmod 644 public/index.php public/.htaccess 2>/dev/null

# Make artisan and shell scripts executable
echo "   → Making scripts executable..."
chmod +x artisan 2>/dev/null
chmod +x fix-permissions.sh 2>/dev/null
chmod +x deploy/*.sh 2>/dev/null

echo "✅ Permissions fixed!"
echo ""
echo "📝 Note: WordPress sites are NOT touched - only Laravel app files"
