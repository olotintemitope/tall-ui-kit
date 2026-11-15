<?php

use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use TallUiKit\Support\Checklist\ChecklistStore;
use TallUiKit\Support\Checklist\SessionChecklistStore;
use TallUiKit\Support\Dismiss\DismissStore;
use TallUiKit\Support\KitContrast;
use Workbench\App\Livewire\Workflow\BannerDemo;
use Workbench\App\Livewire\Workflow\SortableDemo;
use Workbench\App\Livewire\Workflow\TreeDemo;

/*
| The workflow components (docs/components/workflow.md): stepper, timeline, tag input,
| avatar group, sortable, slider, banner, tree, and the progress family. Markup,
| accessibility attributes, states, server persistence and the demo endpoints.
*/

function wf(string $blade, array $data = []): string
{
    return Blade::render($blade, $data);
}

describe('stepper', function () {
    $steps = [['label' => 'Account', 'description' => 'Email.'], ['label' => 'Bank', 'state' => 'error'], ['label' => 'Review'], ['label' => 'Live']];

    it('is an ordered list in a named nav with one current step and spoken states', function () use ($steps) {
        $html = wf('<x-ui.stepper label="Setup" :steps="$steps" :current="3" />', compact('steps'));

        expect($html)->toContain('<nav')->toContain('aria-label="Setup"')->toContain('<ol')
            ->and(substr_count($html, 'aria-current="step"'))->toBe(1)
            ->and($html)->toContain('<span class="sr-only">Completed: </span>Account')
            ->toContain('<span class="sr-only">Error: </span>Bank')
            ->toContain('<span class="sr-only">Current: </span>Review')
            ->toContain('<span class="sr-only">Upcoming: </span>Live')
            ->toContain('data-step-state="error"')
            ->toContain('@2xl:flex-row')
            ->not->toContain('<button');
    });

    it('makes complete and error steps clickable as links, Livewire calls or events', function () use ($steps) {
        $wire = wf('<x-ui.stepper :steps="$steps" :current="3" clickable action="goTo" />', compact('steps'));
        expect($wire)->toContain('wire:click="goTo(1)"')->toContain('wire:click="goTo(2)"')->not->toContain('goTo(3)')->not->toContain('goTo(4)');

        $event = wf('<x-ui.stepper :steps="$steps" :current="3" clickable="all" />', compact('steps'));
        expect($event)->toContain('x-data')->toContain("\$dispatch('ui-step', { step: 4")->toContain('focus-ring');

        $links = wf('<x-ui.stepper :steps="[[\'label\' => \'One\', \'href\' => \'/one\'], \'Two\']" :current="2" clickable />');
        expect($links)->toContain('<a')->toContain('href="/one"');
    });

    it('stacks always when vertical and renders the inline text list', function () {
        $vertical = wf('<x-ui.stepper orientation="vertical" :steps="[\'A\', \'B\']" />');
        expect($vertical)->not->toContain('@2xl:flex-row')->toContain('data-stepper="vertical"');

        $inline = wf('<x-ui.stepper variant="inline" :steps="[\'Song\', \'Targets\', \'Pay\']" :current="2" clickable action="goTo" />');
        expect($inline)->toContain('Step 2 of 3')->toContain('wire:click="goTo(1)"')->toContain('Done')->toContain('aria-current="step"');
    });

    it('is the wizard layout\'s step list', function () {
        expect(preg_replace('/\{\{--.*?--\}\}/s', '', file_get_contents(kit_path('resources/views/components/ui/form-layout/wizard.blade.php'))))
            ->toContain('<x-ui.stepper')->not->toContain('<ol');
    });
});

