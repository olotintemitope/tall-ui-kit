<?php

use Illuminate\Support\Facades\Blade;

/* "Text is never cut off" (docs/ui-kit.md). The browser side is tests/Browser/overflow-audit.mjs. */

it('offers lean money and badge markup for dense rows', function () {
    $badge = Blade::render('<x-ui.badge lean variant="success" size="sm">Paid</x-ui.badge>');
    expect(trim($badge))->toMatch('/^<span class="ui-badge"\s+data-tone="success"\s+data-size="sm"\s*>Paid<\/span>$/')
        ->and(strlen(trim($badge)))->toBeLessThan(80);

    $money = Blade::render('<x-ui.money lean :value="25000" />');
    expect(trim($money))->toBe('<span data-money >₦25,000</span>')->not->toContain('x-data');

    expect(file_get_contents(kit_path('resources/css/tall-ui-kit.css')))->toContain('.ui-badge {')->toContain(".ui-badge[data-tone='success']");
});

it('keeps the data table loading state out of the rows and its cells wrapping', function () {
    $states = file_get_contents(kit_path('resources/views/components/data-table/states.blade.php'));
    $index = file_get_contents(kit_path('resources/views/components/data-table/index.blade.php'));
    $assets = file_get_contents(kit_path('resources/views/components/data-table/assets.blade.php'));

    expect($states)->toContain('role="row" data-dt-loading-row')->not->toMatch('/<td[^>]*role="status"/');
    expect($index)->toContain("\$table->loaded ? \$table->announcement : 'Loading '.\$table->noun");
    expect($assets)->not->toContain('text-overflow: ellipsis')->toContain('overflow-wrap: break-word')->toContain('[data-dt] .dt-t { display: block; min-width: 0; overflow-wrap: break-word; }');
});

it('lets buttons, badges, menu items and sidebar rows grow instead of clipping', function () {
    expect(Blade::render('<x-ui.button>Request a payout to GTBank</x-ui.button>'))->toContain('min-h-9')->toContain('wrap-break-word')->not->toContain('whitespace-nowrap');
    // Pills are the exception: one line always, sized to the label, never shrunk or clipped.
    expect(Blade::render('<x-ui.badge>Suspended by an administrator</x-ui.badge>'))->toContain('whitespace-nowrap')->toContain('shrink-0')->not->toContain('max-w-full');
    expect(file_get_contents(kit_path('resources/views/components/ui/dropdown/item.blade.php')))->toContain('min-h-8')->not->toContain('whitespace-nowrap');
    expect(file_get_contents(kit_path('resources/views/components/ui/sidebar/item.blade.php')))->toContain('min-h-8')->not->toContain(' h-8 ');
    expect(Blade::render('<x-ui.tooltip text="A long tooltip">x</x-ui.tooltip>'))->toContain('max-w-xs wrap-anywhere')->not->toContain('whitespace-nowrap');
});

it('wraps a select\'s chosen label and its options, so a long option is never cut', function () {
    $html = Blade::render('<x-ui.select name="status" selected="s" :options="[\'s\' => \'Suspended by an administrator, pending review\']" />');

    // The trigger shows the full label and wraps inside itself; the options wrap in the list (app.css).
    expect($html)->toMatch('/<span class="min-w-0 flex-1 wrap-anywhere" data-select-label[^>]*>Suspended by an administrator, pending review<\/span>/')
        ->toContain('min-h-9 py-1.5')->not->toContain('truncate')->not->toContain('whitespace-nowrap');
    expect(file_get_contents(kit_path('resources/css/tall-ui-kit.css')))->toMatch("/\\.ui-select-list \\[role='option'\\] \\{[^}]*overflow-wrap: anywhere/");
});
