#!/bin/bash

# WordPress Templates Catalog - Universal Server Setup Script
# Compatible with CentOS/RHEL, Ubuntu/Debian, and other Linux distributions

set -e

echo "🚀 WordPress Templates Catalog - Universal Server Setup"
echo "========================================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
DOMAIN_NAME=${1:-"your-domain.com"}
APP_PATH="/var/www/vhosts/$DOMAIN_NAME/httpdocs"
TEMPLATES_ROOT="/srv/templates"

echo -e "${YELLOW}Domain: $DOMAIN_NAME${NC}"
echo -e "${YELLOW}App Path: $APP_PATH${NC}"
echo -e "${YELLOW}Templates Root: $TEMPLATES_ROOT${NC}"

# Check if running as root
if [[ $EUID -ne 0 ]]; then
   echo -e "${RED}This script must be run as root${NC}"
   echo "Usage: sudo $0 your-domain.com"
   exit 1
fi

echo ""
echo "🔍 Detecting Linux distribution..."

# Detect Linux distribution
if [ -f /etc/os-release ]; then
    . /etc/os-release
    OS=$NAME
    VER=$VERSION_ID
    echo -e "${BLUE}Detected: $OS $VER${NC}"
elif type lsb_release >/dev/null 2>&1; then
    OS=$(lsb_release -si)
    VER=$(lsb_release -sr)
    echo -e "${BLUE}Detected: $OS $VER${NC}"
else
    OS=$(uname -s)
    VER=$(uname -r)
    echo -e "${YELLOW}Unknown distribution: $OS $VER${NC}"
fi

# Set package manager and commands based on distribution
if [[ "$OS" == *"CentOS"* ]] || [[ "$OS" == *"Red Hat"* ]] || [[ "$OS" == *"Rocky"* ]] || [[ "$OS" == *"AlmaLinux"* ]]; then
    PKG_MANAGER="yum"
    PKG_INSTALL="yum install -y"
    PKG_UPDATE="yum update -y"
    CHROME_PKG="google-chrome-stable"
    EPEL_RELEASE="epel-release"
    
    # Check if dnf is available (newer RHEL/CentOS)
    if command -v dnf >/dev/null 2>&1; then
        PKG_MANAGER="dnf"
        PKG_INSTALL="dnf install -y"
        PKG_UPDATE="dnf update -y"
    fi
    
    echo -e "${BLUE}Using package manager: $PKG_MANAGER${NC}"
    
elif [[ "$OS" == *"Ubuntu"* ]] || [[ "$OS" == *"Debian"* ]]; then
    PKG_MANAGER="apt"
    PKG_INSTALL="apt install -y"
    PKG_UPDATE="apt update"
    CHROME_PKG="google-chrome-stable"
    
    echo -e "${BLUE}Using package manager: $PKG_MANAGER${NC}"
    
elif [[ "$OS" == *"SUSE"* ]] || [[ "$OS" == *"openSUSE"* ]]; then
    PKG_MANAGER="zypper"
    PKG_INSTALL="zypper install -y"
    PKG_UPDATE="zypper refresh"
    CHROME_PKG="google-chrome-stable"
    
    echo -e "${BLUE}Using package manager: $PKG_MANAGER${NC}"
    
else
    echo -e "${YELLOW}⚠️  Unknown package manager. Manual installation may be required.${NC}"
    PKG_MANAGER="unknown"
fi

echo ""
echo "📦 Installing system dependencies..."

# Update package list
if [[ "$PKG_MANAGER" != "unknown" ]]; then
    echo "Updating package repositories..."
    $PKG_UPDATE
fi

