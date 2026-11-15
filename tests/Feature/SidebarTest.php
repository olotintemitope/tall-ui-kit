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

it('navigates in-app items with wire:navigate and keeps external, disabled and reload items as plain links', function () {
    $html = sidebar('<x-ui.sidebar.section>'
        .'<x-ui.sidebar.item href="/admin/users" icon="users" label="Users" />'
        .'<x-ui.sidebar.item href="https://wa.me/1" icon="message-circle" label="WhatsApp" external />'
        .'<x-ui.sidebar.item icon="inbox" label="Requests" disabled reason="Not open yet." />'
        .'<x-ui.sidebar.item href="/l/report" icon="file-text" label="Report" reload />'
        .'</x-ui.sidebar.section>');

    expect(substr_count($html, 'wire:navigate'))->toBe(1)
        ->and($html)->toMatch('/href="\/admin\/users"\s+[^>]*wire:navigate/')
        ->toContain('data-nav-match');

    $off = sidebar('<x-ui.sidebar.section><x-ui.sidebar.item href="/admin/users" label="Users" /></x-ui.sidebar.section>', 'variant="dark" :fixed="false" :navigate="false"');
    expect($off)->not->toContain('wire:navigate');
});

it('persists the shell sidebar in a plain wrapper with the client sync data', function () {
    config(['tall-ui-kit.sidebar.exact' => ['/admin']]);
    $html = Blade::render('<x-ui.sidebar variant="dark" area="admin" persist="sidebar-admin-abc"><x-slot:header><x-ui.sidebar.header home="/admin" /></x-slot:header><x-slot:nav><x-ui.sidebar.section><x-ui.sidebar.group label="Money" icon="wallet"><x-ui.sidebar.item nested href="/admin/payouts" label="Payouts" /></x-ui.sidebar.group></x-ui.sidebar.section></x-slot:nav><x-slot:footer><x-ui.sidebar.account name="Ada" settings-href="/admin/settings" :areas="[[\'key\' => \'admin\', \'label\' => \'Admin\', \'href\' => \'/admin\'], [\'key\' => \'curator\', \'label\' => \'Curator\', \'href\' => \'/curator\']]" /></x-slot:footer></x-ui.sidebar>');

    // The wrapper carries no Alpine directives: Livewire re-initialises the persisted element itself.
    expect($html)->toMatch('/<div x-persist="sidebar-admin-abc" class="contents" data-sidebar-persist>\s*<div\s+x-data=/')
        ->toContain('data-nav-sync')
        ->toContain('data-nav-exact="[&quot;\/admin&quot;]"')
        ->toContain('data-nav-tones=')
        ->toContain('wire:navigate:scroll')
        ->toMatch('/data-nav-group\s+data-nav-match/')
        ->toMatch('/<a href="\/admin"\s+wire:navigate/')
        ->toMatch('/href="\/admin\/settings"[^>]*wire:navigate/')
        ->toMatch('/<a href="\/curator"(?![^>]*wire:navigate)[^>]*data-area-option="curator"/');

    $preview = sidebar('<x-ui.sidebar.section><x-ui.sidebar.item href="/x" label="X" /></x-ui.sidebar.section>');
    expect($preview)->not->toContain('x-persist')->not->toContain('data-nav-sync');
});
