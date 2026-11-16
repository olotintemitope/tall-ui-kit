<?php

namespace Workbench\App\Docs;

/**
 * Links for the docs site in its two modes. Served (`composer serve`, /docs/...):
 * absolute workbench URLs. Static (`composer docs:build`, site/...): relative paths
 * from the page being written, so the site works from any sub-path (GitHub Pages
 * serves it under /<repo>/) and from a plain static server.
 */
final class Site
{
    public static bool $static = false;

    /** The page being rendered, as a site path without extension: `components/button`. */
    public static string $current = 'index';

    /** A site path (`index`, `tokens`, `components/button`, `patterns/forms`) as a link. */
    public static function url(string $path, ?string $fragment = null): string
    {
        $hash = $fragment ? '#'.$fragment : '';
        if (! self::$static) {
            return url('/docs'.($path === 'index' ? '' : '/'.$path)).$hash;
        }

        return self::prefix().$path.'.html'.$hash;
    }

    /** A built asset: workbench/dist in served mode, site/assets in the static site. */
    public static function asset(string $file): string
    {
        if (! self::$static) {
            return $file === 'docs-search.js' ? url('/docs/search.js') : route('kit-assets', $file);
        }

        return self::prefix().'assets/'.$file;
    }

    public static function is(string $path): bool
    {
        return self::$current === $path;
    }

    private static function prefix(): string
    {
        return str_repeat('../', substr_count(self::$current, '/'));
    }
}
