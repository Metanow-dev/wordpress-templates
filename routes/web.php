<?php

use Illuminate\Support\Facades\Route;

// Redirect bare root to EN listing
Route::get('/', fn () => redirect('/en/templates'));


Route::get('/media/{slug}/{path}', function (string $slug, string $path) {
    // only allow simple slug format
    abort_unless(preg_match('/^[a-z0-9\-]+$/i', $slug), 404);

    $base = rtrim(config('templates.root'), '/');
    // We support "<slug>/public", "<slug>/httpdocs" (Plesk), and "<slug>" as docroot
    $docroots = [
        "$base/$slug/public",
        "$base/$slug/httpdocs", 
        "$base/$slug"
    ];
    $docroot = collect($docroots)->first(fn($p) => is_file($p . '/wp-config.php'));
    
    abort_unless($docroot, 404);

    // Normalize and prevent traversal
    $full = realpath($docroot . '/' . $path);
    abort_unless($full !== false && str_starts_with($full, realpath($docroot) . DIRECTORY_SEPARATOR), 404);

    abort_unless(is_file($full) && is_readable($full), 404);

    return response()->file($full);
})->where('path', '.*')->name('media.file');


// EN
Route::prefix('en')->middleware('set.locale')->group(function () {
    // Allow crawling of main gallery page
    Route::get('templates', fn () => view('templates.index'))->name('templates.index');
    // Block crawling of individual template detail pages
    Route::get('templates/{slug}', fn ($slug) => response("DETAIL locale=en slug=$slug")->header('X-Robots-Tag', 'noindex, nofollow, noarchive, nosnippet'))->name('templates.show');
});

// DE
Route::prefix('de')->middleware('set.locale')->group(function () {
    // Allow crawling of main gallery page
    Route::get('vorlagen', fn () => view('templates.index'))->name('templates.index.de');
    // Block crawling of individual template detail pages
    Route::get('vorlagen/{slug}', fn ($slug) => response("DETAIL locale=de slug=$slug")->header('X-Robots-Tag', 'noindex, nofollow, noarchive, nosnippet'))->name('templates.show.de');
});

