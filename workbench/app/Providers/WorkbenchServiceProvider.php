<?php

namespace Workbench\App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Workbench\App\Docs\BuildDocsCommand;

/**
 * The workbench gallery (composer serve): every page renders sample data only and
 * there are no users, so every ability the demo table asks for is granted.
 */
class WorkbenchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // /files: one local test disk for Livewire's temporary uploads and the chunk endpoint.
        config([
            'filesystems.disks.kit-test' => ['driver' => 'local', 'root' => storage_path('app/kit-test'), 'throw' => false],
            'livewire.temporary_file_upload.disk' => 'kit-test',
            'tall-ui-kit.chunked_uploads.disk' => 'kit-test',
            'tall-ui-kit.chunked_uploads.chunk_size' => 256 * 1024,
            'tall-ui-kit.chunked_uploads.max_size' => 200 * 1024 * 1024,
        ]);
    }

    public function boot(): void
    {
        Gate::before(fn () => true);
        // <x-workbench::gallery.layout> for the static gallery pages.
        Blade::anonymousComponentPath(__DIR__.'/../../resources/views', 'workbench');

        if ($this->app->runningInConsole()) {
            $this->commands([BuildDocsCommand::class]);
        }
    }
}
