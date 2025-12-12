# Setting Up WordPress Templates on a New PC

This guide walks you through installing the WordPress Templates Catalog application on a new PC using Docker/Laravel Sail.

## Prerequisites

Before you begin, ensure the new PC has:
- **Docker Desktop** installed and running
- **Git** (optional, for cloning)
- **WSL2** (if on Windows)
- At least **4GB RAM** available for Docker
- At least **10GB disk space**

---

## Quick Start (5 Steps)

```bash
# 1. Copy project files to new PC
# 2. Install dependencies
# 3. Configure environment
# 4. Start Docker containers
# 5. Import database (optional)
```

---

## Detailed Installation Steps

### Step 1: Transfer Project Files

You have several options to transfer the project:

#### Option A: Using Git (Recommended)
```bash
# On new PC
git clone <repository-url>
cd wordpress-templates
```

#### Option B: Direct Copy
```bash
# On current PC - Create archive (exclude large folders)
tar -czf wordpress-templates.tar.gz \
  --exclude=node_modules \
  --exclude=vendor \
  --exclude=storage/logs/* \
  --exclude=storage/framework/cache/* \
  --exclude=storage/framework/sessions/* \
  --exclude=storage/framework/views/* \
  wordpress-templates/

# Transfer file to new PC (USB, network share, etc.)

# On new PC - Extract
tar -xzf wordpress-templates.tar.gz
cd wordpress-templates
```

#### Option C: Zip/Copy Entire Folder
```bash
# Simply copy the entire project folder via:
# - USB drive
# - Network share
# - Cloud storage (Dropbox, Google Drive, etc.)
```

**Important**: Even if you copy `node_modules` and `vendor`, it's recommended to reinstall them on the new PC.

---

### Step 2: Install Docker Desktop

If Docker is not already installed:

#### Windows
1. Download Docker Desktop from https://www.docker.com/products/docker-desktop
2. Install and restart PC
3. Enable WSL2 integration
4. Verify: Open terminal and run:
   ```bash
   docker --version
   docker compose version
   ```

#### Mac
1. Download Docker Desktop for Mac
2. Install and start Docker
3. Verify:
   ```bash
   docker --version
   docker compose version
   ```

#### Linux
```bash
# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Install Docker Compose
sudo apt-get install docker-compose-plugin

# Add user to docker group
sudo usermod -aG docker $USER
newgrp docker

# Verify
docker --version
docker compose version
```

---

### Step 3: Install PHP Dependencies (via Sail)

You don't need PHP installed locally! Laravel Sail will handle this through Docker.

```bash
# Navigate to project directory
cd wordpress-templates

# Install Composer dependencies using Docker
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs
```

**Windows (PowerShell):**
```powershell
docker run --rm `
    -v "${PWD}:/var/www/html" `
    -w /var/www/html `
    laravelsail/php83-composer:latest `
    composer install --ignore-platform-reqs
```

This command:
- Downloads a temporary Docker container with PHP 8.3 and Composer
- Runs `composer install` inside the container
- Installs all PHP dependencies including Laravel Sail
- Removes the temporary container when done

---

### Step 4: Configure Environment

#### 4.1 Copy Environment File
```bash
# Copy the example environment file
cp .env.example .env

# Or if you have your existing .env from old PC
# Copy it over (but may need adjustments)
```

#### 4.2 Edit `.env` for Local Development

Open `.env` and ensure these settings for local development:

```env
APP_NAME="WordPress Templates Catalog"
APP_ENV=local
APP_KEY=  # Will be generated in next step
APP_DEBUG=true
APP_URL=http://localhost

# Database (Sail MySQL Container)
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password

# Templates Configuration - Local fixtures
TEMPLATES_ROOT=/var/www/html/storage/fixtures/templates
DEMO_URL_PATTERN=http://localhost/{slug}/

# Screenshot System (will be handled by container)
CHROME_BINARY_PATH=/usr/bin/google-chrome-stable
NODE_BINARY_PATH=/usr/bin/node

# Screenshot settings
SCREENSHOT_TIMEOUT=30000
SCREENSHOT_DELAY=700
SCREENSHOT_MEMORY_LIMIT_MB=256
SCREENSHOT_DELAY_BETWEEN_MS=3000

# API Token
API_TOKEN=local_development_token_123

# Cache & Session
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# Mail (logs to file in development)
MAIL_MAILER=log
```

**Important Notes:**
- `DB_HOST=mysql` is correct (Docker service name)
- `TEMPLATES_ROOT=/var/www/html/storage/fixtures/templates` is the path INSIDE the Docker container
- Don't use Windows paths like `C:\Users\...`

---

### Step 5: Start Docker Containers

```bash
# Start all containers (MySQL, Redis, MailHog, etc.)
./vendor/bin/sail up -d

# The first time will take 5-10 minutes to download images
# Subsequent starts will be much faster
```

**What this does:**
- Downloads Docker images (Laravel, MySQL, Redis, etc.)
- Creates containers
- Starts MySQL database
- Starts web server on http://localhost

**Verify containers are running:**
```bash
./vendor/bin/sail ps

# You should see:
# - laravel.test (your app)
# - mysql
# - redis (optional)
# - mailhog (optional)
```

