#!/bin/bash
# Install screenshot dependencies for Plesk server
# This installs Node.js, npm, Chrome, and Puppeteer for Laravel screenshot functionality

set -e

echo "🚀 Installing screenshot dependencies for WordPress Templates..."

# Detect OS
if [ -f /etc/redhat-release ]; then
    OS="centos"
    echo "📍 Detected CentOS/RHEL/CloudLinux"
elif [ -f /etc/debian_version ]; then
    OS="debian"
    echo "📍 Detected Debian/Ubuntu"
else
    echo "❌ Unsupported OS. This script supports CentOS/RHEL and Debian/Ubuntu."
    exit 1
fi

# Step 1: Install Node.js 18
echo "📦 Installing Node.js 18..."

if [ "$OS" = "centos" ]; then
    # CentOS/RHEL/CloudLinux
    if ! command -v node >/dev/null 2>&1 || [[ $(node --version) < "v18" ]]; then
        echo "🔧 Installing Node.js 18 from NodeSource..."
        curl -fsSL https://rpm.nodesource.com/setup_18.x | bash -
        yum install -y nodejs
    else
        echo "✅ Node.js 18+ already installed: $(node --version)"
    fi
elif [ "$OS" = "debian" ]; then
    # Debian/Ubuntu
    if ! command -v node >/dev/null 2>&1 || [[ $(node --version) < "v18" ]]; then
        echo "🔧 Installing Node.js 18 from NodeSource..."
        curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
        apt-get install -y nodejs
    else
        echo "✅ Node.js 18+ already installed: $(node --version)"
    fi
fi

# Verify Node.js installation
if command -v node >/dev/null 2>&1 && command -v npm >/dev/null 2>&1; then
    echo "✅ Node.js: $(node --version)"
    echo "✅ npm: $(npm --version)"
else
    echo "❌ Node.js installation failed!"
    exit 1
fi

# Step 2: Install Google Chrome
echo "🌐 Installing Google Chrome..."

if [ "$OS" = "centos" ]; then
    # CentOS/RHEL/CloudLinux
    if ! command -v google-chrome-stable >/dev/null 2>&1; then
        echo "🔧 Installing Google Chrome..."
        cat > /etc/yum.repos.d/google-chrome.repo << 'EOF'
[google-chrome]
name=google-chrome
baseurl=http://dl.google.com/linux/chrome/rpm/stable/$basearch
enabled=1
gpgcheck=1
gpgkey=https://dl-ssl.google.com/linux/linux_signing_key.pub
EOF
        yum install -y google-chrome-stable
    else
        echo "✅ Google Chrome already installed"
    fi
elif [ "$OS" = "debian" ]; then
    # Debian/Ubuntu
    if ! command -v google-chrome-stable >/dev/null 2>&1; then
        echo "🔧 Installing Google Chrome..."
        wget -q -O - https://dl-ssl.google.com/linux/linux_signing_key.pub | apt-key add -
        echo 'deb [arch=amd64] http://dl.google.com/linux/chrome/deb/ stable main' > /etc/apt/sources.list.d/google.list
        apt-get update
        apt-get install -y google-chrome-stable
    else
        echo "✅ Google Chrome already installed"
    fi
fi

# Verify Chrome installation
if command -v google-chrome-stable >/dev/null 2>&1; then
    CHROME_VERSION=$(google-chrome-stable --version)
    echo "✅ Chrome: $CHROME_VERSION"
else
    echo "❌ Chrome installation failed!"
    exit 1
fi

# Step 3: Install additional dependencies for headless Chrome
echo "🔧 Installing Chrome dependencies..."

if [ "$OS" = "centos" ]; then
    yum install -y \
        libX11 libX11-devel libXcomposite libXcursor libXdamage libXext \
        libXi libXrender libXss libXtst cups-libs libXScrnSaver \
        libXrandr alsa-lib cairo cairo-devel gtk3 pango atk at-spi2-atk \
        libdrm xorg-x11-server-Xvfb gtk3-devel alsa-lib-devel
