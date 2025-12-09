<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TemplateClassificationController extends Controller
{
    /**
     * Get template data for AI classification
     */
    public function getTemplateForClassification(Request $request, string $slug): JsonResponse
    {
        $template = Template::where('slug', $slug)->firstOrFail();
        
        return response()->json([
            'slug' => $template->slug,
            'name' => $template->name,
            'demo_url' => $template->demo_url,
            'screenshot_url' => $template->screenshot_url,
            'active_theme' => $template->active_theme,
            'plugins' => $template->plugins,
            'language' => $template->language,
            'existing_category' => $template->primary_category,
            'existing_tags' => $template->tags,
            'locked_by_human' => $template->locked_by_human,
            'available_categories' => config('catalog.categories'),
            'available_tags' => config('catalog.tags'),
        ]);
    }

    /**
     * Update template classification from AI analysis
     */
    public function updateClassification(Request $request, string $slug): JsonResponse
    {
        try {
            $template = Template::where('slug', $slug)->firstOrFail();
            
            // Don't override human classifications unless forced
            if ($template->locked_by_human && !$request->boolean('force_override')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template classification is locked by human review',
                    'locked_by_human' => true
                ]);
            }
            
            // Log the incoming request data for debugging
            Log::info("Classification update request for {$slug}", [
                'request_data' => $request->all(),
                'content_type' => $request->header('Content-Type')
            ]);
            
            $validated = $request->validate([
                'primary_category' => 'required|string|in:' . implode(',', array_keys(config('catalog.categories'))),
                'tags' => 'required|array',
                'tags.*' => 'string|in:' . implode(',', config('catalog.tags')),
                'confidence' => 'required|numeric|between:0,1',
                'rationale' => 'required|string|max:1000',
                'description_en' => 'nullable|string|max:500',
                'description_de' => 'nullable|string|max:500',
                'ai_generated_name' => 'nullable|string|max:100',
                'needs_review' => 'boolean',
            ]);
        
        $updateData = [
            'primary_category' => $validated['primary_category'],
            'tags' => $validated['tags'],
            'classification_confidence' => $validated['confidence'],
            'classification_source' => 'ai',
            'classification_rationale' => $validated['rationale'],
            'description_en' => $validated['description_en'] ?? null,
            'description_de' => $validated['description_de'] ?? null,
            'needs_review' => $validated['needs_review'] ?? ($validated['confidence'] < 0.8),
            'last_classified_at' => now(),
        ];
        
        // Update name if AI provided a better one
        if (!empty($validated['ai_generated_name'])) {
            $updateData['name'] = $validated['ai_generated_name'];
            $updateData['name_source'] = 'ai';
        }
        
        $template->update($updateData);
        
        Log::info("AI classified template: {$slug}", [
            'category' => $validated['primary_category'],
            'tags' => implode(', ', $validated['tags']),
            'tags_count' => count($validated['tags']),
            'confidence' => $validated['confidence'],
            'ai_generated_name' => $validated['ai_generated_name'] ?? null
        ]);
        
            return response()->json([
                'success' => true,
                'message' => 'Template classified successfully',
                'template' => [
                    'slug' => $template->slug,
                    'primary_category' => $template->primary_category,
                    'tags' => $template->tags,
                    'confidence' => $template->classification_confidence,
                    'needs_review' => $template->needs_review,
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error("Error updating template classification for {$slug}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating template classification: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Trigger n8n workflow for template classification
     */
    public function triggerClassification(string $slug): JsonResponse
    {
        $template = Template::where('slug', $slug)->firstOrFail();
        
        // Skip if already classified and locked by human
        if ($template->locked_by_human) {
            return response()->json([
                'success' => false,
                'message' => 'Template locked by human, skipping AI classification'
            ]);
        }
        
        // Skip if recently classified with high confidence
        if ($template->last_classified_at && 
            $template->classification_confidence > 0.9 && 
            $template->last_classified_at->gt(now()->subDays(7))) {
            return response()->json([
                'success' => false,
                'message' => 'Template recently classified with high confidence'
            ]);
        }
        
        $n8nWebhookUrl = config('services.n8n.classification_webhook');
        if (!$n8nWebhookUrl) {
            return response()->json([
                'success' => false,
                'message' => 'N8N webhook URL not configured'
            ], 500);
        }
        
        try {
            $response = Http::timeout(30)->post($n8nWebhookUrl, [
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
                Log::info("Triggered n8n classification for template: {$slug}");
                return response()->json([
                    'success' => true,
                    'message' => 'Classification workflow triggered successfully'
                ]);
            } else {
                Log::error("Failed to trigger n8n classification for template: {$slug}", [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to trigger classification workflow'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error("Exception triggering n8n classification for template: {$slug}", [
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error triggering classification workflow'
            ], 500);
        }
    }
    
    /**
     * Get classification statistics
     * Optimized to use single query with aggregations to prevent memory issues
     */
    public function getStats(): JsonResponse
    {
        // Use a single aggregated query instead of multiple count queries
        $stats = \DB::select("
            SELECT
                COUNT(*) as total_templates,
                COUNT(primary_category) as classified_templates,
                SUM(CASE WHEN classification_source = 'ai' THEN 1 ELSE 0 END) as ai_classified,
                SUM(CASE WHEN classification_source = 'human' THEN 1 ELSE 0 END) as human_classified,
                SUM(CASE WHEN needs_review = 1 THEN 1 ELSE 0 END) as needs_review,
                SUM(CASE WHEN classification_confidence > 0.8 THEN 1 ELSE 0 END) as high_confidence
            FROM templates
        ")[0];

        // Get category distribution with limit to prevent memory issues
        $categoriesDistribution = Template::whereNotNull('primary_category')
            ->selectRaw('primary_category, COUNT(*) as count')
            ->groupBy('primary_category')
            ->orderBy('count', 'desc')
            ->limit(50) // Limit to top 50 categories
            ->pluck('count', 'primary_category');

        return response()->json([
            'total_templates' => (int) $stats->total_templates,
            'classified_templates' => (int) $stats->classified_templates,
            'ai_classified' => (int) $stats->ai_classified,
            'human_classified' => (int) $stats->human_classified,
            'needs_review' => (int) $stats->needs_review,
            'high_confidence' => (int) $stats->high_confidence,
            'categories_distribution' => $categoriesDistribution,
            'available_categories' => config('catalog.categories'),
            'available_tags' => config('catalog.tags'),
        ]);
    }
}