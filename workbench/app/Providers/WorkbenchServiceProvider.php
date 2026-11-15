<?php

namespace Workbench\App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * The workbench gallery (composer serve): every page renders sample data only and
 * there are no users, so every ability the demo table asks for is granted.
 */
class WorkbenchServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::before(fn () => true);
        // <x-workbench::gallery.layout> for the static gallery pages.
        Blade::anonymousComponentPath(__DIR__.'/../../resources/views', 'workbench');
    }
}
