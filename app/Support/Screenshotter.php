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
                'disable-features=TranslateUI', // Prevent translation overlays
                'disable-features=VizDisplayCompositor', // Prevent consent popups
                'disable-component-extensions-with-background-pages' // Block consent manager extensions
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

        // Dismiss cookie popups and inject hiding CSS before taking screenshot
        $shot->evaluate($this->getCookiePopupScript());
        
        // Additional delay to ensure cookie popups are fully dismissed
        $shot->setDelay(1500); // Extra 1.5 seconds after script execution

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

    /**
     * Generate JavaScript to dismiss common cookie popups and hide consent banners
     */
    private function getCookiePopupScript(): string
    {
        return "
        // Wait for page to be fully loaded
        setTimeout(() => {
            try {
                // Comprehensive cookie popup dismissal script
                
                // 1. Hide common cookie popup elements using CSS
                const hideStyle = document.createElement('style');
                hideStyle.textContent = `
                    /* Common cookie popup selectors */
                    [id*='cookie' i], [class*='cookie' i],
                    [id*='consent' i], [class*='consent' i],
                    [id*='gdpr' i], [class*='gdpr' i],
                    [id*='privacy' i], [class*='privacy' i],
                    [class*='banner' i][class*='cookie' i],
                    [class*='notice' i][class*='cookie' i],
                    .cookiebot-popup,
                    .onetrust-banner-sdk,
                    .ot-sdk-container,
                    #CybotCookiebotDialog,
                    .cc-banner,
                    .cookie-notice,
                    .cookie-law-info-bar,
                    .moove_gdpr_cookie_info_bar,
                    .cli-bar-container,
                    #wpgdprc-consent-bar,
                    .complianz-cookiebanner,
                    .cmplz-cookiebanner,
                    .cmplz-cookiebanner.banner-1,
                    .cmplz-cookiebanner.banner-a,
                    .cmplz-cookiebanner.optin,
                    .cmplz-cookiebanner.cmplz-bottom-right,
                    .cmplz-cookiebanner.cmplz-categories-type-view-preferences,
                    .cmplz-cookiebanner.cmplz-show,
                    #cmplz-cookiebanner-container,
                    [id*='cmplz' i],
                    [class*='cmplz-' i],
                    [class*='complianz' i],
                    .cookie-consent,
                    .gdpr-cookie-notice,
                    .pea_cook_wrapper,
                    .eu-cookie-law,
                    .elementor-widget-eael-cookie-consent,
                    .wp-gdpr-cookie-notice,
                    [data-nosnippet],
                    .cky-consent-container,
                    .borlabs-cookie-banner,
                    #cookie-notice,
                    #cookie-law-info-bar
                    {
                        display: none !important;
                        visibility: hidden !important;
                        opacity: 0 !important;
                        height: 0 !important;
                        overflow: hidden !important;
                        z-index: -9999 !important;
                    }
                    
                    /* Remove backdrop/overlay */
                    body .modal-backdrop,
                    body .backdrop,
                    body [class*='overlay' i],
                    body [class*='modal' i][class*='cookie' i]
                    {
                        display: none !important;
                    }
                    
                    /* Ensure body scroll is enabled */
                    body {
                        overflow: auto !important;
                        position: static !important;
                    }
                `;
                document.head.appendChild(hideStyle);
                
                // 2. Click common accept/dismiss buttons
                const buttonSelectors = [
                    // Generic selectors
                    '[data-accept]', '[data-dismiss]', '[data-close]',
                    'button[class*=\"accept\" i]', 'button[class*=\"allow\" i]',
                    'button[class*=\"agree\" i]', 'button[class*=\"ok\" i]',
                    'a[class*=\"accept\" i]', 'a[class*=\"allow\" i]',
                    
                    // Specific plugin selectors
                    '.cc-allow', '.cc-dismiss', '.cc-accept',
                    '.cookie-notice-dismiss', '.cn-accept-cookie',
                    '.cli-user-preference-checkbox', '.cli_action_button',
                    '.moove_gdpr_infobar_allow_all', '.mgbutton',
                    '.onetrust-close-btn-handler', '#onetrust-accept-btn-handler',
                    '.optanon-allow-all', '.ot-sdk-btn-primary',
                    '#CybotCookiebotDialogBodyButtonAccept',
                    '.cookiebot-dismiss', '.cookiebot-accept',
                    '#wpgdprc-consent-bar .wpgdprc-consent-bar-option[data-gdprconsent=\"accept\"]',
                    '.complianz-accept', '.cmplz-accept',
                    '.cmplz-btn', '.cmplz-accept-all', '.cmplz-functional',
                    '.cmplz-save-settings', '.cmplz-manage-consent',
                    'button[class*=\"cmplz\"]', 'a[class*=\"cmplz\"]',
                    '.pea_cook_btn[data-accept=\"1\"]',
                    '.gdpr-accept', '.gdpr-allow',
                    '.cky-btn-accept', '.cky-consent-accept',
                    '.borlabs-accept-all', '.cookie-consent-accept',
                    '.elementor-button[data-settings*=\"accept\"]'
                ];
                
                buttonSelectors.forEach(selector => {
                    try {
                        const buttons = document.querySelectorAll(selector);
                        buttons.forEach(button => {
                            if (button && typeof button.click === 'function') {
                                button.click();
                            }
                        });
                    } catch (e) {
                        // Ignore individual button errors
                    }
                });
                
                // 3. Specifically target known stubborn containers
                const stubbornContainers = [
                    '#cmplz-cookiebanner-container',
                    '.cmplz-cookiebanner',
                    '[id*=\"cmplz\"]',
                    '[class*=\"cmplz\"]'
                ];
                
                stubbornContainers.forEach(selector => {
                    try {
                        const elements = document.querySelectorAll(selector);
                        elements.forEach(el => {
                            if (el) {
                                // Use the proven browser console method
                                el.hidden = true;
                                el.style.display = 'none !important';
                                el.style.visibility = 'hidden !important';
                                el.style.opacity = '0 !important';
                                el.style.zIndex = '-9999 !important';
                                // Try to remove from DOM as backup
                                try { el.remove(); } catch (e) {}
                            }
                        });
                    } catch (e) {
                        // Ignore errors
                    }
                });
                
                // Specific handling for the exact container you found
                try {
                    const cmplzContainer = document.getElementById('cmplz-cookiebanner-container');
                    if (cmplzContainer) {
                        cmplzContainer.hidden = true;
                        cmplzContainer.style.display = 'none !important';
                        cmplzContainer.style.visibility = 'hidden !important';
                        cmplzContainer.style.opacity = '0 !important';
                    }
                } catch (e) {
                    // Ignore errors
                }
                
                // 4. Try to find and remove any remaining visible cookie elements
                const allElements = document.querySelectorAll('*');
                allElements.forEach(el => {
                    try {
                        const text = el.textContent?.toLowerCase() || '';
                        const className = el.className?.toLowerCase() || '';
                        const id = el.id?.toLowerCase() || '';
                        
                        // Check if element contains cookie-related text/attributes
                        if ((text.includes('cookie') || text.includes('consent') || 
                             text.includes('gdpr') || text.includes('privacy')) &&
                            (className.includes('banner') || className.includes('notice') ||
                             className.includes('popup') || className.includes('modal') ||
                             id.includes('cookie') || id.includes('consent'))) {
                            
                            // Additional check: element should be positioned fixed/absolute
                            const style = window.getComputedStyle(el);
                            if (style.position === 'fixed' || style.position === 'absolute') {
                                el.style.display = 'none';
                                el.style.visibility = 'hidden';
                                el.style.opacity = '0';
                            }
                        }
                    } catch (e) {
                        // Ignore individual element errors
                    }
                });
                
                // 5. Set common cookie acceptance in localStorage/sessionStorage
                try {
                    const cookieKeys = [
                        'cookieAccepted', 'cookiesAccepted', 'gdprAccepted', 
                        'privacyAccepted', 'consentGiven', 'cookieConsent',
                        'cookie-notice-accepted', 'cli-user-preference',
                        'moove_gdpr_popup', 'complianz_consent_status',
                        'wp-gdpr-cookie-notice', 'borlabs-cookie'
                    ];
                    
                    cookieKeys.forEach(key => {
                        localStorage.setItem(key, 'true');
                        localStorage.setItem(key, '1');
                        sessionStorage.setItem(key, 'true');
                        sessionStorage.setItem(key, '1');
                    });
                } catch (e) {
                    // Ignore storage errors
                }
                
                console.log('Cookie popup dismissal script completed');
                
            } catch (error) {
                console.log('Cookie popup script error:', error);
            }
        }, 1000); // Wait 1 second for popups to appear
        ";
    }
}
