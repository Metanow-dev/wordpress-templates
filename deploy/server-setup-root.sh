#!/bin/bash

# WordPress Templates Catalog - Server Setup Script (Root Version)
# Run this script as root on your Plesk server to prepare for deployment

set -e

echo "🚀 WordPress Templates Catalog - Server Setup (Root)"
echo "===================================================="

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

# Check if running as root (required for system-wide installations)
if [[ $EUID -ne 0 ]]; then
   echo -e "${RED}This script must be run as root${NC}"
   echo "Usage: sudo $0 your-domain.com"
   exit 1
fi

echo ""
echo "📦 Installing system dependencies..."

# Update package list
apt update

# Install Chrome/Chromium for screenshots
if ! command -v google-chrome &> /dev/null && ! command -v chromium-browser &> /dev/null; then
    echo "Installing Google Chrome..."
    
    # Install dependencies
    apt install -y wget gnupg software-properties-common
    
    # Add Google Chrome repository
    wget -q -O - https://dl.google.com/linux/linux_signing_key.pub | apt-key add -
    echo "deb [arch=amd64] http://dl.google.com/linux/chrome/deb/ stable main" > /etc/apt/sources.list.d/google-chrome.list
    apt update
    
    if apt install -y google-chrome-stable; then
        echo -e "${GREEN}✅ Google Chrome installed${NC}"
        CHROME_PATH="/usr/bin/google-chrome"
    else
        echo "Google Chrome failed, installing Chromium..."
        apt install -y chromium-browser
        CHROME_PATH="/usr/bin/chromium-browser"
        echo -e "${GREEN}✅ Chromium installed${NC}"
    fi
else
    CHROME_PATH=$(which google-chrome 2>/dev/null || which chromium-browser 2>/dev/null)
    echo -e "${GREEN}✅ Chrome/Chromium already installed at: $CHROME_PATH${NC}"
fi

# Install Node.js if not present or wrong version
NODE_VERSION=$(node --version 2>/dev/null | cut -d'v' -f2 | cut -d'.' -f1)
if [[ -z "$NODE_VERSION" ]] || [[ "$NODE_VERSION" -lt 18 ]]; then
    echo "Installing Node.js 18..."
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
    apt install -y nodejs
    echo -e "${GREEN}✅ Node.js $(node --version) installed${NC}"
else
    echo -e "${GREEN}✅ Node.js $(node --version) already installed${NC}"
fi

# Install additional packages
apt install -y \
    zip unzip \
    git \
    curl \
    supervisor \
    imagemagick \
    jpegoptim optipng pngquant gifsicle \
    libpng-dev libjpeg-dev libfreetype6-dev \
    fonts-liberation libappindicator3-1 libasound2 libatk-bridge2.0-0 \
    libgtk-3-0 libnspr4 libnss3 libx11-xcb1 libxcomposite1 libxcursor1 \
    libxdamage1 libxi6 libxtst6 xdg-utils

echo -e "${GREEN}✅ System dependencies installed${NC}"

echo ""
echo "📁 Creating directories..."

# Create templates directory
mkdir -p "$TEMPLATES_ROOT"
chown -R www-data:www-data "$TEMPLATES_ROOT"
chmod -R 755 "$TEMPLATES_ROOT"
echo -e "${GREEN}✅ Templates directory created: $TEMPLATES_ROOT${NC}"

# Create application directories
mkdir -p "$APP_PATH"
mkdir -p "/var/www/vhosts/$DOMAIN_NAME/backups"
mkdir -p "/var/www/vhosts/$DOMAIN_NAME/logs"

# Set ownership
chown -R www-data:www-data "/var/www/vhosts/$DOMAIN_NAME"

echo -e "${GREEN}✅ Application directories created${NC}"

echo ""
echo "🔧 Configuring system for screenshot generation..."

# Create Chrome wrapper script for screenshot generation
cat > /usr/local/bin/chrome-headless <<'EOF'
#!/bin/bash
# Chrome wrapper for headless screenshot generation
exec /usr/bin/google-chrome \
  --no-sandbox \
  --disable-dev-shm-usage \
  --disable-gpu \
  --disable-extensions \
  --disable-plugins \
  --disable-images \
  --disable-javascript \
  --virtual-time-budget=5000 \
  --run-all-compositor-stages-before-draw \
  --disable-background-timer-throttling \
  --disable-renderer-backgrounding \
  --disable-backgrounding-occluded-windows \
  --disable-client-side-phishing-detection \
  --disable-default-apps \
  --disable-hang-monitor \
  --disable-popup-blocking \
  --disable-prompt-on-repost \
  --disable-sync \
  --disable-translate \
  --metrics-recording-only \
  --no-first-run \
  --safebrowsing-disable-auto-update \
  --password-store=basic \
  --use-mock-keychain \
  "$@"
