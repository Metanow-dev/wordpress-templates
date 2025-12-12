# WordPress Templates Catalog - New PC Setup Script (Windows PowerShell)
# This script automates the installation process on a new Windows development machine

$ErrorActionPreference = "Stop"

# Helper functions
function Print-Step {
    param([string]$message)
    Write-Host "==> $message" -ForegroundColor Blue
}

function Print-Success {
    param([string]$message)
    Write-Host "✓ $message" -ForegroundColor Green
}

function Print-Warning {
    param([string]$message)
    Write-Host "! $message" -ForegroundColor Yellow
}

function Print-Error {
    param([string]$message)
    Write-Host "✗ $message" -ForegroundColor Red
}

# Check if Docker is installed and running
function Check-Docker {
    try {
        $null = docker --version
        $null = docker ps
        Print-Success "Docker is installed and running"
        return $true
    }
    catch {
        Print-Error "Docker is not installed or not running!"
        Write-Host "Please install Docker Desktop from: https://www.docker.com/products/docker-desktop"
        Write-Host "Make sure Docker Desktop is running and try again."
        exit 1
    }
}

# Main setup process
Write-Host ""
Write-Host "╔════════════════════════════════════════════════════════════╗"
Write-Host "║   WordPress Templates Catalog - New PC Setup              ║"
Write-Host "╚════════════════════════════════════════════════════════════╝"
Write-Host ""

# Step 0: Check prerequisites
Print-Step "Checking prerequisites..."
Check-Docker

# Step 1: Install Composer dependencies
Print-Step "Installing PHP dependencies via Docker..."
if (Test-Path "vendor") {
    Print-Warning "vendor/ directory exists. Reinstalling..."
    Remove-Item -Path "vendor" -Recurse -Force
}

docker run --rm `
    -v "${PWD}:/var/www/html" `
    -w /var/www/html `
    laravelsail/php83-composer:latest `
    composer install --ignore-platform-reqs

Print-Success "PHP dependencies installed"

# Step 2: Setup environment file
Print-Step "Setting up environment configuration..."
if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
    Print-Success "Created .env file from .env.example"
}
else {
    Print-Warning ".env file already exists, keeping existing configuration"
}

# Step 3: Start Docker containers
Print-Step "Starting Docker containers (this may take a few minutes on first run)..."
./vendor/bin/sail up -d

Print-Success "Docker containers started"

# Step 4: Wait for services to be ready
Print-Step "Waiting for services to be ready..."
Write-Host "   This usually takes about 10-15 seconds..."
Start-Sleep -Seconds 15

# Check if MySQL is ready
$maxAttempts = 30
$attempt = 0
$mysqlReady = $false

while (-not $mysqlReady -and $attempt -lt $maxAttempts) {
    try {
        $null = ./vendor/bin/sail exec mysql mysqladmin ping -h"localhost" --silent 2>$null
        $mysqlReady = $true
    }
    catch {
        $attempt++
        Write-Host "." -NoNewline
        Start-Sleep -Seconds 1
    }
}

if (-not $mysqlReady) {
    Write-Host ""
    Print-Error "MySQL failed to start after $maxAttempts attempts"
    exit 1
}

Write-Host ""
Print-Success "MySQL is ready"

# Step 5: Generate application key
Print-Step "Generating application key..."
./vendor/bin/sail artisan key:generate --force
Print-Success "Application key generated"

# Step 6: Run database migrations
Print-Step "Running database migrations..."
./vendor/bin/sail artisan migrate --force
Print-Success "Database migrations completed"

# Step 7: Install Node dependencies
Print-Step "Installing Node.js dependencies..."
if (Test-Path "node_modules") {
    Print-Warning "node_modules/ directory exists. Reinstalling..."
    Remove-Item -Path "node_modules" -Recurse -Force
}

./vendor/bin/sail npm install
Print-Success "Node.js dependencies installed"

# Step 8: Build frontend assets
Print-Step "Building frontend assets..."
./vendor/bin/sail npm run build
Print-Success "Frontend assets built"

# Step 9: Create storage link
Print-Step "Creating storage symbolic link..."
./vendor/bin/sail artisan storage:link
Print-Success "Storage link created"

# Step 10: Clear and cache configuration
Print-Step "Clearing caches..."
try {
    ./vendor/bin/sail artisan cache:clear | Out-Null
    ./vendor/bin/sail artisan config:clear | Out-Null
    ./vendor/bin/sail artisan view:clear | Out-Null
}
catch {
    # Ignore errors
}
Print-Success "Caches cleared"

# Final verification
Write-Host ""
Print-Step "Running final verification..."

# Check if app is accessible
try {
    $response = Invoke-WebRequest -Uri "http://localhost" -UseBasicParsing -TimeoutSec 5
    Print-Success "Application is accessible at http://localhost"
}
catch {
    Print-Warning "Could not verify application accessibility (may need a moment to start)"
}

# Check database connection
try {
    $null = ./vendor/bin/sail artisan migrate:status 2>$null
    Print-Success "Database connection working"
}
catch {
    Print-Warning "Could not verify database connection"
}

# Success message
Write-Host ""
Write-Host "╔════════════════════════════════════════════════════════════╗"
Write-Host "║                   ✓ Setup Complete!                       ║"
Write-Host "╚════════════════════════════════════════════════════════════╝"
Write-Host ""
Write-Host "🌐 Your application is running at: " -NoNewline
Write-Host "http://localhost" -ForegroundColor Green
Write-Host ""
Write-Host "📚 Next steps:"
Write-Host "   1. Visit http://localhost in your browser"
Write-Host "   2. Run: " -NoNewline
Write-Host "./vendor/bin/sail artisan templates:scan" -ForegroundColor Blue
Write-Host "   3. Run: " -NoNewline
Write-Host "./vendor/bin/sail artisan templates:screenshot --force" -ForegroundColor Blue
Write-Host ""
Write-Host "💡 Useful commands:"
Write-Host "   ./vendor/bin/sail up -d" -ForegroundColor Blue -NoNewline
Write-Host "       # Start containers"
Write-Host "   ./vendor/bin/sail down" -ForegroundColor Blue -NoNewline
Write-Host "         # Stop containers"
Write-Host "   ./vendor/bin/sail logs" -ForegroundColor Blue -NoNewline
Write-Host "         # View logs"
Write-Host "   ./vendor/bin/sail artisan" -ForegroundColor Blue -NoNewline
Write-Host "      # Run artisan commands"
Write-Host "   ./vendor/bin/sail npm run dev" -ForegroundColor Blue -NoNewline
Write-Host "  # Watch assets (development)"
Write-Host ""
Write-Host "📖 Documentation:"
Write-Host "   - README.md - General documentation"
Write-Host "   - SETUP_NEW_PC.md - Detailed setup guide"
Write-Host "   - MAINTENANCE.md - Maintenance and troubleshooting"
Write-Host ""
Write-Host "💾 To import existing database:"
Write-Host "   Get-Content database_backup.sql | ./vendor/bin/sail mysql -u sail -ppassword laravel" -ForegroundColor Blue
Write-Host ""
