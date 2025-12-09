<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CatalogController extends Controller
{
    /**
     * Get available categories and tags for AI classification
     */
    public function index(): JsonResponse
    {
        $categories = config('catalog.categories');
        
        return response()->json([
            'categories' => array_keys($categories), // Just the keys for n8n
            'categories_translations' => $categories, // Full translations
            'tags' => config('catalog.tags'),
            'locales' => config('catalog.locales'),
        ]);
    }
}