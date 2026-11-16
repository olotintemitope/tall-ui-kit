<?php

use Illuminate\Support\Facades\Route;
use TallUiKit\Uploads\ChunkedUploads;
use Workbench\App\Docs\DocsController;
use Workbench\App\Files\DemoFileController;
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

    foreach (['/' => 'index', '/forms' => 'forms', '/overlays' => 'overlays', '/data' => 'data', '/charts' => 'charts', '/sidebar' => 'sidebar', '/checklist' => 'checklist', '/dates' => 'dates', '/chat' => 'chat', '/community' => 'community', '/files' => 'files'] as $uri => $page) {
        Route::get($uri, fn () => view('gallery.'.$page))->name('gallery.'.$page);
    }

    Route::get('/workflow', fn () => view('gallery.workflow'))->name('gallery.workflow');

    Route::get('/tables', TableGallery::class)->name('gallery.tables');

    // /files: generated bodies for the demo drive (previews and downloads).
    Route::get('/files-demo/{id}', DemoFileController::class)->where('id', '[a-z0-9-]+')->name('files-demo.file');

    // The docs site (composer docs:build renders these routes to site/).
    Route::prefix('docs')->group(function () {
        Route::get('/', [DocsController::class, 'guide'])->name('docs.index');
        Route::get('/search.js', [DocsController::class, 'search'])->name('docs.search');
        Route::get('/components', [DocsController::class, 'components'])->name('docs.components');
        Route::get('/components/{slug}', [DocsController::class, 'component'])->where('slug', '[a-z0-9-]+')->name('docs.component');
        Route::get('/patterns/{slug}', [DocsController::class, 'pattern'])->where('slug', '[a-z0-9-]+')->name('docs.pattern');
        Route::get('/{page}', [DocsController::class, 'guide'])->where('page', '[a-z0-9-]+')->name('docs.guide');
    });
});

/*
 * x-ui.file `chunked` on /files: the kit's chunk endpoint on the workbench test disk. The
 * workbench has no users, so uploads belong to the session (an app keeps 'auth').
 */
ChunkedUploads::routes('workbench/uploads', ['web']);
