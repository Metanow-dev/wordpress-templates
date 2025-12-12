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
        {--skip-problematic : Skip known problematic sites that timeout}
        {--fullpage : Capture full-page instead of viewport}
        {--w=1200 : Viewport width}
        {--h=800  : Viewport height}
        {--limit=0 : Limit number of screenshots to capture (0 = unlimited)}
        {--batch-size=5 : Number of screenshots before Chrome cleanup}';

    protected $description = 'Capture browser screenshots for template demos';

    public function handle(): int
    {
        // Prevent concurrent screenshot processes
        $lockFile = storage_path('framework/screenshot.lock');
        $fp = fopen($lockFile, 'c+');
        if (!flock($fp, LOCK_EX | LOCK_NB)) {
            $this->error('Another screenshot process is already running. Please wait or kill it first.');
            fclose($fp);
            return self::FAILURE;
        }

        try {
            return $this->executeScreenshots();
        } finally {
            flock($fp, LOCK_UN);
            fclose($fp);
            @unlink($lockFile);
        }
    }

    private function executeScreenshots(): int
    {
        $q = Template::query();
        if ($slug = $this->option('slug')) { $q->where('slug', $slug); }

        $w = (int)$this->option('w');
        $h = (int)$this->option('h');
        $full = (bool)$this->option('fullpage');
        $force = (bool)$this->option('force');
        $newOnly = (bool)$this->option('new-only');
        $skipProblematic = (bool)$this->option('skip-problematic');
        $limit = (int)$this->option('limit');
        $batchSize = (int)$this->option('batch-size');

        // Known problematic sites that frequently timeout
        $problematicSites = ['albaniatourguide'];

        // Memory management settings
        $memoryLimit = env('SCREENSHOT_MEMORY_LIMIT_MB', 256); // MB
        $delayBetweenCaptures = env('SCREENSHOT_DELAY_BETWEEN_MS', 2000); // milliseconds

        $count = 0;
        $batchCount = 0;

        if ($limit > 0) {
            $this->info("Limiting to {$limit} screenshot(s)");
        }
        $q->lazy()->each(function (Template $t) use (&$count, &$batchCount, $w, $h, $full, $force, $newOnly, $skipProblematic, $problematicSites, $memoryLimit, $delayBetweenCaptures, $limit, $batchSize) {
            // Stop if limit reached
            if ($limit > 0 && $count >= $limit) {
                return false;
            }
            if (!$t->demo_url) {
                $this->warn("{$t->slug}: no demo_url, skipping");
                return;
            }
            
            // Skip known problematic sites if option is set
            if ($skipProblematic && in_array($t->slug, $problematicSites)) {
                $this->line("{$t->slug}: skipping problematic site");
                return;
            }

            // Check if captured screenshot already exists
            $capturedScreenshotExists = file_exists(base_path('screenshots/' . $t->slug . '.png'));
            $hasThemeScreenshot = $t->screenshot_url && !str_contains($t->screenshot_url, '/screenshots/');

            // When using --new-only, skip templates that already have captured screenshots
            // This allows capturing only newly arrived templates without --force
            if ($newOnly && $capturedScreenshotExists) {
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

            // Check memory usage before capture
            $currentMemoryMB = memory_get_usage(true) / 1024 / 1024;
            if ($currentMemoryMB > $memoryLimit) {
                $this->warn("{$t->slug}: memory limit reached ({$currentMemoryMB}MB), forcing cleanup");
                gc_collect_cycles();
                sleep(2); // Give system time to free resources
            }

            $this->info("{$t->slug}: capturing {$t->demo_url} …");
            try {
                $url = Screenshotter::for($t->slug, $t->demo_url)->capture($w, $h, $full);
                $t->screenshot_url = $url;
                $t->save();
                $this->line(" → saved: ".$url);
                $count++;
                $batchCount++;

                // Cleanup Chrome processes after batch
                if ($batchCount >= $batchSize) {
                    $this->info("  Batch complete ({$batchCount} screenshots). Cleaning up Chrome processes...");
                    $this->call('chrome:cleanup', ['--force' => true]);
                    $batchCount = 0;
                    gc_collect_cycles();
                    sleep(3); // Give system time to clean up
                }

                // Add delay between captures to prevent server overload
                if ($delayBetweenCaptures > 0) {
                    usleep($delayBetweenCaptures * 1000);
                }
            } catch (\Throwable $e) {
                $this->error("{$t->slug}: capture failed: ".$e->getMessage());

                // Force cleanup after error
                gc_collect_cycles();
                sleep(1);
            }
        });

        // Final cleanup
        if ($count > 0) {
            $this->info("Final cleanup...");
            $this->call('chrome:cleanup', ['--force' => true]);
        }

        $this->info("Done. Captured {$count} screenshot(s).");
        return self::SUCCESS;
    }
}
