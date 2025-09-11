<?php

namespace App\Services;

class WordPressAnalyzer
{
    /**
     * Analyze a WordPress installation and collect comprehensive data
     */
    public static function analyze(string $docroot, string $slug, string $demoUrl): array
    {
        $data = [
            'slug' => $slug,
            'demo_url' => $demoUrl,
            'wp_info' => [],
            'theme_info' => [],
            'plugins_info' => [],
            'content_analysis' => [],
            'file_structure' => [],
        ];

        // Basic WordPress info
        $data['wp_info'] = self::getWordPressInfo($docroot);
        
        // Theme information
        $data['theme_info'] = self::getThemeInfo($docroot);
        
        // Plugin information
        $data['plugins_info'] = self::getPluginsInfo($docroot);
        
        // Content analysis
        $data['content_analysis'] = self::analyzeContent($docroot);
        
        // File structure analysis
        $data['file_structure'] = self::analyzeFileStructure($docroot);

        return $data;
    }

    /**
     * Extract WordPress version and basic info
     */
    private static function getWordPressInfo(string $docroot): array
    {
        $info = [
            'version' => null,
            'multisite' => false,
            'debug_mode' => false,
            'language' => 'en_US',
        ];

        // Try to get WP version from wp-includes/version.php
        $versionFile = $docroot . '/wp-includes/version.php';
        if (file_exists($versionFile)) {
            $content = file_get_contents($versionFile);
            if (preg_match('/\$wp_version\s*=\s*[\'"]([^\'"]+)[\'"]/', $content, $matches)) {
                $info['version'] = $matches[1];
            }
        }

        // Check wp-config.php for additional info
        $configFile = $docroot . '/wp-config.php';
        if (file_exists($configFile)) {
            $content = file_get_contents($configFile);
            
            // Check for multisite
            if (strpos($content, 'WP_ALLOW_MULTISITE') !== false || strpos($content, 'MULTISITE') !== false) {
                $info['multisite'] = true;
            }
            
            // Check for debug mode
            if (preg_match('/WP_DEBUG[\'"],\s*true/', $content)) {
                $info['debug_mode'] = true;
            }
            
            // Check for language
            if (preg_match('/WPLANG[\'"],\s*[\'"]([^\'"]*)[\'"]/', $content, $matches)) {
                $info['language'] = $matches[1] ?: 'en_US';
            }
        }

        return $info;
    }

    /**
     * Get active theme and theme information
     */
    private static function getThemeInfo(string $docroot): array
    {
        $info = [
            'active_theme' => null,
            'theme_features' => [],
            'parent_theme' => null,
        ];

        $themesDir = $docroot . '/wp-content/themes';
        if (!is_dir($themesDir)) {
            return $info;
        }

        // Get all themes
        $themes = [];
        foreach (glob($themesDir . '/*', GLOB_ONLYDIR) as $themeDir) {
            $themeName = basename($themeDir);
            $styleFile = $themeDir . '/style.css';
            
            if (file_exists($styleFile)) {
                $themeData = self::parseThemeHeader($styleFile);
                $themes[$themeName] = $themeData;
                
                // Detect if this might be the active theme (basic heuristic)
                if (!$info['active_theme'] && !in_array($themeName, ['twentytwenty', 'twentytwentyone', 'twentytwentytwo', 'twentytwentythree', 'twentytwentyfour'])) {
                    $info['active_theme'] = $themeName;
                    $info['theme_features'] = $themeData;
                }
            }
        }

        return $info;
    }

    /**
     * Parse theme style.css header
     */
    private static function parseThemeHeader(string $styleFile): array
    {
        $content = file_get_contents($styleFile);
        $header = [];
        
        // Extract theme header information
        if (preg_match('/Theme Name:\s*(.+)/i', $content, $matches)) {
            $header['name'] = trim($matches[1]);
        }
        if (preg_match('/Description:\s*(.+)/i', $content, $matches)) {
            $header['description'] = trim($matches[1]);
        }
        if (preg_match('/Version:\s*(.+)/i', $content, $matches)) {
            $header['version'] = trim($matches[1]);
        }
        if (preg_match('/Author:\s*(.+)/i', $content, $matches)) {
            $header['author'] = trim($matches[1]);
        }
        if (preg_match('/Template:\s*(.+)/i', $content, $matches)) {
            $header['parent_theme'] = trim($matches[1]);
        }
        if (preg_match('/Tags:\s*(.+)/i', $content, $matches)) {
            $header['tags'] = array_map('trim', explode(',', $matches[1]));
        }

        return $header;
    }

    /**
     * Get installed plugins information
     */
    private static function getPluginsInfo(string $docroot): array
    {
        $plugins = [];
        $pluginsDir = $docroot . '/wp-content/plugins';
        
        if (!is_dir($pluginsDir)) {
            return $plugins;
        }

        foreach (glob($pluginsDir . '/*', GLOB_ONLYDIR) as $pluginDir) {
            $pluginName = basename($pluginDir);
            $pluginFile = $pluginDir . '/' . $pluginName . '.php';
            
            // Look for main plugin file
            if (!file_exists($pluginFile)) {
                $phpFiles = glob($pluginDir . '/*.php');
                if (!empty($phpFiles)) {
                    $pluginFile = $phpFiles[0];
                }
            }
            
            if (file_exists($pluginFile)) {
                $pluginData = self::parsePluginHeader($pluginFile);
                $pluginData['folder'] = $pluginName;
                $plugins[$pluginName] = $pluginData;
            }
        }

        return $plugins;
    }