# Install Chrome/Chromium based on distribution
install_chrome() {
    if command -v google-chrome >/dev/null 2>&1 || command -v chromium-browser >/dev/null 2>&1; then
        CHROME_PATH=$(which google-chrome 2>/dev/null || which chromium-browser 2>/dev/null)
        echo -e "${GREEN}✅ Chrome/Chromium already installed: $CHROME_PATH${NC}"
        return 0
    fi
    
    if [[ "$PKG_MANAGER" == "yum" ]] || [[ "$PKG_MANAGER" == "dnf" ]]; then
        echo "Installing Chrome on CentOS/RHEL..."
        
        # Install EPEL repository
        $PKG_INSTALL $EPEL_RELEASE || true
        
        # Add Google Chrome repository
        cat > /etc/yum.repos.d/google-chrome.repo <<'EOF'
[google-chrome]
name=google-chrome
baseurl=http://dl.google.com/linux/chrome/rpm/stable/$basearch
enabled=1
gpgcheck=1
gpgkey=https://dl-ssl.google.com/linux/linux_signing_key.pub
EOF
        
        # Install Chrome
        if $PKG_INSTALL google-chrome-stable; then
            CHROME_PATH="/usr/bin/google-chrome"
            echo -e "${GREEN}✅ Google Chrome installed${NC}"
        else
            echo "Trying Chromium..."
            $PKG_INSTALL chromium || {
                echo -e "${YELLOW}⚠️  Chrome installation failed. Please install manually.${NC}"
                CHROME_PATH="/usr/bin/chromium-browser"
            }
        fi
        
    elif [[ "$PKG_MANAGER" == "apt" ]]; then
        echo "Installing Chrome on Ubuntu/Debian..."
        
        # Install dependencies
        $PKG_INSTALL wget gnupg software-properties-common
        
        # Add Google Chrome repository
        wget -q -O - https://dl.google.com/linux/linux_signing_key.pub | apt-key add -
        echo "deb [arch=amd64] http://dl.google.com/linux/chrome/deb/ stable main" > /etc/apt/sources.list.d/google-chrome.list
        apt update
        
        if $PKG_INSTALL google-chrome-stable; then
            CHROME_PATH="/usr/bin/google-chrome"
            echo -e "${GREEN}✅ Google Chrome installed${NC}"
        else
            echo "Trying Chromium..."
            $PKG_INSTALL chromium-browser
            CHROME_PATH="/usr/bin/chromium-browser"
            echo -e "${GREEN}✅ Chromium installed${NC}"
        fi
        
    elif [[ "$PKG_MANAGER" == "zypper" ]]; then
        echo "Installing Chrome on openSUSE..."
        
        # Add Google Chrome repository
        zypper addrepo -f http://dl.google.com/linux/chrome/rpm/stable/x86_64 google-chrome
        zypper --gpg-auto-import-keys refresh
        
        if $PKG_INSTALL google-chrome-stable; then
            CHROME_PATH="/usr/bin/google-chrome"
            echo -e "${GREEN}✅ Google Chrome installed${NC}"
        else
            echo "Trying Chromium..."
            $PKG_INSTALL chromium
            CHROME_PATH="/usr/bin/chromium"
            echo -e "${GREEN}✅ Chromium installed${NC}"
        fi
    else
        echo -e "${YELLOW}⚠️  Unknown package manager. Please install Chrome/Chromium manually.${NC}"
        CHROME_PATH="/usr/bin/google-chrome"
    fi
}

install_chrome

# Install Node.js
install_nodejs() {
    NODE_VERSION=$(node --version 2>/dev/null | cut -d'v' -f2 | cut -d'.' -f1)
    if [[ -n "$NODE_VERSION" ]] && [[ "$NODE_VERSION" -ge 18 ]]; then
        echo -e "${GREEN}✅ Node.js $(node --version) already installed${NC}"
        return 0
    fi
    
    echo "Installing Node.js 18..."
    
    # Install Node.js using NodeSource repository (works on most distributions)
    if command -v curl >/dev/null 2>&1; then
        curl -fsSL https://rpm.nodesource.com/setup_18.x | bash - 2>/dev/null || \
        curl -fsSL https://deb.nodesource.com/setup_18.x | bash - 2>/dev/null || {
            echo "NodeSource setup failed, trying manual installation..."
            
            # Try direct installation based on package manager
            if [[ "$PKG_MANAGER" == "yum" ]] || [[ "$PKG_MANAGER" == "dnf" ]]; then
                $PKG_INSTALL nodejs npm
            elif [[ "$PKG_MANAGER" == "apt" ]]; then
                $PKG_INSTALL nodejs npm
            elif [[ "$PKG_MANAGER" == "zypper" ]]; then
                $PKG_INSTALL nodejs18 npm18
            fi
        }
        
        # Install Node.js
        if [[ "$PKG_MANAGER" != "unknown" ]]; then
            $PKG_INSTALL nodejs npm
        fi
        
        echo -e "${GREEN}✅ Node.js $(node --version 2>/dev/null || echo 'installed') installed${NC}"
    else
        echo -e "${YELLOW}⚠️  curl not found. Please install Node.js 18+ manually.${NC}"
    fi
}

install_nodejs

