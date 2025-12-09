<?php

namespace App\Jobs;

use App\Models\Template;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClassifyTemplateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 120;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public int $maxExceptions = 2;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $slug
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $template = Template::where('slug', $this->slug)->first();

        if (!$template) {
            Log::warning("Template not found for classification: {$this->slug}");
            return;
        }

        // Skip if already classified and locked by human
        if ($template->locked_by_human) {
            Log::info("Template locked by human, skipping AI classification: {$this->slug}");
            return;
        }

        // Skip if recently classified with high confidence
        if ($template->last_classified_at &&
            $template->classification_confidence > 0.9 &&
            $template->last_classified_at->gt(now()->subDays(7))) {
            Log::info("Template recently classified with high confidence, skipping: {$this->slug}");
            return;
        }

        $n8nWebhookUrl = config('services.n8n.classification_webhook');
        if (!$n8nWebhookUrl) {
            Log::error('N8N webhook URL not configured');
            $this->fail(new \Exception('N8N webhook URL not configured'));
            return;
        }

        try {
            $response = Http::timeout(60)
                ->retry(2, 5000) // Retry twice with 5s delay
                ->post($n8nWebhookUrl, [
                    'slug' => $template->slug,
                    'name' => $template->name,
                    'demo_url' => $template->demo_url,
                    'screenshot_url' => $template->screenshot_url,
                    'active_theme' => $template->active_theme,
                    'plugins' => $template->plugins,
                    'api_callback_url' => route('api.templates.classification.update', $template->slug),
                    'api_token' => config('services.api.token'),
                ]);

            if ($response->successful()) {
                Log::info("Successfully triggered n8n classification for template: {$this->slug}");
            } else {
                Log::error("Failed to trigger n8n classification for template: {$this->slug}", [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                $this->fail(new \Exception('N8N webhook returned non-success status: ' . $response->status()));
            }
        } catch (\Exception $e) {
            Log::error("Exception triggering n8n classification for template: {$this->slug}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e; // Re-throw to trigger retry mechanism
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Classification job failed permanently for template: {$this->slug}", [
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        // Optionally mark template as needing review
        Template::where('slug', $this->slug)->update([
            'needs_review' => true,
        ]);
    }
}
