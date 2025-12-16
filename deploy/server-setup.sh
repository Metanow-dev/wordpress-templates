#!/bin/bash

# WordPress Templates Catalog - Server Setup Script
# Run this script on your Plesk server to prepare for deployment

set -e

echo "🚀 WordPress Templates Catalog - Server Setup"
echo "=============================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
DOMAIN_NAME=${1:-"your-domain.com"}
APP_PATH="/var/www/vhosts/$DOMAIN_NAME/httpdocs"
TEMPLATES_ROOT="/srv/templates"

echo -e "${YELLOW}Domain: $DOMAIN_NAME${NC}"
echo -e "${YELLOW}App Path: $APP_PATH${NC}"
echo -e "${YELLOW}Templates Root: $TEMPLATES_ROOT${NC}"

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   echo -e "${RED}This script should not be run as root for security reasons${NC}"
   echo "Please run as the web user or with sudo for specific commands"
   exit 1
fi

echo ""
echo "📦 Installing system dependencies..."

# Update package list
sudo apt update

# Install Chrome/Chromium for screenshots
if ! command -v google-chrome &> /dev/null && ! command -v chromium-browser &> /dev/null; then
    echo "Installing Chrome/Chromium..."
    
    # Try to install Google Chrome first
    wget -q -O - https://dl.google.com/linux/linux_signing_key.pub | sudo apt-key add -
    echo "deb [arch=amd64] http://dl.google.com/linux/chrome/deb/ stable main" | sudo tee /etc/apt/sources.list.d/google-chrome.list
    sudo apt update
    
    if sudo apt install -y google-chrome-stable; then
        echo -e "${GREEN}✅ Google Chrome installed${NC}"
    else
        echo "Google Chrome failed, installing Chromium..."
        sudo apt install -y chromium-browser
        echo -e "${GREEN}✅ Chromium installed${NC}"
    fi
else
    echo -e "${GREEN}✅ Chrome/Chromium already installed${NC}"
fi

# Install Node.js if not present or wrong version
NODE_VERSION=$(node --version 2>/dev/null | cut -d'v' -f2 | cut -d'.' -f1)
if [[ -z "$NODE_VERSION" ]] || [[ "$NODE_VERSION" -lt 18 ]]; then
    echo "Installing Node.js 18..."
    curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
    sudo apt install -y nodejs
    echo -e "${GREEN}✅ Node.js $(node --version) installed${NC}"
else
    echo -e "${GREEN}✅ Node.js $(node --version) already installed${NC}"
fi

# Install additional packages
sudo apt install -y \
    zip unzip \
    git \
    curl \
    supervisor \
    imagemagick \
    jpegoptim optipng pngquant gifsicle \
    libpng-dev libjpeg-dev libfreetype6-dev

echo -e "${GREEN}✅ System dependencies installed${NC}"

echo ""
echo "📁 Creating directories..."

# Create templates directory
sudo mkdir -p "$TEMPLATES_ROOT"
sudo chown -R www-data:www-data "$TEMPLATES_ROOT"
sudo chmod -R 755 "$TEMPLATES_ROOT"
echo -e "${GREEN}✅ Templates directory created: $TEMPLATES_ROOT${NC}"

# Create application directories
sudo mkdir -p "$APP_PATH"
sudo mkdir -p "/var/www/vhosts/$DOMAIN_NAME/backups"
sudo mkdir -p "/var/www/vhosts/$DOMAIN_NAME/logs"

# Set ownership
sudo chown -R www-data:www-data "/var/www/vhosts/$DOMAIN_NAME"

echo -e "${GREEN}✅ Application directories created${NC}"

echo ""
echo "🔧 Configuring PHP..."

# Find PHP configuration file
PHP_INI=$(php --ini | grep "Loaded Configuration File" | cut -d: -f2 | xargs)

if [[ -n "$PHP_INI" ]]; then
    echo "PHP configuration file: $PHP_INI"
    
    # Backup original PHP configuration
    sudo cp "$PHP_INI" "$PHP_INI.backup.$(date +%Y%m%d_%H%M%S)"
    
    # Update PHP settings for screenshot generation
    sudo sed -i 's/memory_limit = .*/memory_limit = 512M/' "$PHP_INI"
    sudo sed -i 's/max_execution_time = .*/max_execution_time = 300/' "$PHP_INI"
    sudo sed -i 's/upload_max_filesize = .*/upload_max_filesize = 32M/' "$PHP_INI"
    sudo sed -i 's/post_max_size = .*/post_max_size = 32M/' "$PHP_INI"
    
    echo -e "${GREEN}✅ PHP configuration updated${NC}"
else
    echo -e "${YELLOW}⚠️  Could not find PHP configuration file. Please update manually:${NC}"
    echo "  - memory_limit = 512M"
    echo "  - max_execution_time = 300"
    echo "  - upload_max_filesize = 32M"
    echo "  - post_max_size = 32M"
fi

echo ""
echo "⏰ Setting up log rotation..."