# Install additional packages based on distribution
install_additional_packages() {
    echo "Installing additional packages..."
    
    if [[ "$PKG_MANAGER" == "yum" ]] || [[ "$PKG_MANAGER" == "dnf" ]]; then
        $PKG_INSTALL \
            zip unzip \
            git \
            curl wget \
            supervisor \
            ImageMagick \
            jpegoptim optipng \
            liberation-fonts \
            alsa-lib \
            atk \
            gtk3 \
            libXcomposite \
            libXcursor \
            libXdamage \
            libXi \
            libXrandr \
            libXScrnSaver \
            libXtst \
            nss \
            xdg-utils
            
    elif [[ "$PKG_MANAGER" == "apt" ]]; then
        $PKG_INSTALL \
            zip unzip \
            git \
            curl wget \
            supervisor \
            imagemagick \
            jpegoptim optipng pngquant gifsicle \
            libpng-dev libjpeg-dev libfreetype6-dev \
            fonts-liberation libappindicator3-1 libasound2 libatk-bridge2.0-0 \
            libgtk-3-0 libnspr4 libnss3 libx11-xcb1 libxcomposite1 libxcursor1 \
            libxdamage1 libxi6 libxtst6 xdg-utils
            
    elif [[ "$PKG_MANAGER" == "zypper" ]]; then
        $PKG_INSTALL \
            zip unzip \
            git \
            curl wget \
            supervisor \
            ImageMagick \
            jpegoptim optipng \
            liberation-fonts \
            libasound2 \
            libatk-1_0-0 \
            libgtk-3-0 \
            libXcomposite1 \
            libXcursor1 \
            libXdamage1 \
            libXi6 \
            libXrandr2 \
            libXss1 \
            libXtst6 \
            mozilla-nss \
            xdg-utils
    fi
}

install_additional_packages

echo -e "${GREEN}✅ System dependencies installation completed${NC}"

echo ""
echo "📁 Creating directories..."

# Create templates directory
mkdir -p "$TEMPLATES_ROOT"
if command -v chown >/dev/null 2>&1; then
    chown -R apache:apache "$TEMPLATES_ROOT" 2>/dev/null || \
    chown -R www-data:www-data "$TEMPLATES_ROOT" 2>/dev/null || \
    chown -R nginx:nginx "$TEMPLATES_ROOT" 2>/dev/null || \
    echo -e "${YELLOW}⚠️  Could not set ownership. Please set manually: chown -R webuser:webgroup $TEMPLATES_ROOT${NC}"
fi
chmod -R 755 "$TEMPLATES_ROOT"
echo -e "${GREEN}✅ Templates directory created: $TEMPLATES_ROOT${NC}"

# Create application directories
mkdir -p "$APP_PATH"
mkdir -p "/var/www/vhosts/$DOMAIN_NAME/backups"
mkdir -p "/var/www/vhosts/$DOMAIN_NAME/logs"

# Try to set ownership with common web server users
if command -v chown >/dev/null 2>&1; then
    chown -R apache:apache "/var/www/vhosts/$DOMAIN_NAME" 2>/dev/null || \
    chown -R www-data:www-data "/var/www/vhosts/$DOMAIN_NAME" 2>/dev/null || \
    chown -R nginx:nginx "/var/www/vhosts/$DOMAIN_NAME" 2>/dev/null || \
    echo -e "${YELLOW}⚠️  Could not set ownership. Please set manually after Plesk creates the domain.${NC}"
fi

