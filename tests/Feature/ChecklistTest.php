<?php

use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use TallUiKit\Support\Checklist\ChecklistStore;
use TallUiKit\Support\Checklist\SessionChecklistStore;
use TallUiKit\Support\KitContrast;
use Workbench\App\Livewire\ChecklistGallery;

/*
| x-ui.checklist and x-ui.checklist.item: markup, accessibility attributes, states,
| the next step, persistence through ChecklistStore and the WithChecklist trait.
*/

function checklist_items(array $done = [true, false, false]): array
{
    return [
        ['key' => 'email', 'label' => 'Verify your email', 'done' => $done[0]],
        ['key' => 'profile', 'label' => 'Complete your profile', 'hint' => 'Name and phone.', 'done' => $done[1], 'href' => '/profile', 'actionLabel' => 'Edit profile'],
        ['key' => 'team', 'label' => 'Invite your team', 'done' => $done[2], 'action' => "complete('team')", 'actionLabel' => 'Invite'],
        ['key' => 'import', 'label' => 'Import data', 'done' => false, 'optional' => true, 'href' => '/import', 'actionLabel' => 'Import'],
    ];
}

function render_checklist(string $props, array $items): string
{
    return Blade::render('<x-ui.checklist '.$props.' :items="$items" />', ['items' => $items]);
}

it('renders a named section with a list described by the progress line', function () {
    $html = render_checklist('title="Set up your workspace" description="A few steps." name="setup"', checklist_items());

    expect($html)
        ->toContain('<section')
        ->toContain('aria-labelledby="checklist-setup-title"')
        ->toContain('<h2 id="checklist-setup-title"')
        ->toContain('A few steps.')
        ->toContain('<ol class="divide-y divide-line" aria-labelledby="checklist-setup-title" aria-describedby="checklist-setup-progress"')
        ->toContain('id="checklist-setup-progress" class="type-meta whitespace-nowrap">1 of 3 done</p>')
        ->toContain('<progress')
        ->toContain('id="checklist-setup-bar"')
        ->toContain('aria-valuetext="1 of 3 done"')
        ->toContain('max="3"')
        ->toContain('value="1"');
});

it('says each step state and marks the first open required step as next', function () {
    $html = render_checklist('title="Setup" name="setup"', checklist_items());

    expect(substr_count($html, '<span class="sr-only">Done: </span>'))->toBe(1)
        ->and(substr_count($html, '<span class="sr-only">Not done: </span>'))->toBe(3)
        ->and(substr_count($html, 'aria-current="step"'))->toBe(1)
        ->and($html)->toMatch('/<li[^>]*class="[^"]*bg-surface-subtle[^"]*"[^>]*id="checklist-setup-profile"[^>]*aria-current="step"/s')
        ->and($html)->toContain('data-done="1"')
        ->and($html)->toContain('data-checklist-check');
});

it('mutes done steps without crossing them out and drops their action', function () {
    $html = render_checklist('title="Setup" name="setup"', checklist_items([true, true, false]));

    expect($html)
        ->toContain('type-body text-ink-muted')
        ->not->toContain('line-through')
        ->not->toContain('href="/profile"')
        ->toContain('wire:click="complete(&#039;team&#039;)"')
        ->toContain('wire:target="complete"')
        ->toContain('aria-describedby="checklist-setup-team-label"');
});

it('renders link actions as links and labels optional steps', function () {
    $html = render_checklist('title="Setup" name="setup"', checklist_items());

    expect($html)
        ->toContain('href="/profile"')
        ->toContain('Edit profile')
        ->toContain('(Optional)');
});

it('counts required steps only and shows the all set line with Dismiss when they are done', function () {
    $html = render_checklist('title="Setup" name="setup" dismissible', checklist_items([true, true, true]));

    expect($html)
        ->toContain('3 of 3 done')
        ->toContain('data-complete="1"')
        ->toContain('You&#039;re all set.')
        ->toContain('data-checklist-dismiss')
        ->not->toContain('aria-current="step"');

    $partial = render_checklist('title="Setup" name="setup" dismissible', checklist_items());
    expect($partial)->not->toContain('data-checklist-dismiss')->not->toContain('all set');
});

it('wires the collapse button and starts collapsed when asked', function () {
    $html = render_checklist('title="Setup" name="setup" collapsible :collapsed="true"', checklist_items());

    expect($html)
        ->toContain('aria-controls="checklist-setup-steps"')
        ->toContain('aria-expanded="false"')
        ->toContain('aria-label="Show steps"')
        ->toMatch('/id="checklist-setup-steps"[^>]*x-cloak/');

    expect(render_checklist('title="Setup" name="setup"', checklist_items()))->not->toContain('data-checklist-toggle');
});