describe('timeline', function () {
    $items = [
        ['key' => 'a', 'time' => '2026-11-15 13:00:00', 'actor' => 'Ada Obi', 'text' => 'approved it.', 'status' => ['Approved', 'success']],
        ['key' => 'b', 'time' => '2026-11-15 09:00:00', 'actor' => 'Bola Ade', 'text' => 'commented.', 'body' => 'Looks good.'],
        ['key' => 'c', 'time' => '2026-11-14 10:00:00', 'actor' => 'Chidi Eze', 'text' => 'attached a file.', 'attachment' => ['name' => 'a.pdf', 'href' => '/a.pdf', 'meta' => '2 KB']],
        ['key' => 'd', 'time' => '2026-11-10 10:00:00', 'text' => 'Created.'],
    ];

    it('groups by day under headings with machine-readable times', function () use ($items) {
        $html = wf('<x-ui.timeline :items="$items" group-by-day now="2026-11-15 14:00:00" />', compact('items'));

        expect($html)->toContain('>Today</h3>')->toContain('>Yesterday</h3>')->toContain('>10 Nov 2026</h3>')
            ->toContain('<time datetime="2026-11-15T13:00:00+01:00"')
            ->toContain('Approved')->toContain('Looks good.')->toContain('href="/a.pdf"')
            ->toMatch('/<ol\s+aria-labelledby="timeline-[a-f0-9]+-day-0"/');
    });

    it('hides entries past the limit behind an expandable Show more', function () use ($items) {
        $html = wf('<x-ui.timeline :items="$items" :limit="2" now="2026-11-15 14:00:00" />', compact('items'));

        expect(substr_count($html, 'data-timeline-extra'))->toBe(2)
            ->and($html)->toContain('data-timeline-first-extra')->toContain('Show 2 more')
            ->toContain('aria-expanded="false"')->toContain('x-data="{ expanded: false }"');
    });

    it('pages from the server with load more and has an empty state', function () use ($items) {
        expect(wf('<x-ui.timeline :items="$items" :total="40" more-action="loadMore" />', compact('items')))->toContain('Showing 4 of 40')->toContain('wire:click="loadMore"');
        expect(wf('<x-ui.timeline :items="[]" />'))->toContain('No activity yet.');
    });
});

describe('tag input', function () {
    it('is a combobox with a listbox below when it has suggestions', function () {
        $html = wf('<x-ui.tag-input label="Genres" :value="[\'Afrobeats\']" :max="5" :suggestions="[\'Fuji\', \'Juju\']" />');

        expect($html)->toContain('role="combobox"')->toContain('aria-autocomplete="list"')->toContain('role="listbox"')
            ->toContain('top-full')->toContain('$revealBelow($el, $refs.input)')
            ->toContain('x-modelable="tags"')->toContain('aria-live="polite"')
            ->toContain('1 of 5')->toContain('Afrobeats')
            ->toMatch('/aria-describedby="[^"]*-status [^"]*-count"/')
            ->not->toContain('<select')->not->toContain('rounded-full');
    });

    it('is a plain input without suggestions and posts name[] in plain forms', function () {
        $html = wf('<x-ui.tag-input label="Codes" name="codes" />');

        expect($html)->not->toContain('role="combobox"')->toContain('name="codes[]"');
        expect(wf('<x-ui.tag-input label="Codes" name="codes" wire:model="codes" />'))->not->toContain('name="codes[]"')->toContain('wire:model="codes"');
    });
});

describe('avatar group', function () {
    it('names everyone, counts the overflow and takes focus for its tooltip', function () {
        $html = wf('<x-ui.avatar-group :people="[\'Ada Obi\', \'Bola Ade\', \'Chidi Eze\', \'Dayo Bello\', \'Efe Okoro\']" :max="3" label="Reviewers" />');

        expect($html)->toContain('role="img"')->toContain('aria-label="Reviewers: Ada Obi, Bola Ade, Chidi Eze, Dayo Bello and Efe Okoro"')
            ->toContain('+3')->toContain('tabindex="0"')->toContain('data-avatar-tip');
        expect(substr_count($html, 'data-avatar-more'))->toBe(1);
    });

    it('shows everyone when they fit', function () {
        expect(wf('<x-ui.avatar-group :people="[\'Ada Obi\', \'Bola Ade\']" />'))->not->toContain('data-avatar-more')->toContain('aria-label="Ada Obi and Bola Ade"');
    });
});

