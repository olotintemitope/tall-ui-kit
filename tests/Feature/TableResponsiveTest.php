<?php

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Blade;

// Tables never push the page sideways: a stack table stacks below 640px and, below
// 1024px, whenever its columns do not fit (data-stacked, set by fitLayout()); stacked
// cells break long unbroken words; footers and pagers wrap instead of overflowing.
// Browser evidence: every admin page at 320/390/768/1024/1440 had no page-level
// sideways scroll after this change.

it('stacks a stack table whenever its columns do not fit below 1024px', function () {
    $assets = Blade::render('<x-data-table.assets />');

    expect($assets)
        ->toContain("[data-dt][data-layout='stack'][data-stacked] thead { display: none; }")
        ->toContain('fitLayout(box)')
        ->toContain('width >= 640 && width < 1024');
});

it('breaks long unbroken words inside stacked cells', function () {
    $assets = Blade::render('<x-data-table.assets />');

    expect(substr_count($assets, 'overflow: visible; overflow-wrap: anywhere; min-width: 0; }'))->toBe(2);
});

it('lets the kit pager wrap instead of overflowing', function () {
    $paginator = new LengthAwarePaginator(range(1, 10), 500, 10, 25, ['path' => '/x']);
    $html = Blade::render('<x-ui.pagination :paginator="$p" />', ['p' => $paginator]);

    expect($html)->toContain('flex min-w-0 flex-wrap items-center justify-between');
});

it('sizes the row action menu to its labels and lets items grow', function () {
    $menu = file_get_contents(kit_path('resources/views/components/data-table/row-menu.blade.php'));
    $assets = Blade::render('<x-data-table.assets />');

    // A fixed 192px menu with 32px items wrapped "Show this user's transactions" over the next item.
    expect($menu)->toContain('w-max min-w-48 max-w-[calc(100vw-1rem)]')
        ->toContain('min-h-8 w-full')
        ->not->toContain("'flex h-8 w-full")
        ->and($assets)->not->toContain('const width = 192;')
        ->toContain('tbody tr:not([data-selected]):hover { background: var(--color-panel-footer); }');
});