---

### Step 6: Generate Application Key

```bash
# Generate Laravel encryption key
./vendor/bin/sail artisan key:generate

# This updates APP_KEY in your .env file
```

---

### Step 7: Run Database Migrations

#### Option A: Fresh Database (Empty Start)
```bash
# Create database tables
./vendor/bin/sail artisan migrate

# You now have an empty database with proper structure
```

#### Option B: Import Existing Database

If you want to bring your existing data from the old PC:

**On OLD PC:**
```bash
# Export database
./vendor/bin/sail mysql -u sail -p laravel > database_backup.sql
# Password: password

# Or using mysqldump
./vendor/bin/sail exec mysql mysqldump -u sail -ppassword laravel > database_backup.sql
```

**Transfer `database_backup.sql` to NEW PC**

**On NEW PC:**
```bash
# First run migrations to create tables
./vendor/bin/sail artisan migrate

# Then import data
./vendor/bin/sail mysql -u sail -p laravel < database_backup.sql
# Password: password

# Or:
cat database_backup.sql | ./vendor/bin/sail mysql -u sail -ppassword laravel
```

---

### Step 8: Install Node Dependencies & Build Assets

```bash
# Install npm packages
./vendor/bin/sail npm install

# Build frontend assets for development
./vendor/bin/sail npm run dev

# Or for production build:
./vendor/bin/sail npm run build
```

---

### Step 9: Create Storage Link

```bash
# Create symbolic link for public storage
./vendor/bin/sail artisan storage:link
```

---

### Step 10: Set Up Test Data (Optional)

If you want to test with sample WordPress templates:

```bash
# Scan for templates in fixtures directory
./vendor/bin/sail artisan templates:scan

# Generate screenshots
./vendor/bin/sail artisan templates:screenshot --force
```

---

## Verification Checklist

Verify everything is working:

```bash
# ✅ Check containers are running
./vendor/bin/sail ps

# ✅ Check database connection
./vendor/bin/sail artisan migrate:status

# ✅ Check application is accessible
curl http://localhost
# Or open in browser: http://localhost

# ✅ Check Livewire routes
./vendor/bin/sail artisan route:list

# ✅ Check logs for errors
./vendor/bin/sail logs

# ✅ Check storage permissions
ls -la storage/
```

**Expected Results:**
- All containers show "Up" status
- No migration errors
- Homepage loads successfully
- No permission errors in logs

---

## Common Issues & Solutions

### Issue 1: Port Already in Use

**Error:** `Bind for 0.0.0.0:80 failed: port is already allocated`

**Solution:** Change ports in `docker-compose.yml`:
```yaml
services:
  laravel.test:
    ports:
      - '8080:80'  # Change from 80 to 8080
```

Then access at http://localhost:8080

### Issue 2: Permission Errors (Linux/Mac)

**Error:** Permission denied errors in storage/

**Solution:**
```bash
# Fix permissions
chmod -R 775 storage bootstrap/cache
chown -R $USER:$USER storage bootstrap/cache

# Or using Sail
./vendor/bin/sail artisan cache:clear
./vendor/bin/sail artisan config:clear
```

### Issue 3: Docker Containers Won't Start

**Solution:**
```bash
# Stop all containers
./vendor/bin/sail down

# Remove volumes (WARNING: deletes database)
./vendor/bin/sail down -v

# Rebuild and start
./vendor/bin/sail build --no-cache
./vendor/bin/sail up -d
```

### Issue 4: composer install fails

**Error:** Dependencies could not be resolved

