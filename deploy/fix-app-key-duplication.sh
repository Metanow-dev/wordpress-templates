#!/bin/bash
# Fix APP_KEY duplication that causes 500 errors
# Run this when you get 500 errors after deployment

set -e

APP_PATH="/var/www/vhosts/wp-templates.metanow.dev/httpdocs"

echo "🔑 APP_KEY Duplication Fix"
echo "   This fixes duplicate APP_KEY entries that cause 500 errors"

cd "$APP_PATH"

if [ ! -f .env ]; then
    echo "❌ No .env file found!"
    exit 1
fi

# Count APP_KEY occurrences
APP_KEY_COUNT=$(grep -c "^APP_KEY=" .env || echo "0")

echo "Found $APP_KEY_COUNT APP_KEY entries in .env file"

if [ "$APP_KEY_COUNT" -gt 1 ]; then
    echo "🚨 DUPLICATE APP_KEY entries detected - this causes 500 errors!"
    
    echo "Current APP_KEY entries:"
    grep "^APP_KEY=" .env | nl
    
    # Get the first valid APP_KEY (should be base64 encoded)
    FIRST_VALID_KEY=""
    while IFS= read -r line; do
        if echo "$line" | grep -q "APP_KEY=base64:"; then
            FIRST_VALID_KEY="$line"
            break
        fi
    done < <(grep "^APP_KEY=" .env)
    
    if [ -n "$FIRST_VALID_KEY" ]; then
        echo "📝 Using first valid APP_KEY: $FIRST_VALID_KEY"
        
        # Create backup
        cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
        echo "✅ Created backup: .env.backup.$(date +%Y%m%d_%H%M%S)"
        
        # Remove all APP_KEY lines
        sed -i '/^APP_KEY=/d' .env
        
        # Add back the valid one
        echo "$FIRST_VALID_KEY" >> .env
        
        echo "✅ Fixed APP_KEY duplication - kept valid key"
    else
        echo "⚠️  No valid base64 APP_KEY found, generating new one..."
        
        # Remove all APP_KEY lines
        sed -i '/^APP_KEY=/d' .env
        
        # Detect PHP binary
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
        
        # Generate new key
        $PHP_BIN artisan key:generate --no-interaction
        echo "✅ Generated new APP_KEY"
    fi
    
elif [ "$APP_KEY_COUNT" -eq 0 ]; then
    echo "❌ No APP_KEY found at all!"
    
    # Detect PHP binary
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
    
    echo "🔑 Generating APP_KEY..."
    $PHP_BIN artisan key:generate --no-interaction
    echo "✅ Generated APP_KEY"
    
else
    echo "✅ APP_KEY is properly configured (1 entry found)"
    
    # Check if it's a valid base64 key
    if grep -q "APP_KEY=base64:" .env; then
        echo "✅ APP_KEY is valid base64 encoded"
    else
        echo "⚠️  APP_KEY exists but may not be valid"
        grep "^APP_KEY=" .env
    fi
fi

# Clear config cache to ensure changes take effect
if command -v php >/dev/null 2>&1; then
    echo "🔄 Clearing Laravel config cache..."
    
    # Detect PHP binary
    if [ -x "/opt/alt/php83/usr/bin/php" ]; then
        PHP_BIN="/opt/alt/php83/usr/bin/php"
    elif [ -x "/opt/plesk/php/8.3/bin/php" ]; then
        PHP_BIN="/opt/plesk/php/8.3/bin/php"
    elif [ -x "/opt/plesk/php/8.2/bin/php" ]; then
        PHP_BIN="/opt/plesk/php/8.2/bin/php"
    else
        PHP_BIN="php"
    fi
    
    $PHP_BIN artisan config:clear
    $PHP_BIN artisan config:cache
    echo "✅ Config cache refreshed"
fi

echo ""
echo "🎉 APP_KEY fix completed!"
echo ""
echo "📊 FINAL STATUS:"
FINAL_COUNT=$(grep -c "^APP_KEY=" .env || echo "0")
echo "   APP_KEY entries: $FINAL_COUNT"
if [ "$FINAL_COUNT" -eq 1 ]; then
    echo "   ✅ Perfect! Exactly 1 APP_KEY entry"
    grep "^APP_KEY=" .env | sed 's/APP_KEY=base64:.*$/APP_KEY=base64:[HIDDEN]/'
else
    echo "   ❌ Still incorrect number of APP_KEY entries"
fi

echo ""
echo "🧪 Test your application now - the 500 error should be resolved!"