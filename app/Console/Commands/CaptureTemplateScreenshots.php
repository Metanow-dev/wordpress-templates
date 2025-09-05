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

        $count = 0;
        $q->lazy()->each(function (Template $t) use (&$count, $w, $h, $full, $force) {
            if (!$t->demo_url) {
                $this->warn("{$t->slug}: no demo_url, skipping");
                return;
            }
            if ($t->screenshot_url && !$force) {
                $this->line("{$t->slug}: screenshot exists, skip (use --force to recapture)");
                return;
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
