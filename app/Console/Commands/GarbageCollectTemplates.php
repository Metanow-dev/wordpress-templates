<?php

namespace App\Console\Commands;

use App\Models\Template;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GarbageCollectTemplates extends Command
{
    protected $signature = 'templates:gc
        {--dry-run : Show what would be deleted without actually deleting}
        {--clean-screenshots : Also remove orphaned screenshot files}';

    protected $description = 'Remove templates from database that no longer exist in filesystem';

    public function handle(): int
    {
        $root = config('templates.root');
        if (!is_dir($root)) {
            $this->error("Templates root not found: {$root}");
            return self::FAILURE;
        }

        $isDryRun = $this->option('dry-run');
        $cleanScreenshots = $this->option('clean-screenshots');

        $this->info("Scanning templates root: {$root}");
        $this->info($isDryRun ? "DRY RUN MODE - No changes will be made" : "LIVE MODE - Will delete orphaned records");
        $this->newLine();

        // Get all templates from database
        $dbTemplates = Template::all();
        $this->info("Found {$dbTemplates->count()} templates in database");

        // Check each template to see if it still exists in filesystem
        $orphanedTemplates = [];
        $validTemplates = 0;

        foreach ($dbTemplates as $template) {
            $base = $root . '/' . $template->slug;

            // Check common layouts: <slug>/public, <slug>/httpdocs (Plesk), or <slug> as docroot
            $docroots = ["{$base}/public", "{$base}/httpdocs", $base];
            $exists = collect($docroots)->contains(fn($p) => is_file($p . '/wp-config.php'));

            if (!$exists) {
                $orphanedTemplates[] = $template;
                $this->warn("  ORPHANED: {$template->slug} (directory not found or no wp-config.php)");
            } else {
                $validTemplates++;
            }
        }

        $orphanedCount = count($orphanedTemplates);
        $this->newLine();
        $this->info("Results:");
        $this->line("  Valid templates: {$validTemplates}");
        $this->line("  Orphaned templates: {$orphanedCount}");

        if ($orphanedCount === 0) {
            $this->info("No orphaned templates found. Database is clean!");
            return self::SUCCESS;
        }

        // Delete orphaned templates
        if (!$isDryRun) {
            $this->newLine();
            if (!$this->confirm("Delete {$orphanedCount} orphaned template(s) from database?", true)) {
                $this->info("Aborted by user.");
                return self::SUCCESS;
            }

            $deletedCount = 0;
            $deletedScreenshots = 0;

            foreach ($orphanedTemplates as $template) {
                // Optionally clean up screenshot files
                if ($cleanScreenshots) {
                    $screenshotFiles = $this->getScreenshotFiles($template->slug);
                    foreach ($screenshotFiles as $file) {
                        if (File::exists($file)) {
                            File::delete($file);
                            $deletedScreenshots++;
                            $this->line("  Deleted screenshot: " . basename($file));
                        }
                    }
                }

                // Delete from database
                $template->delete();
                $deletedCount++;
                $this->info("  Deleted: {$template->slug}");
            }

            $this->newLine();
            $this->info("Garbage collection complete!");
            $this->line("  Templates deleted: {$deletedCount}");
            if ($cleanScreenshots) {
                $this->line("  Screenshots deleted: {$deletedScreenshots}");
            }
        } else {
            $this->newLine();
            $this->comment("Run without --dry-run to actually delete these templates.");

            if ($cleanScreenshots) {
                $this->newLine();
                $this->comment("Screenshots that would be deleted:");
                foreach ($orphanedTemplates as $template) {
                    $screenshotFiles = $this->getScreenshotFiles($template->slug);
                    foreach ($screenshotFiles as $file) {
                        if (File::exists($file)) {
                            $this->line("  " . basename($file));
                        }
                    }
                }
            }
        }

        return self::SUCCESS;
    }

    /**
     * Get all screenshot files for a given slug
     */
    private function getScreenshotFiles(string $slug): array
    {
        $basePath = base_path('screenshots/');
        $files = [
            $basePath . $slug . '.png',
        ];

        // Add responsive variants
        $widths = [480, 768, 1024];
        foreach ($widths as $w) {
            $files[] = $basePath . $slug . "-{$w}.webp";
            $files[] = $basePath . $slug . "-{$w}.png";
        }

        return $files;
    }
}
