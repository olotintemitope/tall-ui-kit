<?php

namespace Workbench\App\Docs;

use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

/**
 * `composer docs:build`: renders every docs route of the workbench to static HTML in
 * site/ (relative links, so it works under any sub-path), copies the built theme CSS,
 * the kit JS and Livewire's script (which carries Alpine), and writes the search
 * index. No network: the assets come from `npm run build` and vendor/.
 */
final class BuildDocsCommand extends Command
{
    protected $signature = 'docs:build {--out= : Output directory (default: site/ in the package root)} {--only= : Comma-separated site paths to render, e.g. components/button}';

    protected $description = 'Render the docs site to static HTML';

    public function handle(Kernel $kernel): int
    {
        $root = dirname(__DIR__, 3);
        $out = rtrim($this->option('out') ?: $root.'/site', '/');
        $dist = $root.'/workbench/dist';

        foreach (['neutral.css', 'newbanger.css', 'gallery.js', 'docs.js'] as $asset) {
            if (! is_file($dist.'/'.$asset)) {
                $this->error("workbench/dist/{$asset} is missing: run `npm run build` first.");

                return self::FAILURE;
            }
        }

        $paths = self::paths();
        if ($only = $this->option('only')) {
            $paths = array_values(array_intersect($paths, explode(',', $only)));
        } else {
            File::deleteDirectory($out);
        }
        File::ensureDirectoryExists($out.'/assets');

        // Assets: every file Vite built (chunks included), plus Livewire's script.
        foreach (File::files($dist) as $file) {
            File::copy($file->getPathname(), $out.'/assets/'.$file->getFilename());
        }
        File::copy($root.'/vendor/livewire/livewire/dist/livewire.min.js', $out.'/assets/livewire.min.js');
        File::put($out.'/assets/docs-search.js', SearchIndex::script());
        File::put($out.'/.nojekyll', '');

        Site::$static = true;
        $failed = 0;
        foreach ($paths as $path) {
            Site::$current = $path;
            $uri = '/docs'.($path === 'index' ? '' : '/'.preg_replace('#/index$#', '', $path));
            $response = $kernel->handle($request = Request::create($uri));
            $kernel->terminate($request, $response);

            if ($response->getStatusCode() !== 200) {
                $this->error("{$uri}: HTTP {$response->getStatusCode()}");
                $failed++;

                continue;
            }

            $html = $this->finish((string) $response->getContent(), $path);
            File::ensureDirectoryExists(dirname($out.'/'.$path.'.html'));
            File::put($out.'/'.$path.'.html', $html);
            $this->line(sprintf('  %-34s %6.1f KB', $path.'.html', strlen($html) / 1024));
        }
        Site::$static = false;

        $this->info(sprintf('%d pages in %s', count($paths) - $failed, $out));

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    /** @return list<string> Every page of the site, as paths without extension. */
    public static function paths(): array
    {
        $paths = array_keys(Catalog::GUIDES);
        $paths[] = 'components/index';
        foreach (array_keys(Catalog::components()) as $slug) {
            $paths[] = 'components/'.$slug;
        }
        foreach (array_keys(Catalog::PATTERNS) as $slug) {
            $paths[] = 'patterns/'.$slug;
        }

        return $paths;
    }

    /** Point Livewire's script at the copied file and drop server-only bits. */
    private function finish(string $html, string $path): string
    {
        $prefix = str_repeat('../', substr_count($path, '/'));
        $html = preg_replace('#<script src="[^"]*/livewire[^"]*\.js[^"]*"#', '<script src="'.$prefix.'assets/livewire.min.js"', $html);
        // Outside a real request @livewireScripts can print nothing; Livewire's script carries Alpine, so make sure it is there once.
        if (! str_contains($html, 'assets/livewire.min.js')) {
            $html = str_replace('</body>', '<script src="'.$prefix.'assets/livewire.min.js" data-navigate-once="true"></script>'."\n</body>", $html);
        }
        $html = preg_replace('#\sdata-csrf="[^"]*"#', '', $html);
        $html = preg_replace('#<meta name="csrf-token"[^>]*>#', '', $html);

        if (preg_match('#(href|src|action)="https?://(localhost|127\.0\.0\.1)[^"]*"#', $html, $m)) {
            $this->warn("  {$path}: absolute local URL left in the page: {$m[0]}");
        }

        return $html;
    }
}
