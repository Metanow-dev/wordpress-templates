<?php

namespace App\Console\Commands;

use App\Models\Template;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

class ScanTemplates extends Command
{
    protected $signature = 'templates:scan {--path=} {--limit=0}';
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
            foreach (config('templates.screenshot_candidates') as $pattern) {
                foreach (glob($docroot . '/' . $pattern) as $file) {
                    $rel = Str::after($file, $docroot);
                    $rel = ltrim($rel, '/');
                    $screenshot = rtrim($demoUrl, '/') . '/' . $rel;
                    break 2;
                }
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
