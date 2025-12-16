# Deployment Guide for megasandboxs.com

This guide explains the deployment process and how to handle common deployment issues.

## Automatic Deployment via GitHub Actions

The project uses GitHub Actions for automatic deployment when code is pushed to the `main` branch.

### Required GitHub Secrets

Make sure these secrets are configured in your GitHub repository (Settings > Secrets and variables > Actions):

| Secret Name | Value | Description |
|-------------|-------|-------------|
| `PLESK_HOST` | Server IP address | Your Plesk server IP |
| `PLESK_USERNAME` | `megasandboxs.com_c9h1nddlyw` | SSH username (Plesk domain user) |
| `PLESK_USER` | `megasandboxs.com_c9h1nddlyw` | Plesk domain user for file ownership |
| `PLESK_SSH_KEY` | `-----BEGIN PRIVATE KEY-----...` | Private SSH key for authentication |
| `PLESK_SSH_PORT` | `22` | SSH port (default: 22) |
| `DOMAIN_NAME` | `megasandboxs.com` | Your domain name |

### Deployment Process

When you push to `main`:

1. **Build Phase** (GitHub):
   - Installs PHP and Node.js dependencies
   - Builds frontend assets with Vite
   - Creates deployment archive

2. **Deploy Phase** (Server):
   - Extracts archive to `/var/www/vhosts/megasandboxs.com/httpdocs`
   - Creates Laravel directory structure
   - Sets proper ownership (`megasandboxs.com_c9h1nddlyw:psacln`)
   - Sets permissions (775 for storage/cache, 755/644 for others)
   - Runs Laravel setup commands (migrations, caching, etc.)
   - **Runs comprehensive fix script** (`deploy/fix-deployment-megasandboxs.sh`)
   - Performs health checks

3. **Post-Deploy Phase** (Server):
   - Scans WordPress templates
   - Updates screenshots
   - Cleans up old backups

### What the Comprehensive Fix Script Does

The `deploy/fix-deployment-megasandboxs.sh` script automatically fixes:

1. **Root .htaccess** - Creates/updates the root .htaccess with rewrite rules for:
   - `/build/*` → `public/build/*` (Laravel assets)
   - `/img/*` → `public/img/*` (images)
   - `/vendor/*` → `public/vendor/*` (Livewire assets)
   - `/storage/*` → `public/storage/*` (storage symlink)
   - WordPress sites routing (existing files/directories)
   - Laravel routing (everything else)

2. **Public .htaccess** - Creates Laravel's public directory .htaccess

3. **File Ownership** - Fixes ownership of all Laravel files to correct Plesk user

4. **Permissions** - Sets proper permissions on storage, cache, and files

5. **Laravel Configuration** - Clears and rebuilds all Laravel caches

6. **Directory Structure** - Ensures all Laravel directories exist

7. **Health Checks** - Creates test files for verification

## Manual Deployment

If you need to deploy manually:

```bash
# 1. SSH into the server
ssh megasandboxs.com_c9h1nddlyw@your-server-ip

# 2. Navigate to application directory
cd /var/www/vhosts/megasandboxs.com/httpdocs

# 3. Pull latest changes
git pull origin main

# 4. Install dependencies
composer install --no-dev --optimize-autoloader
npm ci --production
npm run build

# 5. Run Laravel commands
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force

# 6. Run comprehensive fix
chmod +x deploy/fix-deployment-megasandboxs.sh
./deploy/fix-deployment-megasandboxs.sh

# 7. Fix permissions (if needed)
chmod +x fix-permissions.sh
./fix-permissions.sh
```

## After Git Pull

After every `git pull`, you should run the fix-permissions script:

```bash
./fix-permissions.sh
```

This script:
- Fixes ownership of all Laravel files (not WordPress sites!)
- Sets proper permissions on storage and cache
- Makes scripts executable
- Fixes root .htaccess and index.php ownership

## Common Issues and Fixes

### Issue: 403 Forbidden Error

**Symptoms**: Site returns 403 Forbidden

