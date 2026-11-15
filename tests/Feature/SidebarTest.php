<?php

use Illuminate\Support\Facades\Blade;
use TallUiKit\Support\SidebarContrast;

beforeEach(fn () => $this->withoutVite());

function sidebar(string $nav, string $props = 'variant="dark" :fixed="false"'): string
{
    return Blade::render("<x-ui.sidebar {$props}><x-slot:nav>{$nav}</x-slot:nav></x-ui.sidebar>");
}

it('shows a group as active and open when a child is the current page', function () {
    $html = sidebar('<x-ui.sidebar.section><x-ui.sidebar.group label="Payouts" icon="wallet"><x-ui.sidebar.item nested href="/a" label="Requests" :active="true" /><x-ui.sidebar.item nested href="/b" label="History" :active="false" /></x-ui.sidebar.group></x-ui.sidebar.section>');

    expect($html)->toMatch('/data-nav-group\s+data-active/')
        ->toContain('aria-expanded="true"')
        ->toContain('x-data="{ open: true');

    $closed = sidebar('<x-ui.sidebar.section><x-ui.sidebar.group label="Payouts"><x-ui.sidebar.item nested href="/a" label="Requests" :active="false" /></x-ui.sidebar.group></x-ui.sidebar.section>');
    expect($closed)->toContain('aria-expanded="false"')->not->toMatch('/data-nav-group\s+data-active/');
});

it('keeps disabled items legible, unlinked and explained', function () {
    $html = sidebar('<x-ui.sidebar.section><x-ui.sidebar.item icon="file-text" label="Statements" disabled reason="Opens after the first payout." /></x-ui.sidebar.section>', 'variant="light" :fixed="false"');

    expect($html)->toContain('aria-disabled="true"')->toContain('role="link"')
        ->toContain('text-ink-muted cursor-not-allowed')->toContain('decoration-dashed')
        ->toContain('Opens after the first payout.')->toMatch('/aria-describedby="nav-reason-/')
        ->not->toMatch('/href="#"/')->not->toMatch('/\bopacity-\d/');
});

it('renders the New tag and badges, and tooltips for the rail', function () {
    $html = sidebar('<x-ui.sidebar.section><x-ui.sidebar.item href="/t" icon="house" label="Transactions" new /><x-ui.sidebar.item href="/a" icon="gavel" label="Appeals" badge="3 late" badge-variant="danger" /></x-ui.sidebar.section>');

    expect($html)->toContain('data-nav-new')->toContain('>New<')->toContain('data-nav-badge')->toContain('3 late')
        ->toContain('role="tooltip"')->toContain('railed($el)');
});

it('persists the sidebar and its sections per area', function () {
    $html = Blade::render('<x-ui.sidebar variant="light" area="curator"><x-slot:nav><x-ui.sidebar.section label="Finance" key="finance" collapsible><x-ui.sidebar.item href="/f" label="Fees" :active="false" /></x-ui.sidebar.section></x-slot:nav></x-ui.sidebar>');

    expect($html)->toContain("\$persist(false).as('tallui_sidebar_curator')")
        ->toContain("\$persist(true).as('tallui_sidebar_curator_finance')")
        ->toContain('data-sidebar-rail')->toContain('data-sidebar-drawer')
        ->toContain('<dialog')->toContain('rail:w-14')->toContain('data-sidebar-section-toggle')
        ->toContain('x-collapse');
});

it('measures every sidebar state at AA', function () {
    foreach (SidebarContrast::rows() as $row) {
        foreach (['dark', 'light', 'inset'] as $variant) {
            expect($row[$variant])->toBeGreaterThanOrEqual($row['min'], "{$row['state']} on {$variant}");
        }
    }
});
