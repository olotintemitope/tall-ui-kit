<?php

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Blade;
use TallUiKit\Support\LucideIcons;

beforeEach(function () {
    $this->withoutVite();
});

/*
| x-ui.chart, the card family, x-ui.filter-token and the skeleton presets.
*/

function renderKit(string $template, array $data = []): string
{
    return Blade::render($template, $data);
}

function chartData(): array
{
    return [
        'days' => ['2026-11-01', '2026-11-02', '2026-11-03'],
        'one' => [['name' => 'Revenue', 'token' => 'chart-accent', 'points' => [5, 0, 10]]],
    ];
}

describe('cards', function () {
    it('keeps the card base flat with a 1px line border', function () {
        $html = renderKit('<x-ui.card title="Payouts" description="Every Friday"><x-slot:actions><button>Run</button></x-slot:actions> Body <x-slot:footer>Updated today</x-slot:footer></x-ui.card>');

        expect($html)->toContain('rounded-lg border border-line')
            ->toContain('type-heading')
            ->toContain('data-card-footer')
            ->toContain('bg-panel-footer')
            ->toMatch('/data-card-header>.*<button>Run<\/button>/s')
            ->not->toMatch('/\bshadow-/')
            ->not->toContain('rounded-2xl');
    });

    it('renders a summary card with figure, delta and sparkline', function () {
        $html = renderKit('<x-ui.card.summary label="Revenue" :value="4281500" money :previous="3820000" period="30d" :sparkline="[1, 2, 3]" sub="12 campaigns" />');

        expect($html)->toContain('data-card-summary')
            ->toContain('type-label')
            ->toContain('type-figure')
            ->toContain('₦4,281,500')
            ->toContain('vs prior 30d')
            ->toContain('data-ui-chart="sparkline"')
            ->toContain('12 campaigns');
    });

    it('renders a list card with rows, a view all footer and an empty line', function () {
        $html = renderKit('<x-ui.card.list title="Waiting" view-all="/admin/campaigns"><x-ui.card.row title="Second Wind" meta="Playlist">6</x-ui.card.row></x-ui.card.list>');
        expect($html)->toContain('data-card-list')
            ->toContain('<ul class="px-4')
            ->toContain('type-body-strong')
            ->toContain('href="/admin/campaigns"')
            ->toContain('View all');

        expect(renderKit('<x-ui.card.list title="Disputes" empty="No open disputes." />'))
            ->toContain('No open disputes.')
            ->not->toContain('<ul');
    });

    it('renders a table card with a flush table and a named pagination footer', function () {
        $paginator = new LengthAwarePaginator([1, 2], 12, 2, 1, ['path' => '/admin/payouts']);
        $html = renderKit('<x-ui.card.table title="Payouts" :paginator="$p"><x-slot:head><x-ui.table.th>Ref</x-ui.table.th></x-slot:head><x-ui.table.row><x-ui.table.td>PO-1</x-ui.table.td></x-ui.table.row></x-ui.card.table>', ['p' => $paginator]);

        expect($html)->toContain('data-card-table')
            ->toContain('<caption class="sr-only">Payouts</caption>')
            ->toContain('aria-label="Payouts pagination"')
            ->not->toMatch('/data-card-body[^>]*>\s*<div class="overflow-x-auto[^"]*rounded-lg/');
    });

    it('renders a chart card around the chart anatomy', function () {
        expect(renderKit('<x-ui.card.chart :labels="$days" :series="$one" title="Revenue" :previous="10" delta-period="14d" />', chartData()))
            ->toContain('data-card-chart')
            ->toContain('x-data="uiChart"')
            ->toContain('vs prior 14d');
    });

    it('renders a detail card as a two-column description list', function () {
        $html = renderKit('<x-ui.card.detail title="Campaign" status="In review" status-variant="warning" :items="[[\'label\' => \'Musician\', \'value\' => \'Kemi\'], [\'label\' => \'Curators\', \'value\' => \'6\', \'numeric\' => true]]" />');

        expect($html)->toContain('<dl class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-2">')
            ->toContain('<dt class="type-meta">Musician</dt>')
            ->toContain('type-numeric text-ink')
            ->toContain('In review')
            ->toContain('bg-amber-50');
    });

    it('renders a link card as a single anchor with the focus ring', function () {
        $html = renderKit('<x-ui.card.link href="/admin/payouts" title="Payout requests" meta="14 waiting" />');

        expect(substr_count($html, '<a '))->toBe(1)
            ->and(substr_count($html, '<button'))->toBe(0)
            ->and($html)->toContain('href="/admin/payouts"')
            ->toContain('focus-ring')
            ->toContain('hover:bg-surface-subtle')
            ->toContain('text-ink-muted');
    });

    it('renders a profile card with initials, verified badge and stats', function () {
        $html = renderKit('<x-ui.card.profile name="Chioma Eze" meta="Curator" verified :stats="[\'Playlists\' => \'12\', \'Acceptance\' => \'68%\']" />');

        expect($html)->toContain('CE')
            ->toContain('Verified')
            ->toContain('<dt class="type-meta">Playlists</dt>')
            ->toContain('68%');
    });

    it('renders a selectable card with a real input, price column and linked reason', function () {
        $html = renderKit('<x-ui.card.selectable name="tier" value="macro" title="Macro" :price="120000" unit="per video" disabled reason="None available." wire:model.live="tier" />');

        expect($html)->toContain('type="radio"')
            ->toContain('wire:model.live="tier"')
            ->toContain('disabled')
            ->toContain('aria-describedby="select-tier-macro-reason"')
            ->toContain('₦120,000')
            ->toContain('has-checked:border-primary-text')
            ->toContain('rounded-lg');

        expect(renderKit('<x-ui.card.selectable type="checkbox" name="blogs[]" value="pulse" title="Pulse" />'))
            ->toContain('type="checkbox"');
    });
});

it('renders a filter token with an accessible remove button', function () {
    $html = renderKit('<x-ui.filter-token label="Genre: Afrobeats" wire:click="removeFilter(\'genre\')" />');

    expect($html)->toContain('data-filter-token')
        ->toContain('rounded-md border border-control')
        ->toContain('type-body')
        ->toMatch('/<button[^>]*aria-label="Remove filter: Genre: Afrobeats"[^>]*wire:click="removeFilter\(\'genre\'\)"/s')
        ->not->toContain('rounded-full');
});

it('renders the grid and list skeleton presets as one loading status', function () {
    expect(renderKit('<x-ui.skeleton.grid :count="4" label="Loading playlists" />'))
        ->toContain('role="status"')->toContain('Loading playlists')->toContain('data-skeleton="grid"');

    expect(substr_count(renderKit('<x-ui.skeleton.list :rows="3" />'), 'animate-pulse'))->toBe(9);
});

it('ships the marketplace icons in the kit', function (string $name) {
    expect(LucideIcons::has($name))->toBeTrue();
})->with(['layout-grid', 'sliders-horizontal', 'git-compare-arrows']);
