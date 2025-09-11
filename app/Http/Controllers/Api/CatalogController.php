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
        return response()->json([
            'categories' => config('catalog.categories'),
            'tags' => config('catalog.tags'),
            'locales' => config('catalog.locales'),
        ]);
    }
}