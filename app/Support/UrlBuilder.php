<?php

namespace App\Support;

class UrlBuilder
{
    public static function screenshot(string $slug, string $relativePath, string $demoUrlPattern): string
    {
        $relativePath = ltrim($relativePath, '/');

        if (app()->environment('local')) {
            // Serve via Laravel media route in dev
            return url("media/{$slug}/{$relativePath}");
        }

        // In prod, point at the real demo path
        $demoUrl = str_replace('{slug}', $slug, $demoUrlPattern);
        return rtrim($demoUrl, '/') . '/' . $relativePath;
    }
}
