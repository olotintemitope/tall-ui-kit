<?php

use BladeUI\Icons\Exceptions\SvgNotFound;
use Illuminate\Support\Facades\Blade;
use Symfony\Component\Finder\Finder;
use TallUiKit\Support\LucideIcons;

/*
| x-ui.icon on mallardduck/blade-lucide-icons, and every icon name the app uses.
*/

/**
 * Icon names written as literals in Blade views and PHP: x-ui.icon and
 * x-marketplace.icon `name="..."`, quoted literals inside their `:name="..."`
 * expressions, `icon="..."` props, `'icon' => '...'` option arrays and
 * `->icon('...')` data-table actions.
 *
 * @return array<string, list<string>> name => files that use it
 */
function iconNamesInUse(): array
{
    $files = Finder::create()->files()->in([kit_path('resources/views'), kit_path('src'), kit_path('workbench')])->name(['*.blade.php', '*.php']);
    $names = [];

    foreach ($files as $file) {
        $source = $file->getContents();
        $found = [];

        preg_match_all('/<x-(?:ui|marketplace)\.icon\b[^>]*?\sname="([a-z0-9-]+)"/', $source, $m);
        $found = [...$found, ...$m[1]];

        preg_match_all('/<x-(?:ui|marketplace)\.icon\b[^>]*?\s:name="([^"]+)"/', $source, $m);
        foreach ($m[1] as $expression) {
            preg_match_all("/'([a-z0-9-]+)'/", $expression, $literals);
            $found = [...$found, ...array_filter($literals[1], fn (string $l): bool => ! in_array($l, ['icon', 'key', 'name'], true))];
        }

        preg_match_all('/\sicon="([a-z0-9-]+)"/', $source, $m);
        $found = [...$found, ...$m[1]];

        preg_match_all("/'icon'\s*=>\s*'([a-z0-9-]+)'/", $source, $m);
        $found = [...$found, ...$m[1]];

        preg_match_all("/->icon\('([a-z0-9-]+)'\)/", $source, $m);
        $found = [...$found, ...$m[1]];

        foreach (array_unique($found) as $name) {
            $names[$name][] = str_replace(kit_path().'/', '', $file->getPathname());
        }
    }

    ksort($names);

    return $names;
}

it('finds the icon names used across the views', function () {
    $names = array_keys(iconNamesInUse());

    expect(count($names))->toBeGreaterThan(30)
        ->and($names)->toContain('check', 'x', 'chart-line', 'chart-column', 'layout-dashboard', 'trash-2', 'copy');
});

it('resolves every icon name the views and PHP use', function () {
    $missing = collect(iconNamesInUse())
        ->reject(fn (array $files, string $name): bool => LucideIcons::has($name))
        ->map(fn (array $files, string $name): string => $name.' ('.implode(', ', $files).')')
        ->values()
        ->all();

    expect($missing)->toBe([]);
});

it('maps the React chart names onto the package', function () {
    expect(LucideIcons::resolve('chart-line'))->toBe('line-chart')
        ->and(LucideIcons::resolve('chart-column'))->toBe('bar-chart-3')
        ->and(LucideIcons::resolve('check'))->toBe('check');

    foreach (LucideIcons::ALIASES as $react => $package) {
        expect(LucideIcons::has($package))->toBeTrue("alias {$react} points at a missing icon {$package}");
    }
});

it('renders inline currentColor SVG with an adjustable stroke and no duplicate root', function () {
    $html = Blade::render('<x-ui.icon name="chart-line" stroke-width="1.5" class="size-4 text-ink-muted" />');

    expect(substr_count($html, '<svg'))->toBe(1)
        ->and($html)->toContain('stroke="currentColor"')
        ->toContain('stroke-width="1.5"')
        ->not->toContain('stroke-width="2"')
        ->toContain('aria-hidden="true"')
        ->toContain('class="shrink-0 size-4 text-ink-muted"')
        ->toContain('<path d="m19 9-5 5-4-4-3 3"/>');

    expect(Blade::render('<x-ui.icon name="check" />'))->toContain('stroke-width="2"')
        ->toContain('<path d="M20 6 9 17l-5-5"/>');
});

it('passes Livewire and Alpine attributes through', function () {
    expect(Blade::render('<x-ui.icon name="refresh-cw" wire:loading.class="animate-spin" wire:target="refresh" />'))
        ->toContain('wire:loading.class="animate-spin"')
        ->toContain('wire:target="refresh"');
});

it('throws for an unknown icon in testing', function () {
    LucideIcons::inner('not-a-lucide-icon');
})->throws(SvgNotFound::class);

it('does not register a Blade component per icon', function () {
    expect(config('blade-icons.components.disabled'))->toBeTrue()
        ->and(array_key_exists('lucide-check', Blade::getClassComponentAliases()))->toBeFalse();
});
