<?php

namespace TallUiKit;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * Registers the kit's anonymous Blade components under their usual names
 * (`x-ui.button`, `x-data-table`, ...), the config, and the publishable views,
 * config, CSS and JS. Auto-discovered through composer.json `extra.laravel`.
 */
class TallUiKitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/tall-ui-kit.php', 'tall-ui-kit');

        // x-ui.checklist persist="server": session by default; an app's own binding wins.
        $this->app->bindIf(Support\Checklist\ChecklistStore::class, Support\Checklist\SessionChecklistStore::class);
        // x-ui.banner persist="server": the checklist's store by default, so one per-user binding covers both.
        $this->app->bindIf(Support\Dismiss\DismissStore::class, fn ($app) => $app->make(Support\Checklist\ChecklistStore::class));
    }

    public function boot(): void
    {
        $views = __DIR__.'/../resources/views';

        // A published copy (resources/views/vendor/tall-ui-kit/components) wins over the package's.
        $published = resource_path('views/vendor/tall-ui-kit/components');
        if (is_dir($published)) {
            Blade::anonymousComponentPath($published);
        }

        // Root anonymous path: <x-ui.button> resolves to components/ui/button.blade.php.
        Blade::anonymousComponentPath($views.'/components');
        $this->loadViewsFrom($views, 'tall-ui-kit');

        if (config('tall-ui-kit.pagination', true)) {
            // Plain paginators and Livewire's WithPagination both render x-ui.pagination.
            Paginator::defaultView('tall-ui-kit::components.ui.pagination');
            Paginator::defaultSimpleView('tall-ui-kit::components.ui.pagination');
            View::prependNamespace('livewire', $views.'/livewire');
        }

        if (config('tall-ui-kit.progress_bar', true)) {
            // x-ui.progress-bar replaces Livewire's own wire:navigate bar.
            config(['livewire.navigate.show_progress_bar' => false]);
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/tall-ui-kit.php' => config_path('tall-ui-kit.php'),
            ], 'tall-ui-kit-config');

            $this->publishes([
                $views.'/components' => resource_path('views/vendor/tall-ui-kit/components'),
            ], 'tall-ui-kit-views');

            $this->publishes([
                __DIR__.'/../resources/css' => resource_path('css/vendor/tall-ui-kit'),
                __DIR__.'/../resources/js' => resource_path('js/vendor/tall-ui-kit'),
            ], 'tall-ui-kit-assets');
        }
    }
}
