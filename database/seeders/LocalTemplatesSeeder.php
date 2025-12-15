<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class LocalTemplatesSeeder extends Seeder
{
    /**
     * Seed local template examples for development only.
     * These templates are loaded from storage/fixtures/templates
     */
    public function run(): void
    {
        // Only run in local environment
        if (!app()->environment('local')) {
            $this->command->warn('⚠️  LocalTemplatesSeeder only runs in local environment. Skipping...');
            return;
        }

        $this->command->info('🌱 Seeding local template examples...');

        $fixturesPath = storage_path('fixtures/templates');

        if (!File::exists($fixturesPath)) {
            $this->command->error('❌ Fixtures directory not found: ' . $fixturesPath);
            return;
        }

        $templateDirs = File::directories($fixturesPath);

        if (empty($templateDirs)) {
            $this->command->warn('⚠️  No template directories found in fixtures');
            return;
        }

        $templates = [
            [
                'slug' => 'hotel-classic',
                'name' => 'Hotel Classic',
                'demo_url' => 'http://localhost/hotel-classic',
                'description_en' => 'Elegant hotel website with booking system, room gallery, and amenities showcase. Perfect for boutique hotels and resorts.',
                'description_de' => 'Elegante Hotel-Website mit Buchungssystem, Zimmergalerie und Ausstattungsübersicht. Perfekt für Boutique-Hotels und Resorts.',
                'primary_category' => 'hospitality',
                'tags' => ['booking', 'responsive', 'gallery', 'contact_form', 'multilingual'],
            ],
            [
                'slug' => 'portfolio-creative',
                'name' => 'Portfolio Creative',
                'demo_url' => 'http://localhost/portfolio-creative',
                'description_en' => 'Modern portfolio template for designers and creative professionals. Features project showcase and smooth animations.',
                'description_de' => 'Modernes Portfolio-Template für Designer und Kreative. Mit Projekt-Showcase und flüssigen Animationen.',
                'primary_category' => 'portfolio',
                'tags' => ['portfolio', 'responsive', 'gallery', 'modern_design', 'animations'],
            ],
            [
                'slug' => 'real-estate-modern',
                'name' => 'Real Estate Modern',
                'demo_url' => 'http://localhost/real-estate-modern',
                'description_en' => 'Professional real estate website with property listings, search filters, and agent profiles.',
                'description_de' => 'Professionelle Immobilien-Website mit Objektlisten, Suchfiltern und Makler-Profilen.',
                'primary_category' => 'real_estate',
                'tags' => ['real_estate', 'responsive', 'search', 'contact_form', 'maps'],
            ],
            [
                'slug' => 'restaurant-deluxe',
                'name' => 'Restaurant Deluxe',
                'demo_url' => 'http://localhost/restaurant-deluxe',
                'description_en' => 'Sophisticated restaurant website with menu display, reservation system, and gallery.',
                'description_de' => 'Anspruchsvolle Restaurant-Website mit Menüanzeige, Reservierungssystem und Galerie.',
                'primary_category' => 'restaurant',
                'tags' => ['restaurant', 'responsive', 'menu', 'booking', 'gallery'],
            ],
        ];

        $count = 0;
        foreach ($templates as $templateData) {
            // Skip if directory doesn't exist
            $dirPath = $fixturesPath . '/' . $templateData['slug'];
            if (!File::exists($dirPath)) {
                continue;
            }

            // Check for screenshot in multiple possible locations
            $screenshotUrl = null;
            $publicPath = storage_path('app/public/screenshots');

            // Ensure screenshots directory exists
            if (!File::exists($publicPath)) {
                File::makeDirectory($publicPath, 0755, true);
            }

            // Check if screenshot already exists in public storage
            $publicScreenshot = $publicPath . '/' . $templateData['slug'] . '.png';

            // Try to find screenshot in fixture directory
            $possiblePaths = [
                $dirPath . '/screenshot.png',
                $dirPath . '/public/wp-content/themes/active/screenshot.png',
                $dirPath . '/public/screenshot.png',
            ];

            foreach ($possiblePaths as $path) {
                if (File::exists($path) && File::size($path) > 0) {
                    // Copy real screenshot
                    File::copy($path, $publicScreenshot);
                    $screenshotUrl = asset('storage/screenshots/' . $templateData['slug'] . '.png');
                    break;
                }
            }

            // If screenshot exists in public storage (even if we didn't find it in fixtures)
            if (File::exists($publicScreenshot) && File::size($publicScreenshot) > 0) {
                $screenshotUrl = asset('storage/screenshots/' . $templateData['slug'] . '.png');
            }

            // Create or update template
            Template::updateOrCreate(
                ['slug' => $templateData['slug']],
                [
                    'name' => $templateData['name'],
                    'name_source' => 'manual',
                    'demo_url' => $templateData['demo_url'],
                    'screenshot_url' => $screenshotUrl,
                    'language' => 'en',
                    'active_theme' => 'custom',
                    'description_en' => $templateData['description_en'],
                    'description_de' => $templateData['description_de'],
                    'auto_translated' => false,
                    'primary_category' => $templateData['primary_category'],
                    'tags' => $templateData['tags'],
                    'classification_confidence' => 1.00,
                    'classification_source' => 'human', // Must be 'ai', 'human', or 'manifest'
                    'locked_by_human' => true,
                    'needs_review' => false,
                    'last_scanned_at' => now(),
                    'last_classified_at' => now(),
                ]
            );

            $count++;
        }

        $this->command->info("✅ Successfully seeded {$count} local template examples");
    }
}
