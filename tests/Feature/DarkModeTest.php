<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Livewire\Component;
use Livewire\Livewire;
use TallUiKit\Livewire\Concerns\WithColorScheme;
use TallUiKit\Support\ColorScheme\ColorSchemeStore;

/*
| Dark mode (docs/theming.md "Dark mode"): the head snippet sets the scheme before
| paint, x-ui.theme-toggle is an accessible radiogroup, the server store is checked,
| and no component hard-codes a light colour.
*/

it('sets html[data-color-scheme] in an inline head script, defaulting to system', function () {
    $html = Blade::render('<x-ui.head />');

    expect($html)->toContain('<script>')
        ->not->toContain('defer')
        ->not->toContain('src=')
        ->toContain("root.dataset.colorScheme = schemes.indexOf(scheme) > -1 ? scheme : 'system'")
        ->toContain("key = 'tallui_color_scheme'")
        ->toContain("root.dataset.colorSchemes = 'light dark system'");
});

it('runs the head snippet before the stylesheet, so the first paint has the scheme', function () {
    $html = $this->get('/')->assertOk()->getContent();

    $script = strpos($html, 'root.dataset.colorScheme');
    $stylesheet = strpos($html, 'rel="stylesheet" href="'.route('kit-assets', 'neutral.css'));

    expect($script)->toBeInt()->and($stylesheet)->toBeInt()->and($script)->toBeLessThan($stylesheet);
});

it('prints a server choice from the prop or the store, and ignores values outside the configured schemes', function () {
    expect(Blade::render('<x-ui.head scheme="dark" />'))->toContain("scheme = 'dark'");
    expect(Blade::render('<x-ui.head scheme="sepia" />'))->toContain('scheme = null');

    config(['tall-ui-kit.color_scheme.persist' => 'server']);
    app(ColorSchemeStore::class)->put('light');
    expect(Blade::render('<x-ui.head />'))->toContain("scheme = 'light'")->toContain("colorSchemePersist = 'server'");
});

it('locks the app to light when only one scheme is offered', function () {
    config(['tall-ui-kit.color_scheme.schemes' => ['light'], 'tall-ui-kit.color_scheme.default' => 'system']);

    expect(Blade::render('<x-ui.head />'))->toContain("root.dataset.colorSchemes = 'light'")->toContain(": 'light'")
        ->and(trim(Blade::render('<x-ui.theme-toggle />')))->toBe('');
});

it('renders the theme toggle as a labelled radiogroup with one tab stop and a live region', function () {
    $html = Blade::render('<x-ui.theme-toggle />');

    expect($html)->toContain('role="radiogroup"')
        ->toContain('aria-label="Colour scheme"')
        ->toContain('role="status" aria-live="polite" data-theme-toggle-live')
        ->toContain('x-on:keydown.right.prevent="move(1)"')
        ->toContain('x-on:keydown.left.prevent="move(-1)"')
        ->toContain('focus-ring')
        ->and(substr_count($html, 'role="radio"'))->toBe(3)
        ->and(substr_count($html, 'tabindex="0"'))->toBe(1)
        ->and(substr_count($html, 'aria-checked="true"'))->toBe(1)
        ->and($html)->toMatch('/<\/svg>\s*Light/')->toMatch('/<\/svg>\s*Dark/')->toMatch('/<\/svg>\s*System/');

    $icons = Blade::render('<x-ui.theme-toggle icon-only label="Mode" />');
    expect($icons)->toContain('aria-label="Mode"')
        ->toContain('aria-label="Light"')->toContain('aria-label="Dark"')->toContain('aria-label="System"');
});

it('stores a valid scheme through WithColorScheme and refuses anything else', function () {
    $component = new class extends Component
    {
        use WithColorScheme;

        public function render(): string
        {
            return '<div><x-ui.theme-toggle /></div>';
        }
    };

    Livewire::test($component::class)->call('setColorScheme', 'dark')->assertOk();
    expect(app(ColorSchemeStore::class)->get())->toBe('dark');

    Livewire::test($component::class)->call('setColorScheme', 'sepia')->assertStatus(422);
    expect(app(ColorSchemeStore::class)->get())->toBe('dark');
});

it('routes every component colour through tokens: no white, black or palette status colours', function () {
    $sources = collect(File::allFiles(kit_path('resources/views')))
        ->filter(fn ($file) => str_ends_with($file->getFilename(), '.blade.php'))
        ->mapWithKeys(fn ($file) => [$file->getRelativePathname() => preg_replace('/\{\{--.*?--\}\}/s', '', $file->getContents())]);

    // The dark sidebar's own tones (sidebar parts, and the `dark` tone of avatar and kbd) stay white on the `sidebar` surface in both schemes.
    $withoutDarkTones = fn (string $source): string => preg_replace(['/\'[^\']*\' => \$tone === \'dark\'/', '/\$tone === \'dark\' \? \'[^\']*\'/'], '', $source);
    $offenders = $sources
        ->reject(fn (string $source, string $path) => str_contains($path, 'sidebar/'))
        ->filter(fn (string $source) => preg_match('/(?<![\w-])(bg|text|border|ring|outline|stroke|fill|divide)-(white|black)\b/', $withoutDarkTones($source)))
        ->keys()->all();
    $palette = $sources->filter(fn (string $source) => preg_match('/\b(bg|text|border|ring)-(red|amber|emerald|blue|green|yellow)-\d{2,3}\b/', $source))->keys()->all();

    expect($offenders)->toBe([])->and($palette)->toBe([]);

    $css = File::get(kit_path('resources/css/tall-ui-kit.css')).File::get(kit_path('resources/views/components/data-table/assets.blade.php'));
    $css = preg_replace('/@media print \{.*?\n\}/s', '', $css);
    expect(preg_match('/:\s*white\b|,\s*white\)|var\(--color-(red|amber|emerald|blue)-/', $css))->toBe(0);
});

it('keeps print light: the dark variant is screen only', function () {
    $css = File::get(kit_path('resources/css/tall-ui-kit.css'));

    expect($css)->toContain('@custom-variant dark {')
        ->toContain('@media screen {')
        ->toContain('@media screen and (prefers-color-scheme: dark)');
});