**Solution:** Use the Docker method (don't install on host):
```bash
docker run --rm \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs
```

### Issue 5: npm install fails

**Solution:** Run inside container:
```bash
./vendor/bin/sail npm install
```

### Issue 6: Database Connection Refused

**Error:** SQLSTATE[HY000] [2002] Connection refused

**Solution:** Verify `.env` database settings:
```env
DB_HOST=mysql  # NOT localhost or 127.0.0.1
DB_PORT=3306
```

### Issue 7: Screenshots Don't Work

**Error:** Chrome/Chromium not found

**Solution:** Install in container:
```bash
./vendor/bin/sail bash

# Inside container
apt-get update
apt-get install -y chromium-browser

# Or modify Dockerfile to include Chrome
```

---

## Quick Reference Commands

```bash
# Start containers
./vendor/bin/sail up -d

# Stop containers
./vendor/bin/sail down

# View logs
./vendor/bin/sail logs
./vendor/bin/sail logs -f  # Follow logs

# Access container shell
./vendor/bin/sail bash

# Run artisan commands
./vendor/bin/sail artisan [command]

# Run npm commands
./vendor/bin/sail npm [command]

# Access MySQL
./vendor/bin/sail mysql

# Run tests
./vendor/bin/sail test

# Clear cache
./vendor/bin/sail artisan cache:clear
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan view:clear
```

---

## Create Sail Alias (Optional but Recommended)

Instead of typing `./vendor/bin/sail` every time:

### Linux/Mac:
```bash
# Add to ~/.bashrc or ~/.zshrc
echo 'alias sail="./vendor/bin/sail"' >> ~/.bashrc
source ~/.bashrc

# Now you can use:
sail up -d
sail artisan migrate
sail npm run dev
```

### Windows (PowerShell):
```powershell
# Add to PowerShell profile
notepad $PROFILE

# Add this line:
Set-Alias sail ./vendor/bin/sail.ps1

# Save and reload:
. $PROFILE
```

---

## File/Folder Transfer Checklist

When transferring from old PC to new PC, you need:

### ✅ Required Files:
- All application code (`app/`, `config/`, `routes/`, etc.)
- `.env` file (or create from `.env.example`)
- `composer.json` and `composer.lock`
- `package.json` and `package-lock.json`
- `database/migrations/`
- `resources/` (views, CSS, JS)

### ✅ Optional (Can Be Regenerated):
- `vendor/` (run `composer install`)
- `node_modules/` (run `npm install`)
- `bootstrap/cache/*` (auto-generated)
- `storage/framework/cache/*` (auto-generated)
- `storage/framework/sessions/*` (auto-generated)
- `storage/framework/views/*` (auto-generated)
- `storage/logs/*` (log files)

### ✅ Data to Transfer (if needed):
- Database export (`database_backup.sql`)
- `storage/app/public/screenshots/` (if you want existing screenshots)
- `storage/fixtures/templates/` (test WordPress installations)

### ❌ Don't Need:
- `.git/` (if using Git, clone fresh)
- `.idea/`, `.vscode/` (IDE settings)
- `*.log` files

---

## Automated Setup Script

Create `setup-new-pc.sh` for quick setup:

```bash
#!/bin/bash

echo "🚀 Setting up WordPress Templates on new PC..."

# 1. Install Composer dependencies
echo "📦 Installing PHP dependencies..."
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs

# 2. Copy environment file
if [ ! -f .env ]; then
    echo "📝 Creating .env file..."
    cp .env.example .env
fi

# 3. Start Docker containers
echo "🐳 Starting Docker containers..."
./vendor/bin/sail up -d

# Wait for containers to be ready
echo "⏳ Waiting for containers to start..."
sleep 10

# 4. Generate app key
echo "🔑 Generating application key..."
./vendor/bin/sail artisan key:generate

# 5. Run migrations
echo "🗄️ Running database migrations..."
./vendor/bin/sail artisan migrate

# 6. Install Node dependencies
echo "📦 Installing Node dependencies..."
./vendor/bin/sail npm install

# 7. Build assets
echo "🎨 Building frontend assets..."
./vendor/bin/sail npm run build

# 8. Create storage link
echo "🔗 Creating storage link..."
./vendor/bin/sail artisan storage:link

# 9. Clear caches
echo "🧹 Clearing caches..."
./vendor/bin/sail artisan cache:clear
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan view:clear

echo "✅ Setup complete!"
echo ""
echo "🌐 Application is running at: http://localhost"
echo ""
echo "Useful commands:"
echo "  ./vendor/bin/sail up -d     # Start containers"
echo "  ./vendor/bin/sail down      # Stop containers"
echo "  ./vendor/bin/sail logs      # View logs"
echo "  ./vendor/bin/sail artisan   # Run artisan commands"
```

Make it executable and run:
```bash
chmod +x setup-new-pc.sh
./setup-new-pc.sh
```

---

## Complete Setup Example

Here's a complete terminal session example:

```bash
# 1. Navigate to project
cd wordpress-templates

# 2. Install dependencies
docker run --rm \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs

# 3. Setup environment
cp .env.example .env
# Edit .env if needed

# 4. Start Docker
./vendor/bin/sail up -d

# 5. Generate key
./vendor/bin/sail artisan key:generate

# 6. Setup database
./vendor/bin/sail artisan migrate

# 7. Install frontend
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev

# 8. Create storage link
./vendor/bin/sail artisan storage:link

# 9. Open browser
# Visit: http://localhost
```

---

## Next Steps After Installation

1. **Verify Installation**: Open http://localhost in browser
2. **Scan Templates**: `./vendor/bin/sail artisan templates:scan`
3. **Generate Screenshots**: `./vendor/bin/sail artisan templates:screenshot --force`
4. **Check Livewire**: Navigate to `/en/templates` or `/de/vorlagen`

---

## Need Help?

- Check [README.md](README.md) for general documentation
- Check [MAINTENANCE.md](MAINTENANCE.md) for maintenance tasks
- Check Laravel Sail docs: https://laravel.com/docs/sail
- Check Docker Desktop docs: https://docs.docker.com/desktop/

---

## Summary

**TL;DR - Minimum Steps:**
```bash
# 1. Copy project to new PC
# 2. Install Composer dependencies via Docker
docker run --rm -v "$(pwd):/var/www/html" -w /var/www/html \
    laravelsail/php83-composer:latest composer install --ignore-platform-reqs

# 3. Setup environment
cp .env.example .env

# 4. Start everything
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm install && ./vendor/bin/sail npm run dev
./vendor/bin/sail artisan storage:link

# 5. Visit http://localhost
```

That's it! 🎉