describe('sortable', function () {
    it('renders named grip handles with instructions and a live region, no Alpine per row', function () {
        $html = wf('<x-ui.sortable label="Methods" action="reorder" :items="[[\'key\' => \'card\', \'label\' => \'Card\'], [\'key\' => \'ussd\', \'label\' => \'USSD\']]" />');

        expect($html)->toContain('data-sortable-action="reorder"')->toContain('aria-label="Reorder Card"')->toContain('data-key="ussd"')
            ->toMatch('/aria-describedby="sortable-[a-f0-9]+-help"/')->toContain('aria-live="polite"')
            ->not->toContain('x-data');
    });

    it('keeps row keys stable when the order changes', function () {
        $a = wf('<x-ui.sortable label="M" :items="[[\'key\' => \'a\', \'label\' => \'A\'], [\'key\' => \'b\', \'label\' => \'B\']]" />');
        $b = wf('<x-ui.sortable label="M" :items="[[\'key\' => \'b\', \'label\' => \'B\'], [\'key\' => \'a\', \'label\' => \'A\']]" />');
        preg_match_all('/wire:key="([^"]+)"/', $a, $ka);
        preg_match_all('/wire:key="([^"]+)"/', $b, $kb);

        expect($ka[1])->toEqualCanonicalizing($kb[1]);
    });

    it('uses native pointer events, no drag library', function () {
        $js = file_get_contents(kit_path('resources/js/sortable-core.js'));

        expect($js)->toContain('pointerdown')->toContain('setPointerCapture')->toContain("'ui-sort'")->toContain('wire.call(action, order)')
            ->and(json_decode(file_get_contents(kit_path('package.json')), true)['devDependencies'])->not->toHaveKey('sortablejs');
    });

    it('accepts only a permutation of the known keys on the server', function () {
        Livewire::test(SortableDemo::class)->call('reorder', ['wallet', 'card', 'transfer', 'mobile', 'ussd'])->assertSet('order.0', 'wallet');
        Livewire::test(SortableDemo::class)->call('reorder', ['card', 'card', 'transfer', 'mobile', 'ussd'])->assertStatus(422);
        Livewire::test(SortableDemo::class)->call('reorder', ['hack'])->assertStatus(422);
    });
});

describe('slider', function () {
    it('renders APG slider thumbs with formatted values', function () {
        $html = wf('<x-ui.slider label="Budget" :min="0" :max="100000" :step="5000" format="money" :value="25000" name="budget" />');

        expect($html)->toContain('role="slider"')->toContain('aria-valuemin="0"')->toContain('aria-valuemax="100000"')
            ->toContain('aria-valuenow="25000"')->toContain('aria-valuetext="₦25,000"')->toContain('aria-orientation="horizontal"')
            ->toContain('tabindex="0"')->toContain('name="budget" value="25000"')->toContain('x-modelable="value"');
    });

    it('links the two thumbs of a dual slider and names each', function () {
        $html = wf('<x-ui.slider label="Followers" dual :min="0" :max="100" :value="[20, 70]" />');

        expect($html)->toContain('aria-label="Minimum followers"')->toContain('aria-label="Maximum followers"')
            ->toMatch('/aria-valuemin="0"\s+aria-valuemax="70"\s+aria-valuenow="20"/')
            ->toMatch('/aria-valuemin="20"\s+aria-valuemax="100"\s+aria-valuenow="70"/')
            ->toContain('20 to 70');
    });

    it('marks a disabled slider without opacity and leaves x-ui.range alone', function () {
        expect(wf('<x-ui.slider label="Volume" disabled />'))->toContain('aria-disabled="true"')->toContain('tabindex="-1"')->not->toContain('opacity');
        expect(wf('<x-ui.range label="Volume" />'))->toContain('type="range"');
    });
});

