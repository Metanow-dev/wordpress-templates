<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Support\Screenshotter;

class GenerateScreenshotVariants extends Command
{
    protected $signature = 'templates:screenshot-variants {--slug=} {--all}';
    protected $description = 'Generate WebP responsive variants (-480/-768/-1024) for existing screenshots';

    public function handle(): int
    {
        $slug = $this->option('slug');
        $all  = (bool)$this->option('all');

        $base = storage_path('app/public/screenshots');
        if (!is_dir($base)) {
            $this->error('Screenshots directory not found: '.$base);
            return self::FAILURE;
        }

        $slugs = [];
        if ($slug) {
            $slugs = [ $slug ];
        } elseif ($all) {
            foreach (File::files($base) as $file) {
                if ($file->getExtension() === 'png') {
                    $slugs[] = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                }
            }
        } else {
            $this->warn('Nothing to do. Pass --slug=example or --all');
            return self::SUCCESS;
        }

        $count = 0;
        foreach ($slugs as $s) {
            $png = $base.'/'.$s.'.png';
            if (!is_file($png)) {
                $this->line("$s: base PNG missing, skipping");
                continue;
            }
            try {
                Screenshotter::for($s, '#')->generateVariants();
                $this->line("$s: variants generated");
                $count++;
            } catch (\Throwable $e) {
                $this->error("$s: failed generating variants: ".$e->getMessage());
            }
        }

        $this->info("Done. Generated variants for {$count} screenshot(s).");
        return self::SUCCESS;
    }
}

