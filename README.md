# WordPress Templates Catalog

A Laravel + Livewire application that indexes and displays WordPress demo sites with automated screenshot generation. The app scans WordPress installations, captures browser screenshots, and presents them in a searchable catalog with bilingual support (EN/DE).

---

## Features

- 🗂️ **Interactive Catalog** with search, filtering by categories/tags, and pagination
- 🔄 **Automated Scanner** to detect WordPress installations and metadata
- 📸 **Screenshot Generation** using Puppeteer/Chrome for live site captures
- 🖼️ **Theme Screenshot Detection** from WordPress themes
- 🌍 **Bilingual Support** with English (`/en/templates`) and German (`/de/vorlagen`) routes
- ⚙️ **RESTful API** for programmatic template management
- 🚀 **Production Ready** with Plesk/cPanel deployment support
- 🎨 **Modern UI** with Tailwind CSS and responsive design

---

## Screenshots & Demo

The application automatically captures screenshots of WordPress sites and displays them in a modern, responsive gallery interface with filtering capabilities.

---

## Local Development

### Requirements
- Docker + Docker Compose (via [Laravel Sail](https://laravel.com/docs/sail))
- Node.js 18+ with npm
- Chrome/Chromium browser (managed automatically by Puppeteer)

### Quick Start

```bash
# Clone repository
git clone <your-repo-url>
cd wordpress-templates

# Install PHP dependencies
composer install

# Install Node.js dependencies (includes Puppeteer)
npm install

# Copy environment file
cp .env.example .env

# Start Docker containers
./vendor/bin/sail up -d

# Generate application key
./vendor/bin/sail artisan key:generate

# Run database migrations
./vendor/bin/sail artisan migrate

# Build frontend assets
./vendor/bin/sail npm run dev
```

Access the application at [http://localhost](http://localhost)

### Setting up Test Data

```bash
# Scan for WordPress installations (uses fixtures by default)
./vendor/bin/sail artisan templates:scan

# Generate screenshots for all templates
./vendor/bin/sail artisan templates:screenshot --force
```

---

## Screenshot System

### How it Works

1. **Theme Screenshots**: First tries to use WordPress theme's built-in screenshot
2. **Browser Capture**: If no theme screenshot exists, uses Puppeteer to capture live site
3. **Automated Processing**: Optimizes images and stores in `storage/app/public/screenshots/`
4. **Smart Updates**: Only re-captures when forced or when sites change

### Screenshot Commands

```bash
# Capture screenshots for all templates
./vendor/bin/sail artisan templates:screenshot

# Force re-capture (ignores existing screenshots)
./vendor/bin/sail artisan templates:screenshot --force

# Capture specific template
./vendor/bin/sail artisan templates:screenshot --slug=template-name

# Full-page screenshots
./vendor/bin/sail artisan templates:screenshot --fullpage

# Custom dimensions
./vendor/bin/sail artisan templates:screenshot --w=1920 --h=1080
```

### Testing Screenshots Locally

For local development without real WordPress sites:

```bash
# Option 1: Start local WordPress container
./vendor/bin/sail up -d wordpress
# WordPress available at http://localhost:8080

# Option 2: Use public websites for testing
./vendor/bin/sail artisan tinker --execute="App\Models\Template::query()->update(['demo_url' => 'https://example.com']);"
```

---

## Production Deployment

### Server Requirements
- PHP 8.2+
- MySQL 8.0+
- Nginx/Apache
- Node.js 18+
- Chrome/Chromium browser
- Sufficient memory for browser automation (2GB+ recommended)

### Plesk Configuration

#### Environment Variables (`.env`)
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=wordpress_templates
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# Templates Configuration
TEMPLATES_ROOT=/var/www/vhosts
DEMO_URL_PATTERN=https://{slug}/

# Screenshot Configuration
CHROME_BINARY_PATH=/usr/bin/google-chrome
NODE_BINARY_PATH=/usr/bin/node

# API Security
API_TOKEN=your-secure-random-token
```

#### Nginx Additional Directives (in Plesk)
```nginx
# Serve WordPress sites directly
location ~ ^/([a-zA-Z0-9\-_]+)/?(.*)$ {
    set $wp_root "/var/www/vhosts/$1/httpdocs";
    if (-d $wp_root) {
        root $wp_root;
        try_files /$2 /$2/ /index.php?$args;
        
        location ~ \.php$ {
            try_files $uri =404;
            include fastcgi_params;
            fastcgi_param SCRIPT_FILENAME $wp_root$fastcgi_script_name;
            fastcgi_pass unix:/var/www/vhosts/system/your-domain.com/php-fpm.sock;
        }
        break;
    }
}
```

#### Scheduled Tasks (Cron)
```bash
# Scan for new WordPress installations every 15 minutes
*/15 * * * * cd /var/www/vhosts/your-domain.com/httpdocs && php artisan templates:scan

# Update screenshots daily at 2 AM
0 2 * * * cd /var/www/vhosts/your-domain.com/httpdocs && php artisan templates:screenshot

# Clean up old files weekly
0 3 * * 0 cd /var/www/vhosts/your-domain.com/httpdocs && php artisan storage:link
```

### Production Workflow

1. **Initial Setup**: Scanner discovers all WordPress installations in your hosting directory
2. **Automated Updates**: Cron jobs regularly check for new sites and update screenshots
3. **Manual Triggers**: Use commands to force updates when needed

```bash
# Production commands
php artisan templates:scan --path=/var/www/vhosts
php artisan templates:screenshot --force
php artisan queue:work # If using queued jobs
```

---

## API Endpoints

### Import Template
```http
POST /api/import-template
X-Api-Token: your-api-token
Content-Type: application/json

{
    "slug": "new-wordpress-site",
    "docroot": "/var/www/vhosts/new-site.com/httpdocs",
    "demo_url": "https://new-site.com"
}
```

### Get Templates
```http
GET /api/templates
X-Api-Token: your-api-token
```

---

## Configuration

### Templates Config (`config/templates.php`)
```php
return [
    'root' => env('TEMPLATES_ROOT', storage_path('fixtures/templates')),
    'demo_url_pattern' => env('DEMO_URL_PATTERN', 'https://your-domain.com/{slug}/'),
    'screenshot_candidates' => [
        'wp-content/themes/*/screenshot.png',
        'wp-content/themes/*/screenshot.jpg',
        'screenshot.png',
    ],
];
```

### Browser Configuration
The system automatically detects Chrome installations. Override if needed:
```env
CHROME_BINARY_PATH=/usr/bin/chromium-browser
NODE_BINARY_PATH=/usr/local/bin/node
```

---

## Key Commands

```bash
# Development
./vendor/bin/sail up -d                    # Start containers
./vendor/bin/sail artisan templates:scan  # Scan for WordPress sites
./vendor/bin/sail artisan templates:screenshot --force  # Generate screenshots
./vendor/bin/sail npm run dev             # Watch frontend assets

# Production
php artisan templates:scan                # Scan for new installations
php artisan templates:screenshot          # Update screenshots
php artisan config:cache                  # Cache configuration
php artisan route:cache                   # Cache routes
```

---

## Project Structure

```
app/
├── Console/Commands/
│   ├── ScanTemplates.php         # WordPress site scanner
│   └── CaptureTemplateScreenshots.php  # Screenshot generator
├── Http/
│   └── Middleware/SetLocale.php  # Language detection
├── Livewire/
│   └── TemplatesIndex.php        # Main catalog component
├── Models/
│   └── Template.php              # Template model
└── Support/
    └── Screenshotter.php         # Screenshot service

config/templates.php              # Scanner configuration
resources/views/livewire/         # Blade templates
storage/fixtures/templates/       # Local test data
routes/web.php                   # Web routes (bilingual)
routes/api.php                   # API routes
```

---

## Troubleshooting

### Screenshot Issues
```bash
# Test screenshot functionality
./vendor/bin/sail artisan templates:screenshot --slug=test-site --force

# Check Chrome installation
./vendor/bin/sail node -e "console.log(require('puppeteer').executablePath())"

# Debug browser issues
./vendor/bin/sail artisan templates:screenshot --slug=test-site --force -vvv
```

### Common Issues
- **Memory errors**: Increase PHP memory limit for screenshot generation
- **Permission issues**: Ensure storage directories are writable
- **Network timeouts**: Adjust timeout settings for slow WordPress sites

---

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

---

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).

---

## Support

For issues and feature requests, please use the [GitHub issue tracker](https://github.com/your-org/wordpress-templates/issues).# Deployment test
🚀 **Deployment Status**: Ready for production deployment to wp-templates.metanow.dev
## Deployment Notes

- Fixed .env file format issues with API_TOKEN
- Database password properly quoted for special characters
- Ready for successful deployment
# .env file manually recreated on server Mon Sep  8 10:06:59 CEST 2025