describe('banner', function () {
    it('renders each tone with AA text, an action link and a dismiss button', function () {
        $html = wf('<x-ui.banner tone="danger" title="Card failed." action-label="Update" action-href="/billing" dismissible persist="none">Plan active.</x-ui.banner>');

        expect($html)->toContain('<section')->toContain('aria-label="Card failed."')->toContain('bg-danger-tint text-danger-strong')
            ->toContain('href="/billing"')->toContain('aria-label="Dismiss announcement"')->toContain('uiBanner')
            ->not->toContain('role="alert"');
        expect(wf('<x-ui.banner>Note.</x-ui.banner>'))->toContain('bg-info-tint text-info-text')->not->toContain('<section')->not->toContain('aria-label=');
    });

    it('hides a locally dismissed banner before first paint', function () {
        expect(wf('<x-ui.banner name="news-1" dismissible>New.</x-ui.banner>'))->toContain('<script>')->toContain('dismiss_banner:news-1');
    });

    it('reads server dismissals from the DismissStore, which defaults to the checklist store', function () {
        expect(app(DismissStore::class))->toBeInstanceOf(SessionChecklistStore::class)
            ->and(app(ChecklistStore::class))->toBeInstanceOf(DismissStore::class);

        app(DismissStore::class)->dismiss('banner:news-2');
        expect(wf('<x-ui.banner name="news-2" dismissible persist="server">New.</x-ui.banner>'))->toBe('')
            ->and(wf('<x-ui.banner name="news-3" dismissible persist="server">New.</x-ui.banner>'))->toContain('New.');
    });

    it('refuses names outside the key pattern', function () {
        wf('<x-ui.banner name="bad name" dismissible>x</x-ui.banner>');
    })->throws(Exception::class);

    it('writes dismissals through WithDismissals for listed keys only', function () {
        Livewire::test(BannerDemo::class)->call('dismissNotice', 'banner:payouts-2026-11');
        expect(app(DismissStore::class)->dismissed('banner:payouts-2026-11'))->toBeTrue();

        Livewire::test(BannerDemo::class)->call('dismissNotice', 'banner:other')->assertStatus(422);
        Livewire::test(BannerDemo::class)->call('dismissNotice', 'bad key!')->assertStatus(422);
    });
});

describe('tree', function () {
    $nodes = [
        ['id' => 'a', 'label' => 'Finance', 'expanded' => true, 'children' => [['id' => 'a1', 'label' => 'Q1'], ['id' => 'a2', 'label' => 'Q2']]],
        ['id' => 'b', 'label' => 'Archive', 'hasChildren' => true],
    ];

    it('renders the APG tree roles, levels and one tab stop', function () use ($nodes) {
        $html = wf('<x-ui.tree label="Files" :nodes="$nodes" />', compact('nodes'));

        expect($html)->toContain('role="tree"')->toContain('aria-label="Files"')->toContain('role="group"')
            ->toContain('aria-level="2"')->toContain('aria-setsize="2"')->toContain('aria-posinset="1"')
            ->toContain('aria-expanded="true"')->toContain('aria-expanded="false"')->toContain('data-lazy')
            ->toContain('wire:ignore')->toMatch('/aria-labelledby="tree-[a-f0-9]+-[a-f0-9]+-label"/');
        expect(substr_count($html, 'tabindex="0"'))->toBe(1);
    });

    it('works out mixed parents for checks and marks the selected row', function () use ($nodes) {
        $checked = wf('<x-ui.tree label="Files" :nodes="$nodes" checkable :value="[\'a1\']" />', compact('nodes'));
        expect($checked)->toContain('aria-multiselectable="true"')->toMatch('/data-id="a"[^>]*>|aria-checked="mixed"/')
            ->and(substr_count($checked, 'aria-checked="mixed"'))->toBe(1)
            ->and(substr_count($checked, 'aria-checked="true"'))->toBe(1);

        $selected = wf('<x-ui.tree label="Files" :nodes="$nodes" selectable value="a2" />', compact('nodes'));
        expect(substr_count($selected, 'aria-selected="true"'))->toBe(1);
    });

    it('answers lazy children for known folders only', function () {
        $demo = new TreeDemo;
        expect($demo->children('archive'))->toHaveCount(3);
        Livewire::test(TreeDemo::class)->call('children', '../etc')->assertStatus(404);
    });

    it('builds lazily loaded rows with textContent, never HTML from the server', function () {
        $js = file_get_contents(kit_path('resources/js/tree-core.js'));
        expect($js)->toContain('label.textContent = String(node.label')->toContain('server.call(o.lazy, item.dataset.id)')->toContain('MAX_CHILDREN');
    });
});

