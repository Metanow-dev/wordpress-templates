<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TemplateClassificationController;
use App\Http\Controllers\Api\CatalogController;

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
Route::middleware(['api.token'])->group(function () {
    // Get catalog configuration (categories, tags, locales)
    Route::get('/catalog', [CatalogController::class, 'index'])
        ->name('api.catalog.index');
    
    // Get template data for AI classification
    Route::get('/templates/{slug}/classification', [TemplateClassificationController::class, 'getTemplateForClassification'])
        ->name('api.templates.classification.get');
    
    // Update template classification from AI
    Route::post('/templates/{slug}/classification', [TemplateClassificationController::class, 'updateClassification'])
        ->name('api.templates.classification.update');
    
    // Trigger n8n classification workflow
    Route::post('/templates/{slug}/classify', [TemplateClassificationController::class, 'triggerClassification'])
        ->name('api.templates.classification.trigger');
    
    // Get classification statistics
    Route::get('/templates/classification/stats', [TemplateClassificationController::class, 'getStats'])
        ->name('api.templates.classification.stats');
});

// Health check endpoint (no auth required)
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'app' => config('app.name'),
        'version' => '1.0.0'
    ]);
});