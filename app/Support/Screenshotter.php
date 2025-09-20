<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Spatie\Browsershot\Browsershot;

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
            
            // Set correct file permissions
            if (file_exists($screenshotFile)) {
                chmod($screenshotFile, 0644);
            }
            
            // Set correct directory permissions
            if (is_dir($screenshotDir)) {
                chmod($screenshotDir, 0755);
            }
            
            // Set correct ownership (Plesk domain user)
            $sysUser = 'wp-templates.metanow_r6s2v1oe7wr';
            $group = 'psacln';
            
            if (file_exists($screenshotFile)) {
                @chown($screenshotFile, $sysUser);
                @chgrp($screenshotFile, $group);
            }
            
            if (is_dir($screenshotDir)) {
                @chown($screenshotDir, $sysUser);
                @chgrp($screenshotDir, $group);
            }
            
        } catch (\Exception $e) {
            // Log error but don't fail screenshot capture
            error_log("Failed to fix screenshot permissions: " . $e->getMessage());
        }
    }
}
