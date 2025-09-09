# Plesk 403 Error Fix Guide

## Quick Fix (Run on Server)

```bash
# SSH to server and run the automated fix
cd /var/www/vhosts/wp-templates.metanow.dev/httpdocs
./deploy/fix-plesk-403.sh
```

## Manual Plesk Configuration (Required)

### 1. Set Document Root
**Plesk → Websites & Domains → wp-templates.metanow.dev → Hosting Settings**
- Change **Document root** from `httpdocs` to `httpdocs/public`

### 2. Set PHP Version  
**Plesk → Websites & Domains → wp-templates.metanow.dev → PHP Settings**
- Set to: **LSPHP 8.3 (alt-php) FastCGI**

### 3. Configure Apache Directives
**Plesk → Websites & Domains → wp-templates.metanow.dev → Apache & nginx Settings**

Add to **Additional Apache directives** (both HTTP and HTTPS):

```apache
<Directory "/var/www/vhosts/wp-templates.metanow.dev/httpdocs/public">
    AllowOverride All
    Options -Indexes +SymLinksIfOwnerMatch
    Require all granted
</Directory>
DirectoryIndex index.php index.html
```

### 4. Apply Changes
```bash
plesk repair web -y
systemctl restart lshttpd
```

## Test URLs

After configuration:
- **Static test**: https://wp-templates.metanow.dev/health.txt (should show "OK")  
- **PHP test**: https://wp-templates.metanow.dev/test.php (should show PHP info)
- **Laravel app**: https://wp-templates.metanow.dev/ (should load app)

## Troubleshooting

### Still getting 403?
1. Check error logs: `/var/www/vhosts/wp-templates.metanow.dev/logs/error_log`  
2. Verify `.htaccess` file exists in `public/` directory
3. Confirm PHP handler mapping in Plesk

### Static files work but PHP doesn't?
- PHP handler mapping issue - check step 2 above
- Look for "serving as static file" error in logs

### Laravel routes not working?
- `.htaccess` mod_rewrite issue
- Check step 3 (Apache directives)

## What the Automated Script Does

1. ✅ Creates correct `.htaccess` with PHP handler mapping  
2. ✅ Sets proper file permissions and ownership
3. ✅ Creates test files for verification
4. ✅ Provides manual configuration instructions

## The Root Cause

Plesk's LiteSpeed was trying to serve PHP files as static files instead of processing them through PHP. The fix maps `.php` files to the correct CloudLinux alt-php handler.