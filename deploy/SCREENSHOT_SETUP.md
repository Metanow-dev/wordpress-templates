# Screenshot System Setup Guide

## Quick Installation (Run on Plesk Server)

```bash
# SSH to server as root
cd /var/www/vhosts/wp-templates.metanow.dev/httpdocs
chmod +x deploy/install-screenshot-deps.sh
./deploy/install-screenshot-deps.sh
```

## What Gets Installed

1. **Node.js 18** - Required for running Puppeteer
2. **Google Chrome** - Browser for capturing screenshots  
3. **System dependencies** - Libraries needed for headless Chrome
4. **Puppeteer npm package** - Controls Chrome programmatically

## Manual Installation Steps

If the automated script fails, follow these manual steps:

### 1. Install Node.js 18

**CentOS/RHEL/CloudLinux:**
```bash
curl -fsSL https://rpm.nodesource.com/setup_18.x | bash -
yum install -y nodejs
```

**Ubuntu/Debian:**
```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt-get install -y nodejs
```

### 2. Install Google Chrome

**CentOS/RHEL/CloudLinux:**
```bash
cat > /etc/yum.repos.d/google-chrome.repo << 'EOF'
[google-chrome]
name=google-chrome
baseurl=http://dl.google.com/linux/chrome/rpm/stable/$basearch
enabled=1
gpgcheck=1
gpgkey=https://dl-ssl.google.com/linux/linux_signing_key.pub
EOF
yum install -y google-chrome-stable
```

**Ubuntu/Debian:**
```bash
wget -q -O - https://dl-ssl.google.com/linux/linux_signing_key.pub | apt-key add -
echo 'deb [arch=amd64] http://dl.google.com/linux/chrome/deb/ stable main' > /etc/apt/sources.list.d/google.list
apt-get update && apt-get install -y google-chrome-stable
```

### 3. Install Puppeteer

```bash
cd /var/www/vhosts/wp-templates.metanow.dev/httpdocs
npm install puppeteer --save
chown -R wp-templates.metanow_r6s2v1oe7wr:psacln node_modules package*.json
```

## Testing Installation

### Test Node.js and Chrome
```bash
node --version    # Should show v18.x.x
npm --version     # Should show 9.x.x or higher
google-chrome-stable --version  # Should show Chrome version
```

### Test Puppeteer
```bash
cd /var/www/vhosts/wp-templates.metanow.dev/httpdocs
node -e "console.log(require('puppeteer').executablePath())"
```

### Test Laravel Screenshots
```bash
# Check command exists
php artisan templates:screenshot --help

# Generate screenshots (if templates exist)
php artisan templates:screenshot --limit=1 --force
```

## Troubleshooting

### "Module not found: puppeteer"
```bash
# Reinstall Puppeteer
cd /var/www/vhosts/wp-templates.metanow.dev/httpdocs
npm install puppeteer --save
```

### Chrome crashes or won't start
```bash
# Install missing dependencies (CentOS/RHEL)
yum install -y libX11 libXcomposite libXcursor libXdamage libXext libXi libXtst cups-libs

# Install missing dependencies (Ubuntu/Debian)  
apt-get install -y libnss3-dev libatk-bridge2.0-0 libdrm2 libxkbcomposite1 libxss1 libasound2
```

### Permission denied errors
```bash
# Fix ownership
chown -R wp-templates.metanow_r6s2v1oe7wr:psacln /var/www/vhosts/wp-templates.metanow.dev/httpdocs/node_modules
chown -R wp-templates.metanow_r6s2v1oe7wr:psacln /var/www/vhosts/wp-templates.metanow.dev/httpdocs/package*.json
```

### Screenshots are blank or fail
```bash
# Check Chrome executable path in .env
CHROME_BINARY_PATH=/usr/bin/google-chrome-stable
PUPPETEER_EXECUTABLE_PATH=/usr/bin/google-chrome-stable

# Test Chrome manually
google-chrome-stable --headless --disable-gpu --screenshot https://example.com
```

### Memory issues
```bash
# Add to .env for lower memory usage
SCREENSHOT_TIMEOUT=60000
PUPPETEER_ARGS="--no-sandbox,--disable-setuid-sandbox,--disable-dev-shm-usage"
```

## Environment Configuration

Add these to your `.env` file if not already present:

```env
# Screenshot System Configuration
CHROME_BINARY_PATH=/usr/bin/google-chrome-stable
NODE_BINARY_PATH=/usr/bin/node
PUPPETEER_EXECUTABLE_PATH=/usr/bin/google-chrome-stable
PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true

# Screenshot settings
SCREENSHOT_TIMEOUT=30000
SCREENSHOT_WAIT_FOR_NETWORK_IDLE=true
SCREENSHOT_DELAY=700
```

## Usage Commands

```bash
# Generate all screenshots
php artisan templates:screenshot

# Force regenerate (ignore existing)
php artisan templates:screenshot --force

# Limit number processed
php artisan templates:screenshot --limit=5

# Full page screenshots
php artisan templates:screenshot --fullpage

# Custom dimensions
php artisan templates:screenshot --w=1920 --h=1080

# Specific template
php artisan templates:screenshot --slug=template-name
```