describe('progress family', function () {
    it('keeps x-ui.progress compatible and adds sizes, tones, formats, buffer and label placement', function () {
        expect(wf('<x-ui.progress label="Upload" :value="62" />'))->toContain('<progress')->toContain('value="62"')->toContain('62%')->toContain('scaleX(0.62)')->toContain('h-1');

        $html = wf('<x-ui.progress label="Video" :value="2100000" :max="5000000" :buffer="3400000" format="bytes" size="lg" tone="danger" label-position="end" />');
        expect($html)->toContain('2.1 MB of 5 MB')->toContain('aria-valuetext="2.1 MB of 5 MB"')->toContain('h-3')
            ->toContain('data-tone="danger"')->toContain('data-progress-buffer')->toContain('scaleX(0.68)');

        expect(wf('<x-ui.progress label="Steps" :value="3" :max="5" format="count" label-position="hidden" />'))->toContain('3 of 5')->toContain('sr-only');
        expect(wf('<x-ui.progress label="Old" :value="1" hide-label />'))->toContain('sr-only');
    });

    it('animates the fill with transform only', function () {
        $css = file_get_contents(kit_path('resources/css/tall-ui-kit.css'));
        expect($css)->toContain('transition: transform var(--motion-md) var(--motion-ease-out);')->toContain('transform-origin: 0 50%;');
    });

    it('renders the progress ring as a progressbar, indeterminate without a value', function () {
        $html = wf('<x-ui.progress-ring label="Upload" :value="62" :size="64" show-value tone="success" />');
        expect($html)->toContain('role="progressbar"')->toContain('aria-valuenow="62"')->toContain('aria-valuemin="0"')->toContain('aria-valuemax="100"')->toContain('62%')->toContain('width="64"');

        $busy = wf('<x-ui.progress-ring label="Loading" />');
        expect($busy)->not->toContain('aria-valuenow')->toContain('ui-ring-spin');
    });

    it('renders progress steps as one progressbar with segments and optional labels', function () {
        $html = wf('<x-ui.progress-steps label="Quiz" :total="4" :current="2" :labels="[\'A\', \'B\', \'C\', \'D\']" />');

        expect($html)->toContain('role="progressbar"')->toContain('aria-valuetext="Step 2 of 4"')->toContain('aria-valuemax="4"')
            ->and(substr_count($html, 'ui-steps-segment'))->toBe(4)
            ->and(substr_count($html, 'data-on'))->toBe(2)
            ->and($html)->toContain('aria-current="step"');
    });

    it('shows an upload row with its status word and the actions for its state', function () {
        $uploading = wf('<x-ui.upload-progress name="a-very-long-name.pdf" :size="2400000" :progress="45" cancel-action="cancel(1)" />');
        expect($uploading)->toContain('2.4 MB · Uploading, 45%')->toContain('wire:click="cancel(1)"')->toContain('aria-label="Cancel upload of a-very-long-name.pdf"')->toContain('wrap-anywhere')->not->toContain('truncate');

        $failed = wf('<x-ui.upload-progress name="a.zip" status="failed" error="Dropped." />');
        expect($failed)->toContain('Failed')->toContain('Dropped.')->toContain('data-upload-action="retry"')->toContain('data-upload-action="remove"')->toContain('ui-upload');

        expect(wf('<x-ui.upload-progress name="a.txt" status="done" />'))->toContain('Uploaded')->not->toContain('<progress');
        expect(wf('<x-ui.upload-progress.list for="files" multiple />'))->toContain('uiUploadProgress')->toContain('u0022model\u0022:\u0022files');
    });

    it('follows Livewire upload events in the browser', function () {
        $js = file_get_contents(kit_path('resources/js/upload-progress.js'));
        foreach (['livewire-upload-start', 'livewire-upload-progress', 'livewire-upload-finish', 'livewire-upload-error', 'livewire-upload-cancel', 'cancelUpload'] as $hook) {
            expect($js)->toContain($hook);
        }
    });
});

it('measures every workflow and progress pair', function () {
    $uses = collect(KitContrast::pairs())->pluck(0);

    foreach (['Stepper current marker number', 'Tag chip text', 'Slider track', 'Tree selected row', 'Banner info', 'Banner danger', 'Progress warning fill on its track', 'Progress danger fill against its buffer'] as $use) {
        expect($uses)->toContain($use);
    }
});
