# Plesk Setup Guide for WordPress Templates

## 1. Directory Structure Setup

After deployment, create the templates directory:

```bash
mkdir -p /var/www/vhosts/wp-templates.metanow.dev/httpdocs/templates
chown www-data:www-data /var/www/vhosts/wp-templates.metanow.dev/httpdocs/templates
chmod 755 /var/www/vhosts/wp-templates.metanow.dev/httpdocs/templates
```

## 2. Workflow for Adding New Templates

### Step 1: Clone from Staging
1. In Plesk, go to **Websites & Domains**
2. Find your staging site (e.g., `staging.metanow.dev`)
3. Click **Clone Domain**
4. Choose **Use existing domain**: `wp-templates.metanow.dev`
5. **Important**: Set path to `templates/your-template-name`
6. Complete the clone

### Step 2: Verify Structure
After cloning, you should have:
```
/var/www/vhosts/wp-templates.metanow.dev/httpdocs/templates/your-template-name/httpdocs/
├── wp-config.php
├── wp-content/
├── index.php
└── ... (WordPress files)
```

### Step 3: Run Scanner
```bash
cd /var/www/vhosts/wp-templates.metanow.dev/httpdocs
php artisan templates:scan
```

The scanner will automatically find the WordPress installation and add it to the catalog.

## 3. Nginx Configuration

Add this to **Plesk → Websites & Domains → wp-templates.metanow.dev → Apache & nginx Settings** in the "Additional nginx directives" section:

```nginx
# WordPress Templates - Direct serving
location ~ ^/([a-zA-Z0-9\-_]+)/?(.*)$ {
    set $template_name $1;
    set $template_path $2;
    
    # Check for httpdocs structure (Plesk clones)
    set $template_root "/var/www/vhosts/wp-templates.metanow.dev/httpdocs/templates/$template_name/httpdocs";
    
    if (-f "$template_root/wp-config.php") {
        root $template_root;
        index index.php index.html;
        
        if ($template_path = "") {
            try_files /index.php /index.html =404;
        }
        if ($template_path != "") {
            try_files /$template_path /$template_path/ /index.php?$args;
        }
        
        location ~ \.php$ {
            try_files $uri =404;
            include fastcgi_params;
            fastcgi_param SCRIPT_FILENAME $template_root$fastcgi_script_name;
            fastcgi_param DOCUMENT_ROOT $template_root;
            fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        }
        
        break;
    }
}

# Security: Block sensitive files
location ~ ^/[^/]+/(wp-config\.php|\.env|\.git) {
    deny all;
    return 404;
}
```

## 4. Environment Configuration

Update your `.env` file with:
```env
TEMPLATES_ROOT=/var/www/vhosts/wp-templates.metanow.dev/httpdocs/templates
DEMO_URL_PATTERN=https://wp-templates.metanow.dev/{slug}/
```

## 5. Cron Jobs Setup

Add these cron jobs in **Plesk → Cron Jobs**:

**Template Scanning (every 15 minutes):**
```
*/15 * * * * cd /var/www/vhosts/wp-templates.metanow.dev/httpdocs && php artisan templates:scan
```

**Screenshot Generation (daily at 2 AM):**
```
0 2 * * * cd /var/www/vhosts/wp-templates.metanow.dev/httpdocs && php artisan templates:screenshot
```

## 6. Testing

### Test the Scanner:
```bash
php artisan templates:scan --limit=3
```

### Test Template Access:
Visit: `https://wp-templates.metanow.dev/your-template-name/`

### Test Screenshot Generation:
```bash
php artisan templates:screenshot --limit=1 --force
```

## 7. Template URLs

After setup, your templates will be accessible at:
- **Catalog**: `https://wp-templates.metanow.dev/en/templates`
- **Template**: `https://wp-templates.metanow.dev/template-name/`
- **Template Admin**: `https://wp-templates.metanow.dev/template-name/wp-admin/`

## Troubleshooting

- **Scanner not finding templates**: Check directory structure and permissions
- **Templates not loading**: Verify Nginx configuration and PHP-FPM settings  
- **Screenshots failing**: Install Chrome and check browser configuration
- **Database errors**: Verify credentials in `.env` file