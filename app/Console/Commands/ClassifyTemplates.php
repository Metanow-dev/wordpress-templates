<?php

namespace App\Console\Commands;

use App\Models\Template;
use App\Services\WordPressAnalyzer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Finder\Finder;

class ClassifyTemplates extends Command
{
    protected $signature = 'templates:classify 
                            {slug? : Specific template slug to classify}
                            {--all : Classify all templates}
                            {--force : Force re-classification of already classified templates}
                            {--limit=0 : Limit number of templates to process}';

    protected $description = 'Trigger n8n AI classification for WordPress templates';

    public function handle(): int
    {
        $slug = $this->argument('slug');
        $all = $this->option('all');
        $force = $this->option('force');
        $limit = (int)$this->option('limit');

        if (!$slug && !$all) {
            $this->error('Either provide a specific slug or use --all flag');
            return self::FAILURE;
        }

        // Check n8n configuration
        $webhookUrl = config('services.n8n.classification_webhook');
        $apiToken = config('services.api.token');
        
        if (!$webhookUrl || !$apiToken) {
            $this->error('N8N webhook URL or API token not configured');
            $this->line('Set N8N_CLASSIFICATION_WEBHOOK_URL and API_TOKEN in your .env file');
            return self::FAILURE;
        }

        $this->info("🤖 Triggering AI Classification with n8n");
        $this->line("Webhook: {$webhookUrl}");
        $this->newLine();

        if ($slug) {
            return $this->classifySingle($slug);
        } else {
            return $this->classifyMultiple($force, $limit);
        }
    }

    private function classifySingle(string $slug): int
    {
        $template = Template::where('slug', $slug)->first();
        
        if (!$template) {
            $this->error("Template '{$slug}' not found");
            return self::FAILURE;
        }

        $this->info("🎯 Classifying single template: {$template->slug}");
        
        $docroot = $this->findDocroot($template->slug);
        if (!$docroot) {
            $this->error("WordPress docroot not found for {$template->slug}");
            return self::FAILURE;
        }

        $success = $this->triggerClassification($template, $docroot);
        
        if ($success) {
            $this->info("✅ Classification triggered successfully for {$template->slug}");
            return self::SUCCESS;
        } else {
            $this->error("❌ Failed to trigger classification for {$template->slug}");
            return self::FAILURE;
        }
    }

    private function classifyMultiple(bool $force, int $limit): int
    {
        $query = Template::query();
        
        if (!$force) {
            // Only classify unclassified or low-confidence templates
            $query->where(function($q) {
                $q->whereNull('primary_category')
                  ->orWhere('classification_confidence', '<', 0.7);
            });
        }
        
        // Exclude human-locked templates
        $query->where('locked_by_human', false);
        
        if ($limit > 0) {
            $query->limit($limit);
        }

        $templates = $query->get();
        
        if ($templates->isEmpty()) {
            $this->info('No templates found for classification');
            return self::SUCCESS;
        }

        $this->info("🚀 Classifying {$templates->count()} templates");
        
        $successful = 0;
        $failed = 0;
        
        foreach ($templates as $template) {
            $this->line("Processing: {$template->slug}");
            
            $docroot = $this->findDocroot($template->slug);
            if (!$docroot) {
                $this->warn("  ⚠️  WordPress docroot not found, skipping");
                $failed++;
                continue;
            }

            if ($this->triggerClassification($template, $docroot)) {
                $this->info("  ✅ Classification triggered");
                $successful++;
            } else {
                $this->warn("  ❌ Classification failed");
                $failed++;
            }

            // Small delay to avoid overwhelming n8n
            sleep(1);
        }

        $this->newLine();
        $this->info("📊 Summary:");
        $this->line("  • Successful: {$successful}");
        $this->line("  • Failed: {$failed}");
        $this->line("  • Total: " . ($successful + $failed));

        return $successful > 0 ? self::SUCCESS : self::FAILURE;
    }

    private function triggerClassification(Template $template, string $docroot): bool
    {
        try {
            $webhookUrl = config('services.n8n.classification_webhook');
            $apiToken = config('services.api.token');

            // Get enhanced WordPress data
            $wordpressData = WordPressAnalyzer::analyze($docroot, $template->slug, $template->demo_url);

            $response = Http::timeout(15)->post($webhookUrl, [
                'slug' => $template->slug,
                'name' => $template->name,
                'demo_url' => $template->demo_url,
                'screenshot_url' => $template->screenshot_url,
                'wordpress_data' => $wordpressData,
                'api_callback_url' => url("/api/templates/{$template->slug}/classification"),
                'api_catalog_url' => url("/api/catalog"),
                'api_token' => $apiToken,
            ]);

            return $response->successful();

        } catch (\Exception $e) {
            $this->warn("  Exception: " . $e->getMessage());
            return false;
        }
    }

    private function findDocroot(string $slug): ?string
    {
        $root = config('templates.root');
        if (!is_dir($root)) {
            return null;
        }

        $base = $root . '/' . $slug;
        if (!is_dir($base)) {
            return null;
        }

        // Check common docroot locations
        $docroots = ["{$base}/public", "{$base}/httpdocs", $base];
        
        foreach ($docroots as $docroot) {
            if (is_file($docroot . '/wp-config.php')) {
                return $docroot;
            }
        }

        return null;
    }
}