    /**
     * Parse plugin header
     */
    private static function parsePluginHeader(string $pluginFile): array
    {
        $content = file_get_contents($pluginFile, false, null, 0, 8192); // Read first 8KB
        $header = [];
        
        if (preg_match('/Plugin Name:\s*(.+)/i', $content, $matches)) {
            $header['name'] = trim($matches[1]);
        }
        if (preg_match('/Description:\s*(.+)/i', $content, $matches)) {
            $header['description'] = trim($matches[1]);
        }
        if (preg_match('/Version:\s*(.+)/i', $content, $matches)) {
            $header['version'] = trim($matches[1]);
        }
        if (preg_match('/Author:\s*(.+)/i', $content, $matches)) {
            $header['author'] = trim($matches[1]);
        }

        return $header;
    }

    /**
     * Analyze content structure
     */
    private static function analyzeContent(string $docroot): array
    {
        $analysis = [
            'uploads_size' => 0,
            'uploads_types' => [],
            'has_custom_content' => false,
            'content_indicators' => [],
        ];

        // Analyze uploads directory
        $uploadsDir = $docroot . '/wp-content/uploads';
        if (is_dir($uploadsDir)) {
            $analysis['uploads_size'] = self::getDirSize($uploadsDir);
            $analysis['uploads_types'] = self::getFileTypes($uploadsDir);
            $analysis['has_custom_content'] = $analysis['uploads_size'] > 1024 * 1024; // > 1MB
        }

        // Look for content indicators
        $contentIndicators = [];
        
        // Check for common ecommerce files
        if (is_dir($docroot . '/wp-content/plugins/woocommerce')) {
            $contentIndicators[] = 'woocommerce';
        }
        
        // Check for page builders
        if (is_dir($docroot . '/wp-content/plugins/elementor')) {
            $contentIndicators[] = 'elementor';
        }
        if (is_dir($docroot . '/wp-content/themes') && glob($docroot . '/wp-content/themes/Divi*')) {
            $contentIndicators[] = 'divi';
        }
        
        // Check for other common plugins
        $commonPlugins = [
            'contact-form-7' => 'contact_forms',
            'mailchimp-for-wp' => 'newsletter',
            'yoast' => 'seo',
            'wpml' => 'multilingual',
            'polylang' => 'multilingual',
            'booking' => 'booking',
            'events' => 'events',
            'gallery' => 'gallery',
            'slider' => 'slider',
        ];
        
        foreach ($commonPlugins as $plugin => $indicator) {
            if (is_dir($docroot . '/wp-content/plugins/' . $plugin) || 
                !empty(glob($docroot . '/wp-content/plugins/*' . $plugin . '*'))) {
                $contentIndicators[] = $indicator;
            }
        }

        $analysis['content_indicators'] = array_unique($contentIndicators);

        return $analysis;
    }

    /**
     * Analyze file structure for additional insights
     */
    private static function analyzeFileStructure(string $docroot): array
    {
        $structure = [
            'custom_themes' => 0,
            'custom_plugins' => 0,
            'mu_plugins' => 0,
            'has_child_theme' => false,
            'has_custom_uploads' => false,
        ];

        // Count custom themes (non-twenty* themes)
        $themesDir = $docroot . '/wp-content/themes';
        if (is_dir($themesDir)) {
            $themes = glob($themesDir . '/*', GLOB_ONLYDIR);
            $customThemes = array_filter($themes, function($theme) {
                $themeName = basename($theme);
                return !preg_match('/^twenty\d+/', $themeName);
            });
            $structure['custom_themes'] = count($customThemes);
            
            // Check for child themes
            foreach ($customThemes as $theme) {
                $styleFile = $theme . '/style.css';
                if (file_exists($styleFile)) {
                    $content = file_get_contents($styleFile);
                    if (preg_match('/Template:/i', $content)) {
                        $structure['has_child_theme'] = true;
                        break;
                    }
                }
            }
        }

        // Count custom plugins
        $pluginsDir = $docroot . '/wp-content/plugins';
        if (is_dir($pluginsDir)) {
            $structure['custom_plugins'] = count(glob($pluginsDir . '/*', GLOB_ONLYDIR));
        }

        // Check for must-use plugins
        $muPluginsDir = $docroot . '/wp-content/mu-plugins';
        if (is_dir($muPluginsDir)) {
            $structure['mu_plugins'] = count(glob($muPluginsDir . '/*.php'));
        }

        // Check for custom uploads
        $uploadsDir = $docroot . '/wp-content/uploads';
        if (is_dir($uploadsDir)) {
            $structure['has_custom_uploads'] = self::getDirSize($uploadsDir) > 0;
        }

        return $structure;
    }

    /**
     * Get directory size in bytes
     */
    private static function getDirSize(string $dir): int
    {
        $size = 0;
        try {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        } catch (\Exception $e) {
            // Ignore errors, return 0
        }
        return $size;
    }

    /**
     * Get file types in directory
     */
    private static function getFileTypes(string $dir): array
    {
        $types = [];
        try {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $ext = strtolower($file->getExtension());
                    if ($ext) {
                        $types[$ext] = ($types[$ext] ?? 0) + 1;
                    }
                }
            }
        } catch (\Exception $e) {
            // Ignore errors
        }
        return $types;
    }
}