it('has a polite status for steps that turn done', function () {
    expect(render_checklist('title="Setup" name="setup"', checklist_items()))
        ->toContain('<p class="sr-only" role="status" aria-live="polite" wire:ignore data-checklist-live></p>');
});

it('renders a skeleton with one status while loading', function () {
    $html = render_checklist('title="Setup" loading', checklist_items());

    expect($html)
        ->toContain('role="status"')
        ->toContain('Loading Setup')
        ->toContain('data-checklist-loading')
        ->not->toContain('<section');
});

it('renders nothing once dismissed, and only while every required step is done', function () {
    expect(trim(render_checklist('title="Setup" name="setup" dismissible :dismissed="true"', checklist_items([true, true, true]))))->toBe('');
    expect(render_checklist('title="Setup" name="setup" dismissible :dismissed="true"', checklist_items()))->toContain('<section');
});

it('reads collapsed and dismissed from the bound store with persist server', function () {
    $store = app(ChecklistStore::class);
    expect($store)->toBeInstanceOf(SessionChecklistStore::class);

    $store->setCollapsed('setup', true);
    expect(render_checklist('title="Setup" name="setup" collapsible persist="server"', checklist_items()))->toContain('aria-expanded="false"');

    $store->dismiss('setup');
    expect(trim(render_checklist('title="Setup" name="setup" dismissible persist="server"', checklist_items([true, true, true]))))->toBe('');
});

it('lets an app bind its own store', function () {
    $custom = new class implements ChecklistStore
    {
        public function collapsed(string $key): bool
        {
            return true;
        }

        public function dismissed(string $key): bool
        {
            return false;
        }

        public function setCollapsed(string $key, bool $collapsed): void {}

        public function dismiss(string $key): void {}
    };
    app()->instance(ChecklistStore::class, $custom);

    expect(render_checklist('title="Setup" name="setup" collapsible persist="server"', checklist_items()))->toContain('aria-expanded="false"');
});

it('rejects a name the store could not keep', function () {
    render_checklist('title="Setup" name="bad.name"', checklist_items());
})->throws(Exception::class);

it('takes x-ui.checklist.item rows in the slot with explicit counts', function () {
    $html = Blade::render('<x-ui.checklist title="Launch" :completed="1" :total="2"><x-ui.checklist.item label="Write notes" done id="a" /><x-ui.checklist.item label="Publish" next id="b" href="/publish" action-label="Publish" /></x-ui.checklist>');

    expect($html)
        ->toContain('1 of 2 done')
        ->toContain('aria-current="step"')
        ->toContain('href="/publish"');
});

it('persists collapse and dismiss through WithChecklist and checks the key', function () {
    Livewire::test(ChecklistGallery::class)
        ->call('checklistCollapse', 'workspace', true)
        ->assertOk();
    expect(app(ChecklistStore::class)->collapsed('workspace'))->toBeTrue();

    Livewire::test(ChecklistGallery::class)->call('checklistCollapse', 'other', true)->assertStatus(422);
    Livewire::test(ChecklistGallery::class)->call('checklistCollapse', '../x', true)->assertStatus(422);
});

it('lets the component refuse a dismissal while required steps are open', function () {
    Livewire::test(ChecklistGallery::class)->call('checklistDismiss', 'workspace')->assertForbidden();
    expect(app(ChecklistStore::class)->dismissed('workspace'))->toBeFalse();

    Livewire::test(ChecklistGallery::class)
        ->call('complete', 'invite')
        ->call('complete', 'billing')
        ->call('complete', 'domain')
        ->call('checklistDismiss', 'workspace')
        ->assertOk();
    expect(app(ChecklistStore::class)->dismissed('workspace'))->toBeTrue();
});

it('flips a step to done on the live gallery and keeps the round trip small', function () {
    $component = Livewire::test(ChecklistGallery::class)
        ->assertSee('1 of 4 done')
        ->call('complete', 'invite')
        ->assertSee('2 of 4 done');

    expect(strlen(json_encode($component->effects)))->toBeLessThan(40_000);
});

it('ships the checklist script, the motion rule and the contrast rows', function () {
    expect(file_get_contents(kit_path('resources/js/ui.js')))->toContain("import './checklist';")
        ->and(file_get_contents(kit_path('resources/js/checklist.js')))->toContain("Alpine.data('uiChecklist'")->toContain('data-checklist-flip')
        ->and(file_get_contents(kit_path('resources/css/tall-ui-kit.css')))->toContain('[data-checklist-flip] [data-checklist-check]')->toContain('prefers-reduced-motion: no-preference')
        ->and(collect(KitContrast::pairs())->filter(fn ($p) => str_starts_with($p[0], 'Checklist'))->count())->toBe(9);
});
