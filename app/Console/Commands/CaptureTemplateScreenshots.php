<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Template;
use App\Support\Screenshotter;
use Illuminate\Support\Str;

class CaptureTemplateScreenshots extends Command
{
    protected $signature = 'templates:screenshot 
        {--slug= : Only capture for this slug} 
        {--force : Force re-capture even if screenshot_url exists}
        {--new-only : Only capture screenshots for templates without captured screenshots}
        {--fullpage : Capture full-page instead of viewport}
        {--w=1200 : Viewport width}
        {--h=800  : Viewport height}';

    protected $description = 'Capture browser screenshots for template demos';

    public function handle(): int
    {
        $q = Template::query();
        if ($slug = $this->option('slug')) { $q->where('slug', $slug); }

        $w = (int)$this->option('w');
        $h = (int)$this->option('h');
        $full = (bool)$this->option('fullpage');
        $force = (bool)$this->option('force');
        $newOnly = (bool)$this->option('new-only');

        $count = 0;
        $q->lazy()->each(function (Template $t) use (&$count, $w, $h, $full, $force, $newOnly) {
            if (!$t->demo_url) {
                $this->warn("{$t->slug}: no demo_url, skipping");
                return;
            }

            // Check if captured screenshot already exists
            $capturedScreenshotExists = file_exists(storage_path('app/public/screenshots/' . $t->slug . '.jpg'));
            $hasThemeScreenshot = $t->screenshot_url && !str_contains($t->screenshot_url, '/storage/screenshots/');

            if ($newOnly && $capturedScreenshotExists) {
                $this->line("{$t->slug}: captured screenshot exists, skipping (use --force to recapture)");
                return;
            }

            if (!$force && $capturedScreenshotExists) {
                $this->line("{$t->slug}: captured screenshot exists, skip (use --force to recapture)");
                return;
            }

            // If has theme screenshot but no captured screenshot, capture it
            if ($hasThemeScreenshot && !$capturedScreenshotExists) {
                $this->info("{$t->slug}: replacing theme screenshot with captured screenshot");
            }

            $this->info("{$t->slug}: capturing {$t->demo_url} …");
            try {
                $url = Screenshotter::for($t->slug, $t->demo_url)->capture($w, $h, $full);
                $t->screenshot_url = $url;
                $t->save();
                $this->line(" → saved: ".$url);
                $count++;
            } catch (\Throwable $e) {
                $this->error("{$t->slug}: capture failed: ".$e->getMessage());
            }
        });

        $this->info("Done. Captured {$count} screenshot(s).");
        return self::SUCCESS;
    }
}