elif [ "$OS" = "debian" ]; then
    apt-get update
    apt-get install -y \
        libnss3-dev libatk-bridge2.0-0 libdrm2 libxkbcommon0 libxcomposite1 \
        libxdamage1 libxrandr2 libgbm1 libgtk-3-0 libxss1 libasound2 \
        fonts-liberation libappindicator3-1 xdg-utils
fi

# Step 4: Install Puppeteer in Laravel project
APP_PATH="/var/www/vhosts/wp-templates.metanow.dev/httpdocs"

if [ -d "$APP_PATH" ]; then
    echo "📱 Installing Puppeteer in Laravel project..."
    cd "$APP_PATH"
    
    # Install Puppeteer (skip Chromium download since we have Chrome)
    echo "🎭 Installing Puppeteer..."
    npm install puppeteer --save
    
    # Verify package.json has puppeteer
    if npm list puppeteer >/dev/null 2>&1; then
        echo "✅ Puppeteer installed successfully"
    else
        echo "❌ Puppeteer installation failed!"
        exit 1
    fi
    
    # Set correct ownership
    echo "🔒 Setting correct ownership..."
    chown -R wp-templates.metanow_r6s2v1oe7wr:psacln node_modules package*.json
    
else
    echo "❌ Laravel project not found at $APP_PATH"
    echo "Please deploy the application first, then run this script."
    exit 1
fi

# Step 5: Test the installation
echo "🧪 Testing screenshot functionality..."
cd "$APP_PATH"

# Test Node.js and Puppeteer
cat > test-puppeteer.js << 'EOF'
const puppeteer = require('puppeteer');

(async () => {
    try {
        console.log('Testing Puppeteer...');
        const browser = await puppeteer.launch({
            headless: true,
            executablePath: '/usr/bin/google-chrome-stable',
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-gpu',
                '--remote-debugging-port=9222'
            ]
        });
        console.log('✅ Browser launched successfully');
        
        const page = await browser.newPage();
        await page.setViewport({ width: 1280, height: 720 });
        await page.goto('https://example.com');
        console.log('✅ Page navigation successful');
        
        await browser.close();
        console.log('✅ Puppeteer test completed successfully!');
        
    } catch (error) {
        console.error('❌ Puppeteer test failed:', error.message);
        process.exit(1);
    }
})();
EOF

echo "🎯 Running Puppeteer test..."
if node test-puppeteer.js; then
    echo "✅ Screenshot system is ready!"
    rm test-puppeteer.js
else
    echo "❌ Puppeteer test failed. Check the error above."
    rm test-puppeteer.js
    exit 1
fi

# Step 6: Test Laravel screenshot command
echo "🖼️  Testing Laravel screenshot command..."
if php artisan templates:screenshot --help >/dev/null 2>&1; then
    echo "✅ Laravel screenshot command is available"
    
    # Test with a simple screenshot (if templates exist)
    TEMPLATE_COUNT=$(php artisan tinker --execute="echo App\Models\Template::count();" 2>/dev/null || echo "0")
    if [ "$TEMPLATE_COUNT" -gt 0 ]; then
        echo "📸 Testing screenshot generation..."
        php artisan templates:screenshot --limit=1 --timeout=60
        echo "✅ Screenshot test completed"
    else
        echo "ℹ️  No templates found to screenshot. Run 'php artisan templates:scan' first."
    fi
else
    echo "❌ Laravel screenshot command not found"
fi

echo ""
echo "🎉 Screenshot dependencies installation completed!"
echo ""
echo "📋 Summary:"
echo "   Node.js: $(node --version)"
echo "   npm: $(npm --version)" 
echo "   Chrome: $(google-chrome-stable --version | cut -d' ' -f3)"
echo "   Puppeteer: $(npm list puppeteer --depth=0 2>/dev/null | grep puppeteer || echo 'installed')"
echo ""
echo "🚀 You can now use:"
echo "   php artisan templates:screenshot"
echo "   php artisan templates:screenshot --force"
echo "   php artisan templates:screenshot --limit=5"
echo ""