echo -e "${GREEN}✅ Application directories created${NC}"

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
        .system-info {
            background: #ecf0f1;
            padding: 20px;
            border-radius: 10px;
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
            <p>This page is specifically designed for testing the automated screenshot generation system.</p>
            
            <div class="grid">
                <div class="card">
                    <h3>📸 Screenshot System</h3>
                    <p>Tests Puppeteer-based browser automation for capturing website screenshots.</p>
                    <div class="status">✅ Active</div>
                </div>
                
                <div class="card">
                    <h3>🔍 Template Scanner</h3>
                    <p>Validates the WordPress installation detection and metadata extraction.</p>
                    <div class="status">✅ Working</div>
                </div>
                
                <div class="card">
                    <h3>🌐 Responsive Design</h3>
                    <p>Ensures screenshots look good across different viewport sizes.</p>
                    <div class="status">✅ Responsive</div>
                </div>
            </div>
            
            <div class="system-info">
                <h3>System Information</h3>
                <p><strong>Server:</strong> <?php echo php_uname('n'); ?></p>
                <p><strong>OS:</strong> <?php echo php_uname('s') . ' ' . php_uname('r'); ?></p>
                <p><strong>PHP:</strong> <?php echo PHP_VERSION; ?></p>
                <p><strong>Generated:</strong> <?php echo date('Y-m-d H:i:s T'); ?></p>
            </div>
        </div>
    </div>
</body>
</html>
EOF

chmod -R 755 "$TEST_WP_PATH"
echo -e "${GREEN}✅ Test WordPress site created at: $TEST_WP_PATH${NC}"

echo ""
echo "📋 Creating deployment checklist..."

# Get system information
CHROME_VERSION="Not installed"
if [[ -n "$CHROME_PATH" ]] && command -v "$CHROME_PATH" >/dev/null 2>&1; then
    CHROME_VERSION=$($CHROME_PATH --version 2>/dev/null || echo "Installed but version unknown")
fi

NODE_VERSION_INFO=$(node --version 2>/dev/null || echo "Not found")
PHP_VERSION_INFO=$(php --version 2>/dev/null | head -n1 || echo "Not found")
SERVER_IP=$(curl -s ifconfig.me 2>/dev/null || hostname -I 2>/dev/null | awk '{print $1}' || echo "Unable to detect")

cat > "/root/wordpress-templates-deployment-checklist.txt" <<EOF
WordPress Templates Catalog - Universal Deployment Checklist
============================================================

✅ Server Setup Completed on $(date)

System Information:
------------------
- Distribution: $OS $VER  
- Chrome Path: $CHROME_PATH
- Chrome Version: $CHROME_VERSION
- Node.js Version: $NODE_VERSION_INFO
- PHP Version: $PHP_VERSION_INFO
- Server IP: $SERVER_IP

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
   □ Set CHROME_BINARY_PATH=$CHROME_PATH
   □ Generate app key with: php artisan key:generate

4. SCHEDULED TASKS:
   □ Go to Plesk > Tools & Settings > Scheduled Tasks
   □ Add tasks from deploy/plesk-cron-jobs.txt:
     - */15 * * * * templates:scan (every 15 minutes)
     - 0 2 * * * templates:screenshot (daily at 2 AM)
     - 0 3 * * 0 storage:link (weekly maintenance)

5. GITHUB SECRETS:
   Add these secrets to GitHub repository:
   □ PLESK_HOST: $SERVER_IP
   □ PLESK_USERNAME: root (or your SSH user)
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
- Test Chrome: $CHROME_PATH --headless --dump-dom https://example.com
- Test screenshot: $CHROME_PATH --headless --screenshot=/tmp/test.png --window-size=1200,800 https://example.com
- Application logs: $APP_PATH/storage/logs/
- Check permissions: ls -la $TEMPLATES_ROOT

MANUAL STEPS IF NEEDED:
-----------------------
If automatic installation failed:
- Chrome: Download from https://www.google.com/chrome/ and install manually
- Node.js: Download from https://nodejs.org/ (version 18+)
- Set permissions: chown -R webuser:webgroup $TEMPLATES_ROOT $APP_PATH

SECURITY NOTES:
--------------
- Never commit .env file to git
- Use strong database passwords
- Keep API_TOKEN secure and random (generate with: openssl rand -base64 64)
- Regular security updates recommended

EOF

echo -e "${GREEN}✅ Deployment checklist created: /root/wordpress-templates-deployment-checklist.txt${NC}"

echo ""
echo -e "${GREEN}🎉 Universal server setup completed!${NC}"
echo ""
echo -e "${YELLOW}📋 NEXT STEPS:${NC}"
echo "1. View checklist: cat /root/wordpress-templates-deployment-checklist.txt"
echo "2. Configure Plesk (database, domain, nginx rules)"
echo "3. Set up GitHub secrets for automatic deployment"
echo "4. Create .env file with production settings"
echo "5. Test the system"
echo ""
echo -e "${GREEN}🧪 QUICK TESTS:${NC}"
echo "- Test Chrome: $CHROME_PATH --version"
echo "- Test Node.js: node --version"
echo "- Test PHP: php --version"
echo "- Test site: curl -I https://$DOMAIN_NAME/test-wp/ (after setup)"
echo ""
echo -e "${BLUE}💡 If Chrome test fails, install manually from:${NC}"
echo "   https://www.google.com/chrome/"

# Display the checklist
echo ""
echo "📋 DEPLOYMENT CHECKLIST:"
echo "========================"
cat /root/wordpress-templates-deployment-checklist.txt