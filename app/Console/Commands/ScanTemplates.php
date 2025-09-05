<?php

namespace App\Console\Commands;

use App\Models\Template;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;
use App\Support\UrlBuilder;


class ScanTemplates extends Command
{
    protected $signature = 'templates:scan {--path=} {--limit=0} {--capture : If no theme screenshot found, capture a browser screenshot}';

    protected $description = 'Scan templates root for WordPress installs and upsert into DB';

    public function handle(): int
    {
        $root = $this->option('path') ?: config('templates.root');
        if (!is_dir($root)) {
            $this->error("Templates root not found: {$root}");
            return self::FAILURE;
        }

        $finder = new Finder();
        $finder->in($root)->depth('== 0')->directories(); // only top-level folders (slugs)


        $count = 0;
        foreach ($finder as $dir) {
            $base = $dir->getRealPath();
            // common layouts: <slug>/public or <slug> as docroot
            $docroots = ["{$base}/public", $base];
            $docroot = collect($docroots)->first(fn($p) => is_file($p . '/wp-config.php')) ?? null;

            if (!$docroot) {
                // not a WP install, skip
                continue;
            }

            $slug = Str::slug(basename($dir->getRelativePathname()));

            $demoUrl = str_replace('{slug}', $slug, config('templates.demo_url_pattern'));

            // Find a screenshot
            $screenshot = null;
            $relPath = null;
            foreach (config('templates.screenshot_candidates') as $pattern) {
                foreach (glob($docroot . '/' . $pattern) as $file) {
                    $relPath = ltrim(Str::after($file, $docroot), '/');
                    break 2;
                }
            }

            if ($relPath) {
                $screenshot = UrlBuilder::screenshot($slug, $relPath, config('templates.demo_url_pattern'));
            }

            // Very basic name (we can improve later from site title)
            $name = Str::headline($slug);

            Template::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'demo_url' => $demoUrl,
                    'screenshot_url' => $screenshot,
                    'last_scanned_at' => now(),
                ]
            );

            // Optional auto-capture if no theme screenshot was found
            if (!$screenshot && $this->option('capture')) {
                try {
                    $url = \App\Support\Screenshotter::for($slug, $demoUrl)->capture();
                    \App\Models\Template::where('slug', $slug)->update(['screenshot_url' => $url]);
                    $this->info(" → Captured screenshot for {$slug}");
                } catch (\Throwable $e) {
                    $this->warn(" → Capture failed for {$slug}: " . $e->getMessage());
                }
            }

            $this->info("Indexed {$slug} -> {$demoUrl}");
            $count++;

            if (($lim = (int)$this->option('limit')) > 0 && $count >= $lim) {
                break;
            }
        }

        $this->info("Scan complete. Indexed {$count} sites.");
        return self::SUCCESS;
    }
}
