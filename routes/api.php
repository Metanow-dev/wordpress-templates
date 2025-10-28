<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TemplateClassificationController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\HealthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Template Classification API (for n8n integration)
Route::middleware(['api.token', 'rate.limit:api', 'request.size.limit'])->group(function () {
    // Get catalog configuration (categories, tags, locales)
    Route::get('/catalog', [CatalogController::class, 'index'])
        ->name('api.catalog.index');

    // Get template data for AI classification
    Route::get('/templates/{slug}/classification', [TemplateClassificationController::class, 'getTemplateForClassification'])
        ->name('api.templates.classification.get');

    // Update template classification from AI
    Route::post('/templates/{slug}/classification', [TemplateClassificationController::class, 'updateClassification'])
        ->name('api.templates.classification.update');

    // Trigger n8n classification workflow (heavy operation - stricter rate limit)
    Route::post('/templates/{slug}/classify', [TemplateClassificationController::class, 'triggerClassification'])
        ->middleware('rate.limit:heavy')
        ->name('api.templates.classification.trigger');

    // Get classification statistics
    Route::get('/templates/classification/stats', [TemplateClassificationController::class, 'getStats'])
        ->name('api.templates.classification.stats');
});

// Health check endpoints (no auth required)
Route::get('/health', [HealthController::class, 'simple'])->name('api.health.simple');
Route::get('/health/detailed', [HealthController::class, 'check'])->name('api.health.detailed');