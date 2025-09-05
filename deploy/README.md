# WordPress Templates Catalog - Deployment Guide

This directory contains all the files needed for automatic deployment to a Plesk server.

## 🚀 Quick Deployment Steps

### 1. Server Preparation

Run the setup script on your server:

```bash
# SSH into your Plesk server
ssh your-user@your-server.com

# Download and run the setup script
curl -fsSL https://raw.githubusercontent.com/your-org/wordpress-templates/main/deploy/server-setup.sh -o server-setup.sh
chmod +x server-setup.sh
./server-setup.sh your-domain.com
```

### 2. Database Setup

In Plesk control panel:
1. Go to **Databases**
2. Create database: `wordpress_templates`
3. Create user with full privileges
4. Note the credentials for your `.env` file

### 3. Environment Configuration

1. Copy `deploy/production.env.example` to your server as `.env`
2. Update all the placeholders with your actual values
3. Generate app key: `php artisan key:generate`

### 4. Plesk Configuration

#### Domain Settings
- **Document Root**: `/var/www/vhosts/your-domain.com/httpdocs`
- **PHP Version**: 8.2 or higher

#### Nginx Additional Directives
Copy the content from `deploy/nginx-config.conf` to:
**Plesk > Domains > your-domain.com > Apache & nginx Settings > Additional nginx directives**

#### Scheduled Tasks
Add the cron jobs from `deploy/plesk-cron-jobs.txt` in:
**Plesk > Tools & Settings > Scheduled Tasks**

### 5. GitHub Secrets

Add these secrets to your GitHub repository (**Settings > Secrets and variables > Actions**):

| Secret Name | Value | Description |
|-------------|-------|-------------|
| `PLESK_HOST` | `123.456.789.10` | Your server IP address |
| `PLESK_USERNAME` | `your-ssh-user` | SSH username |
| `PLESK_SSH_KEY` | `-----BEGIN PRIVATE KEY-----...` | Your private SSH key |
| `PLESK_SSH_PORT` | `22` | SSH port (default: 22) |
| `DOMAIN_NAME` | `your-domain.com` | Your domain name |

### 6. First Deployment

Push to the `main` branch to trigger automatic deployment:

```bash
git add .
git commit -m "Initial deployment setup"
git push origin main
```

Monitor the deployment in **GitHub > Actions**.

---

## 📁 File Descriptions

| File | Purpose |
|------|---------|
| `deploy.yml` | GitHub Actions workflow for automatic deployment |
| `server-setup.sh` | Server preparation script (installs dependencies, creates directories) |
| `nginx-config.conf` | Nginx configuration for WordPress site routing |
| `production.env.example` | Production environment variables template |
| `plesk-cron-jobs.txt` | Scheduled tasks for template scanning and screenshots |

---

## 🔧 Manual Deployment (Alternative)

If you prefer manual deployment:

1. **Upload files**:
   ```bash
   rsync -avz --exclude='.git' --exclude='node_modules' \
     ./ user@server:/var/www/vhosts/domain.com/httpdocs/
   ```

2. **Install dependencies**:
   ```bash
   cd /var/www/vhosts/domain.com/httpdocs
   composer install --no-dev --optimize-autoloader
   npm ci --production
   npm run build
   ```

3. **Laravel setup**:
   ```bash
   php artisan key:generate
   php artisan config:cache
   php artisan route:cache
   php artisan migrate --force
   php artisan storage:link
   ```

---

## 🧪 Testing Deployment

After deployment, test these URLs:

- **Main app**: `https://your-domain.com/en/templates`
- **German version**: `https://your-domain.com/de/vorlagen`
- **Test WordPress**: `https://your-domain.com/test-wp/`
- **API health**: `https://your-domain.com/api/templates` (should show JSON)

Test commands:

```bash
cd /var/www/vhosts/your-domain.com/httpdocs

# Test template scanning
php artisan templates:scan -v

# Test screenshot generation
php artisan templates:screenshot --slug=test-wp --force -v

# Check application status
php artisan about
```

---

## 🔍 Monitoring & Troubleshooting

### Log Files

- **Application logs**: `/var/www/vhosts/domain.com/httpdocs/storage/logs/`
- **Deployment logs**: GitHub Actions tab
- **Plesk scheduled tasks**: Plesk > Tools & Settings > Logs
- **Nginx logs**: `/var/www/vhosts/domain.com/logs/`

### Common Issues

| Issue | Solution |
|-------|----------|
| **Screenshots fail** | Check Chrome installation: `/usr/bin/google-chrome --version` |
| **Permission errors** | Run: `chmod -R 775 storage bootstrap/cache` |
| **Memory errors** | Increase PHP memory limit to 512M |
| **Nginx 404 errors** | Verify nginx configuration and restart |

### Health Checks

```bash
# Test Chrome installation
/usr/bin/google-chrome --headless --dump-dom https://example.com

# Test WordPress routing
curl -I https://your-domain.com/test-wp/

# Test Laravel application
curl -f https://your-domain.com/en/templates
```

---

## 🔄 Updating

Updates are automatic via GitHub Actions when you push to `main`. Manual update:

```bash
cd /var/www/vhosts/your-domain.com/httpdocs
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci --production && npm run build
php artisan config:cache
php artisan migrate --force
```

---

## 🚨 Rollback

If deployment fails, the system automatically creates backups:

```bash
# List backups
ls -la /var/www/vhosts/your-domain.com/backups/

# Rollback to previous version
BACKUP_DIR="/var/www/vhosts/your-domain.com/backups/20240101_120000"
APP_DIR="/var/www/vhosts/your-domain.com/httpdocs"

# Backup current (failed) deployment
mv "$APP_DIR" "$APP_DIR.failed.$(date +%Y%m%d_%H%M%S)"

# Restore from backup
cp -r "$BACKUP_DIR" "$APP_DIR"

# Set permissions
chown -R www-data:www-data "$APP_DIR"
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
```

---

## 📞 Support

- **Application issues**: Check `storage/logs/laravel.log`
- **Deployment issues**: Check GitHub Actions logs
- **Server issues**: Check Plesk logs and system logs
- **WordPress routing**: Verify nginx configuration

For detailed troubleshooting, see the main `README.md` file.