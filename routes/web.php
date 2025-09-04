<?php

use Illuminate\Support\Facades\Route;

// Redirect bare root to EN listing
Route::get('/', fn () => redirect('/en/templates'));


Route::get('/media/{slug}/{path}', function (string $slug, string $path) {
    // only allow simple slug format
    abort_unless(preg_match('/^[a-z0-9\-]+$/i', $slug), 404);

    $base = rtrim(config('templates.root'), '/');
    // We support both "<slug>/public" and "<slug>" as docroot
    $docroot = is_file("$base/$slug/public/wp-config.php")
        ? "$base/$slug/public"
        : "$base/$slug";

    // Normalize and prevent traversal
    $full = realpath($docroot . '/' . $path);
    abort_unless($full !== false && str_starts_with($full, realpath($docroot) . DIRECTORY_SEPARATOR), 404);

    abort_unless(is_file($full) && is_readable($full), 404);

    return response()->file($full);
})->where('path', '.*')->name('media.file');


// EN
Route::prefix('en')->middleware('set.locale')->group(function () {
    Route::get('templates', fn () => view('templates.index'))->name('templates.index');
    Route::get('templates/{slug}', fn ($slug) => "DETAIL locale=en slug=$slug")->name('templates.show');
});

// DE
Route::prefix('de')->middleware('set.locale')->group(function () {
    Route::get('vorlagen', fn () => view('templates.index'))->name('templates.index.de');
    Route::get('vorlagen/{slug}', fn ($slug) => "DETAIL locale=de slug=$slug")->name('templates.show.de');
});

