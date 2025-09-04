<?php

use Illuminate\Support\Facades\Route;

// Redirect bare root to EN listing
Route::get('/', fn () => redirect('/en/templates'));

// EN group
Route::prefix('en')->middleware('set.locale')->group(function () {
    Route::get('templates', fn () => 'OK locale=en path='.request()->path())->name('templates.index');
    Route::get('templates/{slug}', fn ($slug) => "DETAIL locale=en slug=$slug")->name('templates.show');
});

// DE group
Route::prefix('de')->middleware('set.locale')->group(function () {
    Route::get('vorlagen', fn () => 'OK locale=de path='.request()->path())->name('templates.index.de');
    Route::get('vorlagen/{slug}', fn ($slug) => "DETAIL locale=de slug=$slug")->name('templates.show.de');
});
