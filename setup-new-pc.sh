#!/bin/bash

# WordPress Templates Catalog - New PC Setup Script
# This script automates the installation process on a new development machine

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Helper functions
print_step() {
    echo -e "${BLUE}==>${NC} $1"
}

print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}!${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

# Check if Docker is installed
check_docker() {
    if ! command -v docker &> /dev/null; then
        print_error "Docker is not installed!"
        echo "Please install Docker Desktop from: https://www.docker.com/products/docker-desktop"
        exit 1
    fi

    if ! docker ps &> /dev/null; then
        print_error "Docker is not running!"
        echo "Please start Docker Desktop and try again."
        exit 1
    fi

    print_success "Docker is installed and running"
}

# Main setup process
echo ""
echo "╔════════════════════════════════════════════════════════════╗"
echo "║   WordPress Templates Catalog - New PC Setup              ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

# Step 0: Check prerequisites
print_step "Checking prerequisites..."
check_docker

# Step 1: Install Composer dependencies
print_step "Installing PHP dependencies via Docker..."
if [ -d "vendor" ] && [ -f "vendor/autoload.php" ]; then
    print_warning "vendor/ directory exists. Reinstalling..."
    rm -rf vendor
fi

docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs

print_success "PHP dependencies installed"

# Step 2: Setup environment file
print_step "Setting up environment configuration..."
if [ ! -f .env ]; then
    cp .env.example .env
    print_success "Created .env file from .env.example"
else
    print_warning ".env file already exists, keeping existing configuration"
fi

# Step 3: Start Docker containers
print_step "Starting Docker containers (this may take a few minutes on first run)..."
./vendor/bin/sail up -d

print_success "Docker containers started"

# Step 4: Wait for services to be ready
print_step "Waiting for services to be ready..."
echo "   This usually takes about 10-15 seconds..."
sleep 15

# Check if MySQL is ready
max_attempts=30
attempt=0
while ! ./vendor/bin/sail exec mysql mysqladmin ping -h"localhost" --silent &> /dev/null; do
    attempt=$((attempt + 1))
    if [ $attempt -ge $max_attempts ]; then
        print_error "MySQL failed to start after $max_attempts attempts"
        exit 1
    fi
    echo -n "."
    sleep 1
done
echo ""
print_success "MySQL is ready"

# Step 5: Generate application key
print_step "Generating application key..."
./vendor/bin/sail artisan key:generate --force
print_success "Application key generated"

# Step 6: Run database migrations
print_step "Running database migrations..."
./vendor/bin/sail artisan migrate --force
print_success "Database migrations completed"

# Step 7: Install Node dependencies
print_step "Installing Node.js dependencies..."
if [ -d "node_modules" ]; then
    print_warning "node_modules/ directory exists. Reinstalling..."
    rm -rf node_modules
fi

./vendor/bin/sail npm install
print_success "Node.js dependencies installed"

# Step 8: Build frontend assets
print_step "Building frontend assets..."
./vendor/bin/sail npm run build
print_success "Frontend assets built"

# Step 9: Create storage link
print_step "Creating storage symbolic link..."
./vendor/bin/sail artisan storage:link
print_success "Storage link created"

# Step 10: Clear and cache configuration
print_step "Clearing caches..."
./vendor/bin/sail artisan cache:clear > /dev/null 2>&1 || true
./vendor/bin/sail artisan config:clear > /dev/null 2>&1 || true
./vendor/bin/sail artisan view:clear > /dev/null 2>&1 || true
print_success "Caches cleared"

# Step 11: Set proper permissions (Linux/Mac only)
if [[ "$OSTYPE" != "msys" ]] && [[ "$OSTYPE" != "win32" ]]; then
    print_step "Setting proper permissions..."
    chmod -R 775 storage bootstrap/cache
    print_success "Permissions set"
fi

# Final verification
echo ""
print_step "Running final verification..."

# Check if app is accessible
if curl -s http://localhost > /dev/null 2>&1; then
    print_success "Application is accessible at http://localhost"
else
    print_warning "Could not verify application accessibility (may need a moment to start)"
fi

# Check database connection
if ./vendor/bin/sail artisan migrate:status > /dev/null 2>&1; then
    print_success "Database connection working"
else
    print_warning "Could not verify database connection"
fi

# Success message
echo ""
echo "╔════════════════════════════════════════════════════════════╗"
echo "║                   ✓ Setup Complete!                       ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""
echo "🌐 Your application is running at: ${GREEN}http://localhost${NC}"
echo ""
echo "📚 Next steps:"
echo "   1. Visit http://localhost in your browser"
echo "   2. Run: ${BLUE}./vendor/bin/sail artisan templates:scan${NC}"
echo "   3. Run: ${BLUE}./vendor/bin/sail artisan templates:screenshot --force${NC}"
echo ""
echo "💡 Useful commands:"
echo "   ${BLUE}./vendor/bin/sail up -d${NC}       # Start containers"
echo "   ${BLUE}./vendor/bin/sail down${NC}         # Stop containers"
echo "   ${BLUE}./vendor/bin/sail logs${NC}         # View logs"
echo "   ${BLUE}./vendor/bin/sail artisan${NC}      # Run artisan commands"
echo "   ${BLUE}./vendor/bin/sail npm run dev${NC}  # Watch assets (development)"
echo ""
echo "📖 Documentation:"
echo "   - README.md - General documentation"
echo "   - SETUP_NEW_PC.md - Detailed setup guide"
echo "   - MAINTENANCE.md - Maintenance and troubleshooting"
echo ""
echo "💾 To import existing database:"
echo "   ${BLUE}./vendor/bin/sail mysql -u sail -ppassword laravel < database_backup.sql${NC}"
echo ""