EOF

chmod +x /usr/local/bin/chrome-headless

# Test Chrome installation
if $CHROME_PATH --version > /dev/null 2>&1; then
    echo -e "${GREEN}✅ Chrome is working: $($CHROME_PATH --version)${NC}"
else
    echo -e "${RED}❌ Chrome installation failed${NC}"
    exit 1
fi

echo ""
echo "⏰ Setting up log rotation..."

# Create log rotation configuration
tee /etc/logrotate.d/wordpress-templates > /dev/null <<EOF
/var/www/vhosts/$DOMAIN_NAME/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
EOF

echo -e "${GREEN}✅ Log rotation configured${NC}"

echo ""
echo "🧪 Creating test WordPress site..."

# Create a test WordPress site for screenshot testing
TEST_WP_PATH="$TEMPLATES_ROOT/test-wp/httpdocs"
mkdir -p "$TEST_WP_PATH"

tee "$TEST_WP_PATH/index.php" > /dev/null <<'EOF'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test WordPress Site - Screenshot Testing</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            line-height: 1.6; 
            color: #333; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 40px 20px; 
        }
        .header { 
            background: rgba(255, 255, 255, 0.95); 
            padding: 40px; 
            border-radius: 20px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 { 
            color: #2c3e50; 
            font-size: 3em; 
            margin-bottom: 20px; 
            font-weight: 700;
        }
        .header p { 
            color: #7f8c8d; 
            font-size: 1.2em; 
        }
        .content { 
            background: rgba(255, 255, 255, 0.9); 
            padding: 40px; 
            border-radius: 20px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); 
            gap: 30px; 
            margin-top: 30px; 
        }
        .card { 
            background: #fff; 
            padding: 30px; 
            border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); 
            transition: transform 0.3s ease;
        }
        .card:hover { 
            transform: translateY(-5px); 
        }
        .card h3 { 
            color: #3498db; 
            margin-bottom: 15px; 
            font-size: 1.5em;
        }
        .status { 
            display: inline-block; 
            background: #27ae60; 
            color: white; 
            padding: 8px 16px; 
            border-radius: 25px; 
            font-size: 0.9em; 
            margin-top: 15px;
        }
        .timestamp { 
            color: #95a5a6; 
            font-style: italic; 
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 Test WordPress Site</h1>
            <p>Screenshot Testing for WordPress Templates Catalog</p>
        </div>
        <div class="content">
            <h2>Screenshot Generation Test</h2>
            <p>This page is specifically designed for testing the automated screenshot generation system of the WordPress Templates Catalog application.</p>
            
            <div class="grid">
                <div class="card">
                    <h3>📸 Screenshot System</h3>
                    <p>Tests Puppeteer-based browser automation for capturing website screenshots automatically.</p>
                    <div class="status">✅ Active</div>
                </div>
                
                <div class="card">
                    <h3>🔍 Template Scanner</h3>
                    <p>Validates the WordPress installation detection and metadata extraction process.</p>
                    <div class="status">✅ Working</div>
                </div>
                
                <div class="card">
                    <h3>🌐 Responsive Design</h3>
                    <p>Ensures screenshots look good across different viewport sizes and devices.</p>
                    <div class="status">✅ Responsive</div>
                </div>
            </div>
            
            <div class="timestamp">
                Generated at: <?php echo date('Y-m-d H:i:s T'); ?>
            </div>
        </div>
    </div>
</body>
</html>
EOF

chown -R www-data:www-data "$TEST_WP_PATH"
chmod -R 755 "$TEST_WP_PATH"

echo -e "${GREEN}✅ Test WordPress site created at: $TEST_WP_PATH${NC}"

echo ""
echo "🔒 Setting up security and permissions..."

# Create a secure directory for sensitive files
mkdir -p "/var/www/vhosts/$DOMAIN_NAME/secure"
chown www-data:www-data "/var/www/vhosts/$DOMAIN_NAME/secure"
chmod 700 "/var/www/vhosts/$DOMAIN_NAME/secure"

# Set proper permissions for Chrome to run
chmod +x $CHROME_PATH

echo -e "${GREEN}✅ Security configuration completed${NC}"

echo ""
echo "📋 Creating deployment checklist..."

cat > "/root/wordpress-templates-deployment-checklist.txt" <<EOF
WordPress Templates Catalog - Deployment Checklist
==================================================

✅ Server Setup Completed on $(date)

System Information:
------------------
- Chrome/Chromium: $CHROME_PATH
- Chrome Version: $($CHROME_PATH --version 2>/dev/null || echo "Error getting version")
- Node.js Version: $(node --version 2>/dev/null || echo "Not found")
- PHP Version: $(php --version 2>/dev/null | head -n1 || echo "Not found")

Paths Created:
--------------
- Domain: $DOMAIN_NAME
- Application: $APP_PATH  
- Templates: $TEMPLATES_ROOT
- Backups: /var/www/vhosts/$DOMAIN_NAME/backups
- Logs: /var/www/vhosts/$DOMAIN_NAME/logs
- Test site: $TEMPLATES_ROOT/test-wp/httpdocs

Next Steps - PLESK CONFIGURATION:
---------------------------------

1. DATABASE SETUP:
   □ Go to Plesk > Databases
   □ Create database: wordpress_templates
   □ Create database user with full privileges
   □ Note credentials for .env file

2. DOMAIN CONFIGURATION:
   □ Go to Plesk > Domains > $DOMAIN_NAME
   □ Set Document Root: $APP_PATH
   □ Set PHP version: 8.2 or higher
   □ Copy nginx config from deploy/nginx-config.conf to:
     Apache & nginx Settings > Additional nginx directives

3. ENVIRONMENT FILE:
   □ Copy deploy/production.env.example to $APP_PATH/.env
   □ Update database credentials, domain, and API token
   □ Generate app key with: php artisan key:generate

4. SCHEDULED TASKS:
   □ Go to Plesk > Tools & Settings > Scheduled Tasks
   □ Add tasks from deploy/plesk-cron-jobs.txt:
     - */15 * * * * templates:scan (every 15 minutes)
     - 0 2 * * * templates:screenshot (daily at 2 AM)
     - 0 3 * * 0 storage:link (weekly maintenance)

5. GITHUB SECRETS:
   Add these secrets to GitHub repository:
   □ PLESK_HOST: $(curl -s ifconfig.me || hostname -I | awk '{print $1}')
   □ PLESK_USERNAME: $(whoami)
   □ PLESK_SSH_KEY: (your private SSH key)
   □ PLESK_SSH_PORT: 22
   □ DOMAIN_NAME: $DOMAIN_NAME

6. FIRST DEPLOYMENT TEST:
   □ Push to main branch to trigger deployment
   □ Monitor: GitHub > Actions
   □ Test URLs:
     - https://$DOMAIN_NAME/en/templates
     - https://$DOMAIN_NAME/de/vorlagen  
     - https://$DOMAIN_NAME/test-wp/

7. VERIFY SCREENSHOT SYSTEM:
   After deployment, test:
   □ cd $APP_PATH
   □ php artisan templates:scan -v
   □ php artisan templates:screenshot --slug=test-wp --force -v

TROUBLESHOOTING:
---------------
- Application logs: $APP_PATH/storage/logs/
- Nginx logs: /var/www/vhosts/$DOMAIN_NAME/logs/
- Test Chrome: $CHROME_PATH --headless --dump-dom https://example.com
- Test screenshot: $CHROME_PATH --headless --screenshot=/tmp/test.png https://example.com

SECURITY NOTES:
--------------
- Never commit .env file to git
- Use strong database passwords
- Keep API_TOKEN secure and random
- Regular security updates recommended

EOF

echo -e "${GREEN}✅ Deployment checklist created: /root/wordpress-templates-deployment-checklist.txt${NC}"

echo ""
echo -e "${GREEN}🎉 Server setup completed successfully!${NC}"
echo ""
echo -e "${YELLOW}📋 NEXT STEPS:${NC}"
echo "1. View full checklist: cat /root/wordpress-templates-deployment-checklist.txt"
echo "2. Configure Plesk (database, domain settings, nginx config)"
echo "3. Set up GitHub secrets for automatic deployment"
echo "4. Create .env file with production settings"
echo "5. Push to main branch to trigger first deployment"
echo ""
echo -e "${GREEN}🧪 TEST URLS (after deployment):${NC}"
echo "- Test site: https://$DOMAIN_NAME/test-wp/"
echo "- Application: https://$DOMAIN_NAME/en/templates"
echo ""
echo -e "${YELLOW}💡 Quick test Chrome:${NC}"
echo "$CHROME_PATH --headless --screenshot=/tmp/test.png --window-size=1200,800 https://example.com"

# Display the checklist
echo ""
echo "📋 DEPLOYMENT CHECKLIST:"
echo "========================"
cat /root/wordpress-templates-deployment-checklist.txt