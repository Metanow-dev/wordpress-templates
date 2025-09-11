<?php

namespace App\Helpers;

class CategoryHelper
{
    /**
     * Get category name in specified language
     */
    public static function getCategoryName(string $categoryKey, string $locale = 'en'): string
    {
        $categories = config('catalog.categories');
        
        if (!isset($categories[$categoryKey])) {
            return $categoryKey;
        }
        
        $translations = $categories[$categoryKey];
        
        // Try config first, then Laravel translation files, then fallback
        return $translations[$locale] 
            ?? $translations['en'] 
            ?? __("categories.{$categoryKey}", [], $locale)
            ?? ucfirst(str_replace('_', ' ', $categoryKey));
    }
    
    /**
     * Get all categories with translations for a specific locale
     */
    public static function getCategoriesForLocale(string $locale = 'en'): array
    {
        $categories = config('catalog.categories');
        $result = [];
        
        foreach ($categories as $key => $translations) {
            $result[$key] = $translations[$locale] ?? $translations['en'] ?? $key;
        }
        
        return $result;
    }
    
    /**
     * Get category options for select dropdowns
     */
    public static function getCategoryOptions(string $locale = 'en'): array
    {
        $categories = self::getCategoriesForLocale($locale);
        $options = [];
        
        foreach ($categories as $key => $name) {
            $options[] = [
                'value' => $key,
                'label' => $name
            ];
        }
        
        return $options;
    }
}