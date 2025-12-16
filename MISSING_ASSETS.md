# Missing Assets After Migration

The following image assets were not migrated from the old server and need to be uploaded:

## Required Files

### 1. Hero Background Image
- **Location**: `public/img/background.png`
- **Used in**: Homepage hero section
- **View**: `resources/views/livewire/templates-index.blade.php:6`

### 2. Metanow Logo
- **Location**: `storage/app/public/img/logo/Metanow.webp`
- **Public URL**: `/storage/img/logo/Metanow.webp` (via symlink)
- **Used in**: Header, footer (3 locations)
- **Views**: `resources/views/livewire/templates-index.blade.php:22,873,911`

## Upload Instructions

### Via Command Line (SCP/SFTP)
```bash
# From your local machine:
scp background.png user@megasandboxs.com:/var/www/vhosts/megasandboxs.com/httpdocs/public/img/
scp Metanow.webp user@megasandboxs.com:/var/www/vhosts/megasandboxs.com/httpdocs/storage/app/public/img/logo/

# Then fix permissions on server:
chown megasandboxs.com_c9h1nddlyw:psacln public/img/background.png
chown megasandboxs.com_c9h1nddlyw:psacln storage/app/public/img/logo/Metanow.webp
chmod 644 public/img/background.png
chmod 644 storage/app/public/img/logo/Metanow.webp
```

### Via Plesk File Manager
1. Navigate to **Files** > **File Manager**
2. For background: Go to `httpdocs/public/img/` and upload `background.png`
3. For logo: Go to `httpdocs/storage/app/public/img/logo/` and upload `Metanow.webp`

## Verify Upload
After uploading, verify the files are accessible:
```bash
ls -la public/img/background.png
ls -la storage/app/public/img/logo/Metanow.webp
```

The images should be visible on the website immediately after upload.
