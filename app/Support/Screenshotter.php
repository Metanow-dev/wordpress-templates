<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Spatie\Browsershot\Browsershot;
use Spatie\Image\Image as SpatieImage;

final class Screenshotter
{
    public function __construct(
        private string $slug,
        private string $url,
        private string $dir = 'screenshots',
    ) {}

    public static function for(string $slug, string $url): self
    {
        return new self($slug, $url);
    }

    private function rel(): string { return $this->dir.'/'.$this->slug.'.png'; }
    private function abs(): string { return storage_path('app/public/'.$this->rel()); }
    private function publicUrl(): string { return asset('storage/'.$this->rel()); }

    private function chromePath(): ?string
    {
        // 1) .env override (recommended)
        $env = env('BROWSERSHOT_CHROME_BINARY');
        if ($env && is_file($env)) return $env;

        // 2) Puppeteer-managed Chrome (if installed in container)
        $pp = trim(@shell_exec('node -e "try{console.log(require(\'puppeteer\').executablePath())}catch(e){}"') ?: '');
        if ($pp && is_file($pp)) return $pp;

        // 3) System binaries (avoid the snap shim chromium-browser)
        foreach (['/usr/bin/google-chrome', '/usr/bin/google-chrome-stable', '/usr/bin/chromium'] as $c) {
            if (is_file($c) && is_executable($c)) return $c;
        }
        return null;
    }

    private function nodePath(): ?string
    {
        $env = env('BROWSERSHOT_NODE_BINARY');
        if ($env && is_file($env)) return $env;

        $p = trim(@shell_exec('command -v node') ?: '');
        return ($p && is_file($p)) ? $p : null;
    }

    public function capture(int $width = 343, int $height = 192, bool $fullPage = false): string
    {
        File::ensureDirectoryExists(dirname($this->abs()), 0775, true);

        // Special handling for problematic sites
        $isProblematicSite = in_array($this->slug, ['albaniatourguide']);
        
        $shot = Browsershot::url($this->url)
            ->windowSize($width, $height)
            ->deviceScaleFactor(1) // 1:1 pixel ratio - no scaling
            ->timeout($isProblematicSite ? 45 : 90) // Increased timeouts
            ->setDelay($isProblematicSite ? 2000 : 3000) // Longer delays for proper loading
            ->quality(100) // Maximum quality for text clarity
            ->format('png') // PNG for better text rendering
            ->addChromiumArguments([
                'no-sandbox',
                'disable-dev-shm-usage',
                'ignore-certificate-errors',
                'disable-background-timer-throttling',
                'disable-backgrounding-occluded-windows',
                'disable-renderer-backgrounding',
                'disable-extensions',
                'disable-plugins',
                'disable-web-security', // Help with CORS issues
                'force-device-scale-factor=1', // No scaling
                'enable-font-antialiasing', // Smooth fonts
                'disable-lcd-text', // Better text on screenshots
                'force-color-profile=srgb', // Consistent colors
                'disable-features=TranslateUI' // Prevent translation overlays
            ]);
        
        // Use different wait strategy for problematic sites
        if ($isProblematicSite) {
            $shot->waitUntilNetworkIdle(0, 1000); // Wait for network to be idle for 1 second
        } else {
            $shot->waitUntilNetworkIdle(0, 2000); // Wait for network to be idle for 2 seconds
        }

        if ($n = $this->nodePath())   $shot->setNodeBinary($n);
        if ($c = $this->chromePath()) $shot->setChromePath($c);
        if ($fullPage)                $shot->fullPage();

        $shot->save($this->abs());

        // Create responsive, optimized variants for faster delivery
        try {
            $this->createResponsiveVariants();
        } catch (\Throwable $e) {
            // Non-fatal: continue even if variant generation fails
            error_log("Failed to create responsive variants: " . $e->getMessage());
        }
        
        // Automatically fix permissions for new screenshots
        $this->fixScreenshotPermissions();
        
        return $this->publicUrl();
    }

