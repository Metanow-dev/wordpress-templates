Here’s a **starter README.md** tailored for your `wordpress-templates` project. It captures setup, dev workflow, production notes, and key paths/commands — useful both for you and new contributors.

---

````markdown
# WordPress Templates Catalog

A Laravel + Livewire app that indexes and displays WordPress demo sites hosted under  
`https://wordpress.metanow.dev/{slug}/`.

The app scans WordPress installations on disk, stores metadata in MySQL,  
and shows them in a searchable/browsable catalog with English + German UI.

---

## Features

- 🗂️ **Catalog UI** with search, sorting, pagination (`/en/templates` and `/de/vorlagen`)
- 🔄 **Scanner** command (`templates:scan`) to auto-detect WP installs under a root path
- 🖼️ **Theme screenshot detection** (from `wp-content/themes/<theme>/screenshot.png`)
- 🌍 **Bilingual support** (EN/DE routes and labels)
- ⚙️ **API endpoint** for automated registration (`POST /api/import-template`)
- 🚀 **Production ready** with Nginx rule mapping `/slug/` → `/srv/templates/{slug}/public`
- 🧩 Extensible for AI classification (via n8n + LLM integration, planned)

---

## Local Development

### Requirements
- Docker + Docker Compose (via [Laravel Sail](https://laravel.com/docs/sail))
- Node.js + npm (for assets)

### Setup
```bash
# clone repo
git clone git@github.com:<your-org>/wordpress-templates.git
cd wordpress-templates

# install PHP deps
composer install

# bring up containers (PHP + MySQL)
./vendor/bin/sail up -d laravel.test mysql

# generate app key
./vendor/bin/sail php artisan key:generate

# install JS deps
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
````

App runs at [http://localhost](http://localhost).

### Database

Run migrations:

```bash
./vendor/bin/sail php artisan migrate
```

### Scanning fixtures

By default, local templates are read from:

```
storage/fixtures/templates/<slug>/public/wp-config.php
```

Example fixture:

```bash
mkdir -p storage/fixtures/templates/hotel-classic/public/wp-content/themes/minimal
touch storage/fixtures/templates/hotel-classic/public/wp-config.php
touch storage/fixtures/templates/hotel-classic/public/wp-content/themes/minimal/screenshot.png
```

Scan and populate DB:

```bash
./vendor/bin/sail php artisan templates:scan
```

Check in Tinker:

```bash
./vendor/bin/sail php artisan tinker
>>> \App\Models\Template::select('slug','demo_url','screenshot_url')->get()->toArray();
```

---

## Useful Commands

* **Re-scan all templates**
  `./vendor/bin/sail php artisan templates:scan`

* **Inspect DB via Tinker**
  `./vendor/bin/sail php artisan tinker`

* **Clear caches**

  ```bash
  ./vendor/bin/sail php artisan route:clear
  ./vendor/bin/sail php artisan config:clear
  ```

* **Run scheduler (dev)**
  `./vendor/bin/sail php artisan schedule:run`

---

## Routes

* Catalog EN: `http://localhost/en/templates`
* Catalog DE: `http://localhost/de/vorlagen`
* Media proxy (local only): `/media/{slug}/{path}` serves files from fixtures

---

## Project Structure

* `app/Livewire/TemplatesIndex.php` — Livewire component for listing
* `app/Console/Commands/ScanTemplates.php` — scanner command
* `app/Models/Template.php` — Eloquent model
* `config/templates.php` — scanner config (paths, URL pattern)
* `storage/fixtures/templates/` — local fake WP installs for dev
* `routes/web.php` — locale-aware routes
* `routes/api.php` — API endpoints

---

## Production Deployment

### Server Layout

* Laravel app:
  `/var/www/vhosts/wordpress.metanow.dev/httpdocs` → `public/`
* WordPress demos:
  `/srv/templates/<slug>/public`

### Env config (`.env`)

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://wordpress.metanow.dev

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wordpress_templates
DB_USERNAME=...
DB_PASSWORD=...

TEMPLATES_ROOT=/srv/templates
DEMO_URL_PATTERN=https://wordpress.metanow.dev/{slug}/
API_TOKEN=<generate-random-long-secret>
```

### Nginx Rule (Plesk > Additional directives)

```nginx
location ~ ^/([a-zA-Z0-9\-_]+)/?(.*)$ {
    set $wp_root "/srv/templates/$1/public";
    if (-d $wp_root) {
        root $wp_root;
        try_files /$2 /$2/ /index.php?$args;

        location ~ \.php$ {
            try_files $uri =404;
            include fastcgi_params;
            fastcgi_param SCRIPT_FILENAME $wp_root$fastcgi_script_name;
            fastcgi_pass unix:/var/www/vhosts/system/wordpress.metanow.dev/php-fpm.sock;
        }
        break;
    }
}
```

### Cron (Plesk Scheduled Task)

```
* * * * * /opt/plesk/php/8.3/bin/php /var/www/vhosts/wordpress.metanow.dev/httpdocs/artisan schedule:run >> /dev/null 2>&1
```

### Test Demo Mapping

```bash
sudo mkdir -p /srv/templates/demo-wp/public
echo "<?php echo 'OK demo-wp';" | sudo tee /srv/templates/demo-wp/public/index.php
```

Visit `https://wordpress.metanow.dev/demo-wp/` → should show `OK demo-wp`.

---

## API Endpoints

### Import Template

```
POST /api/import-template
Headers:
  X-Api-Token: <API_TOKEN>
Body JSON:
{
  "slug": "hotel-classic",
  "docroot": "/srv/templates/hotel-classic/public",
  "demo_url": "https://wordpress.metanow.dev/hotel-classic/"
}
```

---

## Conventions

* **Slug** = folder name under `/srv/templates/` (used in demo URL and DB)
* **Screenshot** = must exist under `wp-content/themes/<active>/screenshot.png`
* **Scanner** = safe to re-run, will upsert records
* **Languages** = routes `/en/templates` and `/de/vorlagen`; labels from `lang/`

---

## Roadmap

* 🔜 Registrar script to auto-symlink WP docroots and POST to `/api/import-template`
* 🔜 n8n automation for AI classification
* 🔜 Admin UI to review / override AI results

---

## License