# Create log rotation configuration
sudo tee /etc/logrotate.d/wordpress-templates > /dev/null <<EOF
/var/www/vhosts/$DOMAIN_NAME/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
    postrotate
        /usr/bin/supervisorctl restart all > /dev/null 2>&1 || true
    endscript
}
EOF

echo -e "${GREEN}✅ Log rotation configured${NC}"

echo ""
echo "🧪 Creating test WordPress site..."

# Create a test WordPress site for screenshot testing
TEST_WP_PATH="$TEMPLATES_ROOT/test-wp/httpdocs"
sudo mkdir -p "$TEST_WP_PATH"

sudo tee "$TEST_WP_PATH/index.php" > /dev/null <<'EOF'
<!DOCTYPE html>
<html>
<head>
    <title>Test WordPress Site</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 50px; }
        .header { background: #0073aa; color: white; padding: 20px; }
        .content { padding: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Test WordPress Site</h1>
        <p>Screenshot testing for WordPress Templates Catalog</p>
    </div>
    <div class="content">
        <h2>This is a test page</h2>
        <p>This page is used to test the screenshot generation system.</p>
        <p>Generated at: <?php echo date('Y-m-d H:i:s'); ?></p>
    </div>
</body>
</html>
EOF

sudo chown -R www-data:www-data "$TEST_WP_PATH"
sudo chmod -R 755 "$TEST_WP_PATH"

echo -e "${GREEN}✅ Test WordPress site created at: $TEST_WP_PATH${NC}"

echo ""
echo "🔒 Setting up security..."

# Create a secure directory for sensitive files
sudo mkdir -p "/var/www/vhosts/$DOMAIN_NAME/secure"
sudo chown www-data:www-data "/var/www/vhosts/$DOMAIN_NAME/secure"
sudo chmod 700 "/var/www/vhosts/$DOMAIN_NAME/secure"

echo -e "${GREEN}✅ Security configuration completed${NC}"

echo ""
echo "📋 Creating deployment checklist..."

cat > "/tmp/deployment-checklist.txt" <<EOF
WordPress Templates Catalog - Deployment Checklist
==================================================

✅ Server Setup Completed

Next Steps:
-----------

1. DATABASE SETUP:
   - Create MySQL database: wordpress_templates
   - Create database user with full privileges
   - Note credentials for .env file

2. PLESK CONFIGURATION:
   - Domain: $DOMAIN_NAME
   - Document root: $APP_PATH
   - PHP version: 8.2+
   - Add Nginx additional directives (see README.md)

3. GITHUB SECRETS:
   Add these secrets to your GitHub repository:
   - PLESK_HOST: Your server IP address
   - PLESK_USERNAME: SSH username
   - PLESK_SSH_KEY: Your private SSH key
   - PLESK_SSH_PORT: SSH port (default: 22)
   - DOMAIN_NAME: $DOMAIN_NAME

4. ENVIRONMENT FILE:
   Create .env file in $APP_PATH with production settings
   (see README.md for complete example)

5. SCHEDULED TASKS:
   Add these cron jobs in Plesk:
   - */15 * * * * php artisan templates:scan
   - 0 2 * * * php artisan templates:screenshot
   - 0 3 * * 0 php artisan storage:link

6. TEST DEPLOYMENT:
   - Push to main branch to trigger deployment
   - Visit: https://$DOMAIN_NAME/en/templates
   - Test: https://$DOMAIN_NAME/test-wp/

Paths Created:
--------------
- Templates: $TEMPLATES_ROOT
- Application: $APP_PATH
- Backups: /var/www/vhosts/$DOMAIN_NAME/backups
- Logs: /var/www/vhosts/$DOMAIN_NAME/logs
- Test site: $TEMPLATES_ROOT/test-wp/httpdocs

Browser for Screenshots:
------------------------
$(which google-chrome 2>/dev/null || which chromium-browser 2>/dev/null || echo "Not found - please install manually")

Node.js Version:
----------------
$(node --version 2>/dev/null || echo "Not found - please install manually")

PHP Version:
------------
$(php --version | head -n1)
EOF

echo -e "${GREEN}✅ Deployment checklist created: /tmp/deployment-checklist.txt${NC}"

echo ""
echo -e "${GREEN}🎉 Server setup completed successfully!${NC}"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo "1. Review the deployment checklist: cat /tmp/deployment-checklist.txt"
echo "2. Set up your database in Plesk"
echo "3. Configure GitHub secrets for automatic deployment"
echo "4. Create your .env file"
echo "5. Push to main branch to trigger first deployment"
echo ""
echo -e "${GREEN}Test your setup:${NC}"
echo "- Test site: https://$DOMAIN_NAME/test-wp/"
echo "- Application: https://$DOMAIN_NAME/en/templates (after deployment)"

# Display the checklist
echo ""
echo "📋 DEPLOYMENT CHECKLIST:"
echo "========================"
cat /tmp/deployment-checklist.txt