    /**
     * Fix permissions automatically after screenshot capture
     */
    private function fixScreenshotPermissions(): void
    {
        try {
            $screenshotFile = $this->abs();
            $screenshotDir = dirname($screenshotFile);

            // Detect target owner/group
            $targetUser = env('SCREENSHOT_SYSUSER');
            $targetGroup = env('SCREENSHOT_GROUP');

            // Try to infer from 'public/storage' symlink or storage dir if not provided
            $probe = is_link(public_path('storage')) ? readlink(public_path('storage')) : storage_path('app/public');
            if (!$targetUser && file_exists($probe)) {
                $ownerId = @fileowner($probe);
                if ($ownerId !== false && function_exists('posix_getpwuid')) {
                    $pw = @posix_getpwuid($ownerId);
                    if ($pw && !empty($pw['name'])) $targetUser = $pw['name'];
                }
                $groupId = @filegroup($probe);
                if ($groupId !== false && function_exists('posix_getgrgid')) {
                    $gr = @posix_getgrgid($groupId);
                    if ($gr && !empty($gr['name'])) $targetGroup = $gr['name'];
                }
            }

            // Fallback defaults for Plesk
            if (!$targetUser) $targetUser = 'wp-templates.metanow_r6s2v1oe7wr';
            if (!$targetGroup) $targetGroup = 'psacln';

            // Apply ownership if possible
            $applyChown = function (string $path) use ($targetUser, $targetGroup): void {
                // Set permissions first
                if (is_dir($path)) {
                    @chmod($path, 0755);
                } else {
                    @chmod($path, 0644);
                }

                // Try PHP functions first
                if (function_exists('chown')) @chown($path, $targetUser);
                if (function_exists('chgrp')) @chgrp($path, $targetGroup);

                // If still wrong owner, attempt shell chown (when allowed)
                $ownerOk = @fileowner($path);
                $groupOk = @filegroup($path);
                $needShell = false;
                if (function_exists('posix_getpwnam') && $ownerOk !== false) {
                    $pw = @posix_getpwnam($targetUser);
                    if ($pw && isset($pw['uid']) && $pw['uid'] !== $ownerOk) $needShell = true;
                }
                if (!$needShell && function_exists('posix_getgrnam') && $groupOk !== false) {
                    $gr = @posix_getgrnam($targetGroup);
                    if ($gr && isset($gr['gid']) && $gr['gid'] !== $groupOk) $needShell = true;
                }
                if ($needShell && function_exists('shell_exec')) {
                    @shell_exec('chown '.escapeshellarg($targetUser).':'.escapeshellarg($targetGroup).' '.escapeshellarg($path));
                }
            };

            // Fix directory permissions
            if (is_dir($screenshotDir)) $applyChown($screenshotDir);

            // Fix main screenshot file permissions
            if (file_exists($screenshotFile)) $applyChown($screenshotFile);

            // Fix all variant files permissions (the missing piece!)
            $basePath = storage_path('app/public/screenshots/');
            $widths = [480, 768, 1024];
            foreach ($widths as $w) {
                $webpPath = $basePath . $this->slug . "-{$w}.webp";
                $pngPath = $basePath . $this->slug . "-{$w}.png";
                
                if (file_exists($webpPath)) $applyChown($webpPath);
                if (file_exists($pngPath)) $applyChown($pngPath);
            }
            
        } catch (\Exception $e) {
            // Log error but don't fail screenshot capture
            error_log("Failed to fix screenshot permissions: " . $e->getMessage());
        }
    }

    /**
     * Create multiple WebP variants for responsive images.
     *  -480w, -768w, -1024w files alongside the original.
     */
    private function createResponsiveVariants(): void
    {
        $src = $this->abs();
        if (!is_file($src)) return;

        $widths = [480, 768, 1024];

        $hasImagick = extension_loaded('imagick');
        $hasGd = extension_loaded('gd');
        $gdCanWebp = function_exists('imagewebp');

        // Prefer Imagick; else use GD. If WebP unsupported, fall back to PNG.
        $driver = $hasImagick ? 'imagick' : ($hasGd ? 'gd' : null);
        if (! $driver) return; // No image library available

        foreach ($widths as $w) {
            $webpDest = storage_path('app/public/screenshots/'.$this->slug."-{$w}.webp");
            $pngDest  = storage_path('app/public/screenshots/'.$this->slug."-{$w}.png");

            $srcMtime = filemtime($src) ?: time();
            $webpFresh = file_exists($webpDest) && filemtime($webpDest) >= $srcMtime;
            $pngFresh  = file_exists($pngDest)  && filemtime($pngDest)  >= $srcMtime;
            if ($webpFresh || $pngFresh) continue;

            try {
                // Force driver explicitly
                $img = SpatieImage::useImageDriver($driver)->loadFile($src);
                $img->width($w);

                if ($hasImagick || ($hasGd && $gdCanWebp)) {
                    $img->format('webp')->quality(82)->save($webpDest);
                } else {
                    $img->format('png')->quality(82)->save($pngDest);
                }
            } catch (\Throwable $e) {
                // Fallback: try PNG if WEBP failed
                try {
                    SpatieImage::useImageDriver($driver)
                        ->loadFile($src)
                        ->width($w)
                        ->format('png')
                        ->quality(82)
                        ->save($pngDest);
                } catch (\Throwable $e2) {
                    // Give up on this size
                    error_log("Variant generation failed for {$this->slug}-{$w}: ".$e2->getMessage());
                }
            }
        }
    }

    /**
     * Public helper to (re)generate responsive variants for an existing PNG.
     */
    public function generateVariants(): void
    {
        $this->createResponsiveVariants();
        // Always fix permissions after creating variants
        $this->fixScreenshotPermissions();
    }
}
