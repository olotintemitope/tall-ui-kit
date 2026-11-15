<?php

namespace TallUiKit\Support;

use BladeUI\Icons\Exceptions\SvgNotFound;
use BladeUI\Icons\Factory;

/**
 * Lucide icons for x-ui.icon, from mallardduck/blade-lucide-icons (ISC icons,
 * https://lucide.dev). The package is pinned at 1.23.0, which ships Lucide 0.412.0:
 * the release closest to the React app's lucide-react@0.436.0, and every icon the
 * Blade views use is drawn exactly as in React (checked node for node) apart from
 * the two chart icons below. Later package releases redraw dozens of them.
 *
 * Lucide renamed the chart icons between 0.412 and 0.436 (bar-chart-* and
 * line-chart became chart-*), so views use the React names and this map points
 * them at the package's older files. chart-line and chart-column differ from
 * React only in the axis corner (square here, 2px rounded in 0.436); the other
 * aliases are drawn identically.
 *
 * docs/ui-kit-copy-icons.md has the upgrade notes and `php artisan icons:cache`.
 */
class LucideIcons
{
    /** The Lucide release the installed package ships. */
    public const LUCIDE_VERSION = '0.412.0';

    /** React (lucide-react 0.436.0) name => package name. */
    public const ALIASES = [
        'chart-line' => 'line-chart',
        'chart-column' => 'bar-chart-3',
        'chart-no-axes-column' => 'bar-chart-2',
        'chart-no-axes-column-increasing' => 'bar-chart',
        'chart-no-axes-gantt' => 'gantt-chart',
        'file-chart-column' => 'file-bar-chart-2',
        'file-chart-column-increasing' => 'file-bar-chart',
        'file-chart-line' => 'file-line-chart',
        'square-chart-gantt' => 'gantt-chart-square',
    ];

    /** @var array<string, string> Inner markup per resolved name, for this request. */
    private static array $inner = [];

    public static function resolve(string $name): string
    {
        return self::ALIASES[$name] ?? $name;
    }

    public static function has(string $name): bool
    {
        try {
            app(Factory::class)->svg('lucide-'.self::resolve($name));

            return true;
        } catch (SvgNotFound) {
            return false;
        }
    }

    /**
     * The icon's child elements (paths, circles, lines), without the package's own
     * <svg> wrapper, so x-ui.icon draws one consistent root element. An unknown name
     * throws in local and testing and draws nothing elsewhere.
     */
    public static function inner(string $name): string
    {
        $resolved = self::resolve($name);

        if (isset(self::$inner[$resolved])) {
            return self::$inner[$resolved];
        }

        try {
            $contents = app(Factory::class)->svg('lucide-'.$resolved)->contents();
        } catch (SvgNotFound $e) {
            if (app()->environment('local', 'testing')) {
                throw $e;
            }

            return '';
        }

        return self::$inner[$resolved] = trim((string) preg_replace(['/^\s*<svg\b[^>]*>/', '/<\/svg>\s*$/', '/>\s+</'], ['', '', '><'], $contents));
    }
}