**Fix**:
```bash
cd /var/www/vhosts/megasandboxs.com/httpdocs
./deploy/fix-deployment-megasandboxs.sh
```

This will fix ownership, permissions, and .htaccess files.

### Issue: Missing CSS/JS Assets (404 errors)

**Symptoms**: Assets in `/build/`, `/img/`, `/vendor/`, `/storage/` return 404

**Cause**: Root .htaccess is missing or incorrect

**Fix**:
```bash
./deploy/fix-deployment-megasandboxs.sh
```

Or manually verify root .htaccess contains rewrite rules for these paths.

### Issue: Wrong File Ownership

**Symptoms**: Files owned by `1001`, `root`, or other users after git pull

**Fix**:
```bash
./fix-permissions.sh
```

### Issue: WordPress Sites Affected by Deployment

**This should never happen!** The deployment is configured to:
- Only touch Laravel app files
- Never recursively process root httpdocs directory
- Respect .gitignore whitelist protection

If WordPress sites are affected, check:
1. `.gitignore` is committed and uses whitelist approach
2. Never run `git clean -fd` or `git reset --hard` without verifying .gitignore
3. Deployment scripts only process Laravel directories

### Issue: Laravel Configuration Cache Errors

**Fix**:
```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

## Health Check URLs

After deployment, verify these URLs work:

- **Main app**: https://megasandboxs.com/en/templates
- **Health check**: https://megasandboxs.com/health.txt (should show "OK")
- **PHP test**: https://megasandboxs.com/test.php (should show PHP version)

## WordPress Sites Protection

WordPress sites in the httpdocs directory are protected by:

1. **Whitelist .gitignore**: Only Laravel directories are tracked by Git
2. **Selective ownership fixes**: Scripts only touch Laravel files
3. **Non-recursive operations**: WordPress sites are never processed recursively
4. **Root-level isolation**: Each WordPress site is isolated in its own directory

## Deployment Scripts

| Script | Purpose | When to Use |
|--------|---------|-------------|
| `deploy/fix-deployment-megasandboxs.sh` | Comprehensive deployment fix | After deployment, or when 403/404 errors occur |
| `fix-permissions.sh` | Fix Laravel file ownership | After every git pull |
| `deploy/fix-screenshot-permissions.sh` | Fix screenshot generation issues | When screenshots fail |

## Important Notes

1. **Never run destructive Git commands** without checking .gitignore first:
   - ❌ `git clean -fd` - Can delete WordPress sites if .gitignore is wrong!
   - ❌ `git reset --hard` - Resets .gitignore to committed version!
   - See `GIT_SAFETY.md` for safe alternatives

2. **Always commit .gitignore changes immediately** - The whitelist protection must be in Git

3. **DocumentRoot stays as httpdocs** - Never change it to public/ (breaks WordPress sites)

4. **Test after every deployment**:
   - Check Laravel app loads
   - Check assets load (CSS, JS, images)
   - Verify WordPress sites still work

## Troubleshooting Checklist

If something goes wrong after deployment:

- [ ] Run `./deploy/fix-deployment-megasandboxs.sh`
- [ ] Check file ownership: `ls -la index.php .htaccess`
- [ ] Verify root .htaccess exists and has rewrite rules
- [ ] Check Laravel logs: `tail -f storage/logs/laravel.log`
- [ ] Test health check: `curl https://megasandboxs.com/health.txt`
- [ ] Clear Laravel cache: `php artisan config:clear && php artisan cache:clear`
- [ ] Verify storage directory is writable: `ls -la storage/`

## Getting Help

If issues persist:

1. Check Laravel logs: `storage/logs/laravel.log`
2. Check web server logs (ask hosting support)
3. Verify Plesk configuration hasn't changed
4. Test with health check URLs
5. Run comprehensive fix script again

## Monitoring Deployments

To monitor a deployment in progress:

1. Go to GitHub repository
2. Click "Actions" tab
3. Click on the running workflow
4. Watch the deployment logs in real-time
5. Check for any errors or warnings

Deployments typically take 2-5 minutes to complete.
