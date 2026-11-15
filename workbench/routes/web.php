<?php

use Illuminate\Support\Facades\Route;
use Workbench\App\Livewire\TableGallery;

/*
 * Workbench gallery (composer serve, then http://127.0.0.1:8000). Pages show the kit
 * on sample data. ?theme=newbanger (or neutral) switches the theme for the session.
 * Build the assets first: npm install && npm run build.
 */

Route::middleware('web')->group(function () {
    Route::get('/kit-assets/{file}', function (string $file) {
        $path = realpath(__DIR__.'/../dist/'.$file);
        abort_unless($path && str_starts_with($path, realpath(__DIR__.'/../dist')), 404);

        return response()->file($path, ['Content-Type' => str_ends_with($file, '.css') ? 'text/css' : 'text/javascript']);
    })->where('file', '[A-Za-z0-9._-]+')->name('kit-assets');

    foreach (['/' => 'index', '/forms' => 'forms', '/overlays' => 'overlays', '/data' => 'data', '/charts' => 'charts', '/sidebar' => 'sidebar'] as $uri => $page) {
        Route::get($uri, fn () => view('gallery.'.$page))->name('gallery.'.$page);
    }

    Route::get('/tables', TableGallery::class)->name('gallery.tables');
});
