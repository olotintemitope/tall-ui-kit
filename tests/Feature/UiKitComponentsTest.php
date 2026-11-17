<?php

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

/*
| Render checks for the x-ui form controls and kit components in
| resources/views/components/ui: id, label and error wiring, aria
| attributes, wire:model passthrough and disabled state.
*/

it('wires a field label, hint and control id together', function () {
    $view = $this->blade('<x-ui.field label="Display name" name="displayName" hint="Up to 60 characters." required><x-ui.input wire:model="displayName" /></x-ui.field>');

    $view->assertSee('for="field-displayname"', false)
        ->assertSee('id="field-displayname"', false)
        ->assertSee('wire:model="displayName"', false)
        ->assertSee('aria-describedby="field-displayname-hint"', false)
        ->assertSee('id="field-displayname-hint"', false)
        ->assertSee('aria-required="true"', false)
        ->assertDontSee('aria-invalid', false)
        ->assertDontSee(' required', false);
});

it('shows the error from the error bag in place of the hint and marks the control invalid', function () {
    $view = $this->withViewErrors(['email' => 'Enter a valid email address.'])
        ->blade('<x-ui.field label="Email" name="email" hint="Work address"><x-ui.input type="email" wire:model="email" /></x-ui.field>');

    $view->assertSee('Enter a valid email address.')
        ->assertSee('id="field-email-error"', false)
        ->assertSee('aria-invalid="true"', false)
        ->assertSee('aria-describedby="field-email-error"', false)
        ->assertSee('aria-live="polite"', false)
        ->assertDontSee('Work address')
        ->assertSee('data-invalid', false);
});

it('accepts an explicit error message on the field', function () {
    $this->blade('<x-ui.field label="Handle" name="handle" error="Taken."><x-ui.input /></x-ui.field>')
        ->assertSee('Taken.')
        ->assertSee('aria-invalid="true"', false)
        ->assertSee('aria-describedby="field-handle-error"', false);
});

it('derives ids and errors from wire:model when used without a field', function () {
    $this->withViewErrors(['form.amount' => 'Too low.'])
        ->blade('<x-ui.input wire:model.live="form.amount" />')
        ->assertSee('id="field-form-amount"', false)
        ->assertSee('wire:model.live="form.amount"', false)
        ->assertSee('aria-invalid="true"', false);
});

it('renders input prefix and suffix inside the border and passes x-model and disabled', function () {
    $this->blade('<x-ui.input x-model="price" disabled><x-slot:prefix>₦</x-slot:prefix><x-slot:suffix>per day</x-slot:suffix></x-ui.input>')
        ->assertSeeInOrder(['₦', '<input', 'per day'], false)
        ->assertSee('x-model="price"', false)
        ->assertSee('disabled', false)
        ->assertSee('data-ui="input"', false);
});

it('wires textarea, select and date controls', function () {
    $this->withViewErrors(['bio' => 'Too long.'])
        ->blade('<x-ui.field label="Bio" name="bio"><x-ui.textarea wire:model="bio" rows="4" /></x-ui.field>')
        ->assertSee('<textarea', false)
        ->assertSee('id="field-bio"', false)
        ->assertSee('rows="4"', false)
        ->assertSee('aria-invalid="true"', false)
        ->assertSee('wire:model="bio"', false);

    $this->blade('<x-ui.field label="Currency" name="currency" hint="Payouts use this."><x-ui.select wire:model="currency" placeholder="Choose" :options="[\'NGN\' => \'Naira\', \'USD\' => \'Dollar\']" disabled /></x-ui.field>')
        ->assertSee('id="field-currency"', false)
        ->assertSee('aria-describedby="field-currency-hint"', false)
        ->assertSee('role="combobox"', false)
        ->assertSee('aria-labelledby="field-currency-label"', false)
        ->assertSeeInOrder(['<div role="option" data-value="" aria-selected="true">Choose</div>', '<div role="option" data-value="NGN">Naira</div>', '<div role="option" data-value="USD">Dollar</div>'], false)
        ->assertSee('wire:model="currency"', false)
        ->assertSee('disabled', false)
        ->assertDontSee('<select', false);

    $this->blade('<x-ui.field label="Launch" name="launchDate"><x-ui.date wire:model="launchDate" min="2026-01-01" /></x-ui.field>')
        ->assertSee('data-ui="date"', false)
        ->assertDontSee('type="date"', false)
        ->assertSee('id="field-launchdate"', false)
        ->assertSee('data-min="2026-01-01"', false)
        ->assertSee('wire:model="launchDate"', false);
});

it('renders a checkbox with its label, description and error', function () {
    $this->withViewErrors(['acceptTerms' => 'Accept the terms.'])
        ->blade('<x-ui.checkbox wire:model="acceptTerms" name="acceptTerms" label="I accept" description="Refund policy applies." disabled />')
        ->assertSee('type="checkbox"', false)
        ->assertSee('for="field-acceptterms"', false)
        ->assertSee('id="field-acceptterms"', false)
        ->assertSee('aria-describedby="field-acceptterms-error field-acceptterms-description"', false)
        ->assertSee('aria-invalid="true"', false)
        ->assertSee('wire:model="acceptTerms"', false)
        ->assertSee('disabled', false)
        ->assertSee('Accept the terms.');
});

it('renders a radio group with the binding on every radio', function () {
    $view = $this->withViewErrors(['visibility' => 'Choose one.'])
        ->blade('<x-ui.radio-group name="visibility" label="Visibility" wire:model="visibility" :options="[\'public\' => \'Public\', \'private\' => \'Private\']" required />');

    $view->assertSee('<fieldset', false)
        ->assertSee('<legend', false)
        ->assertSee('id="field-visibility-public"', false)
        ->assertSee('id="field-visibility-private"', false)
        ->assertSee('aria-invalid="true"', false)
        ->assertSee('aria-describedby="field-visibility-error"', false)
        ->assertSee('Choose one.');

    expect(substr_count((string) $view, 'wire:model="visibility"'))->toBe(2);
});

it('renders an accessible switch bound through x-modelable', function () {
    $this->blade('<x-ui.switch wire:model="notify" label="Email me" description="Weekly." />')
        ->assertSee('role="switch"', false)
        ->assertSee('aria-checked="false"', false)
        ->assertSee('x-modelable="on"', false)
        ->assertSee('wire:model="notify"', false)
        ->assertSee('for="field-notify"', false)
        ->assertSee('aria-describedby="field-notify-description"', false);

    $this->blade('<x-ui.switch name="weekly" label="Weekly" checked disabled />')
        ->assertSee('aria-checked="true"', false)
        ->assertSee('disabled', false)
        ->assertSee('type="hidden" name="weekly"', false);
});

it('renders search with an icon, a clear button and passthrough', function () {
    $this->blade('<x-ui.search wire:model.live="search" placeholder="Search users" />')
        ->assertSee('type="search"', false)
        ->assertSee('wire:model.live="search"', false)
        ->assertSee('aria-label="Search users"', false)
        ->assertSee('aria-label="Clear search"', false);
});

it('renders a file drop area compatible with wire:model uploads', function () {
    $this->withViewErrors(['attachments.0' => 'Too large.'])
        ->blade('<x-ui.field label="Files" name="attachments"><x-ui.file wire:model="attachments" multiple accept=".pdf" /></x-ui.field>')
        ->assertSee('type="file"', false)
        ->assertSee('id="field-attachments"', false)
        ->assertSee('wire:model="attachments"', false)
        ->assertSee('multiple', false)
        ->assertSee('accept=".pdf"', false)
        ->assertSee('livewire-upload-progress', false)
        ->assertSee('aria-invalid="true"', false)
        ->assertSee('Too large.');
});

it('renders form sections, underline tabs and a segmented control', function () {
    $this->blade('<x-ui.form-section title="Profile" description="Public details."><p>fields</p></x-ui.form-section>')
        ->assertSeeInOrder(['Profile', 'Public details.', '<p>fields</p>'], false);

    $this->blade('<x-ui.tabs wire:model.live="tab" :tabs="[\'a\' => \'General\', \'b\' => [\'label\' => \'Queue\', \'count\' => 1200]]"><x-ui.tabs.panel name="a">A</x-ui.tabs.panel></x-ui.tabs>')
        ->assertSee('role="tablist"', false)
        ->assertSee('role="tab"', false)
        ->assertSee('role="tabpanel"', false)
        ->assertSee('x-modelable="tab"', false)
        ->assertSee('wire:model.live="tab"', false)
        ->assertSee('1,200')
        ->assertSee('border-b border-transparent', false)
        ->assertSee('aria-selected:border-secondary', false)
        ->assertDontSee('border-b-2', false)
        ->assertDontSee('rounded-full', false);

    $this->blade('<x-ui.segmented x-model="period" label="Period" :options="[\'7\' => \'7 days\', \'30\' => \'30 days\']" value="30" />')
        ->assertSee('role="radiogroup"', false)
        ->assertSee('aria-label="Period"', false)
        ->assertSee('x-model="period"', false)
        ->assertSee('role="radio"', false);
});

it('renders status badges as filled pills and keeps the old variant names', function (string $variant) {
    $this->blade('<x-ui.badge variant="'.$variant.'">Late</x-ui.badge>')
        ->assertSee('rounded-full', false)
        ->assertSee('Late')
        ->assertDontSee('uppercase', false);
})->with(['neutral', 'success', 'warning', 'info', 'danger', 'outline', 'accent']);

it('keeps button variants and sizes the overview uses', function () {
    $this->blade('<x-ui.button variant="ghost" size="sm" wire:click="refresh" wire:loading.attr="disabled">Refresh</x-ui.button>')
        ->assertSee('type="button"', false)
        ->assertSee('wire:click="refresh"', false)
        ->assertSee('h-8', false);

    $this->blade('<x-ui.button variant="link" size="sm" class="h-auto px-1">Try again</x-ui.button>')
        ->assertSee('px-1', false)
        ->assertDontSee('h-8', false);

    $this->blade('<x-ui.button href="/admin" variant="primary">Home</x-ui.button>')
        ->assertSee('href="/admin"', false)
        ->assertSee('bg-primary', false);
});

it('renders avatar initials and keyboard keys', function () {
    $this->blade('<x-ui.avatar name="Ada Okafor" />')->assertSee('>AO<', false);
    $this->blade('<x-ui.kbd>⌘K</x-ui.kbd>')->assertSee('<kbd', false)->assertSee('⌘K');
});

it('renders a dropdown with menu roles and keyboard handlers', function () {
    $this->blade('<x-ui.dropdown><x-slot:trigger><button>Open</button></x-slot:trigger><x-ui.dropdown.item>Edit</x-ui.dropdown.item><x-ui.dropdown.separator /><x-ui.dropdown.item variant="danger">Delete</x-ui.dropdown.item></x-ui.dropdown>')
        ->assertSee('role="menu"', false)
        ->assertSee('role="menuitem"', false)
        ->assertSee('role="separator"', false)
        ->assertSee('x-on:keydown.down.prevent', false)
        // Escape is handled only while the menu is open, so a closed menu inside a dialog lets the dialog close.
        ->assertSee('x-on:keydown.escape="if (open) { $event.preventDefault(); $event.stopPropagation(); close() }"', false)
        ->assertSee("button.setAttribute('aria-controls', \$refs.panel.id)", false)
        ->assertSee('x-anchor.bottom-end', false);
});

it('renders a dialog with right-aligned footer actions', function () {
    $this->blade('<x-ui.dialog name="edit" title="Edit campaign" description="Applies to new submissions.">Body<x-slot:footer><button>Save</button></x-slot:footer></x-ui.dialog>')
        ->assertSee('role="dialog"', false)
        ->assertSee('aria-labelledby="dialog-edit-title"', false)
        ->assertSee('aria-describedby="dialog-edit-description"', false)
        ->assertSee('justify-end', false);
});

it('renders the pagination bar for plain paginators by default', function () {
    $paginator = new LengthAwarePaginator(range(1, 10), 95, 10, 3, ['path' => '/admin/things']);

    $html = (string) $paginator->links();

    expect($html)
        ->toContain('aria-label="Pagination"')
        ->toContain('21&ndash;30')
        ->toContain('of 95')
        ->toContain('aria-current="page"')
        ->toContain('href="/admin/things?page=4"')
        ->not->toContain('wire:click');

    expect(Paginator::$defaultView)->toBe('tall-ui-kit::components.ui.pagination');
});

it('renders the Livewire pagination view with wire:click page changes', function () {
    $paginator = new LengthAwarePaginator(range(1, 10), 95, 10, 2);

    $html = view('livewire::tailwind', ['paginator' => $paginator, 'elements' => [[1 => '', 2 => '', 3 => '']]])->render();

    expect($html)
        ->toContain('wire:click="nextPage(\'page\')"')
        ->toContain('wire:click="gotoPage(3, \'page\')"')
        ->toContain('aria-label="Pagination"');
});

it('sizes controls to their content and lets the field or a row override it', function () {
    $this->blade('<x-ui.input />')->assertSee('max-w-md', false);
    $this->blade('<x-ui.textarea />')->assertDontSee('max-w-', false);
    // Selects size to their longest label (never a fixed narrow width; text is never cut).
    $this->blade('<x-ui.select :options="[\'a\' => \'All statuses\']" />')->assertSee('data-select-auto', false)->assertSee('data-select-sizer>All statuses<', false)->assertDontSee('max-w-xs', false);
    $this->blade('<x-ui.date />')->assertSee('w-40', false);
    $this->blade('<x-ui.input width="xs" />')->assertSee('w-24', false);
    $this->blade('<x-ui.field label="Amount" name="amount" width="sm"><x-ui.input /></x-ui.field>')->assertSee('max-w-xs', false);
    $this->blade('<x-ui.dropdown width="w-72"><x-slot:trigger><button>Open</button></x-slot:trigger><x-ui.input /></x-ui.dropdown>')->assertSee('max-w-md', false);

    $this->blade('<x-ui.field-row :cols="3"><x-ui.field label="A" name="a"><x-ui.input /></x-ui.field></x-ui.field-row>')
        ->assertSee('md:grid-cols-3', false)
        ->assertSee('gap-x-3', false)
        ->assertSee('[&amp;_[data-control-box]]:max-w-none', false);
});

it('marks required fields with a muted asterisk', function () {
    $this->blade('<x-ui.field label="Email" name="email" required><x-ui.input /></x-ui.field>')
        ->assertSee('<span class="text-ink-muted" aria-hidden="true"> *</span>', false);
});

it('makes the whole checkbox, radio and switch row the click target', function () {
    $this->blade('<x-ui.checkbox name="terms" label="Agree" />')
        ->assertSeeInOrder(['<label for="field-terms"', 'type="checkbox"', 'Agree', '</label>'], false);

    $this->blade('<x-ui.switch name="alerts" label="Alerts" />')
        ->assertSeeInOrder(['<label', 'for="field-alerts"', 'role="switch"', 'Alerts', '</label>'], false);

    $this->blade('<x-ui.radio-group name="plan" variant="cards" :options="[\'a\' => \'Standard\']" />')
        ->assertSeeInOrder(['<label for="field-plan-a"', 'type="radio"', 'Standard', '</label>'], false)
        ->assertSee('has-checked:border-primary-text!', false)
        ->assertSee('gap-3', false);
});

it('keeps a loading button the same width while Livewire runs the action', function () {
    $this->blade('<x-ui.button type="submit" loading="save">Save changes</x-ui.button>')
        ->assertSee('wire:loading.delay.short.class="opacity-0"', false)
        ->assertSee('wire:loading.delay.short.flex', false)
        ->assertSee('wire:target="save"', false)
        ->assertSee('wire:loading.attr="aria-busy"', false)->assertSee('wire:loading.delay.shortest.attr="aria-disabled"', false);
});

it('renders an elevated panel with a footer action bar', function () {
    $this->blade('<x-ui.panel><p>body</p><x-slot:footer><button>Save</button></x-slot:footer></x-ui.panel>')
        ->assertSee('shadow-panel', false)
        ->assertSee('border-panel-border', false)
        ->assertSee('bg-panel-footer', false)
        ->assertSeeInOrder(['<p>body</p>', '<button>Save</button>'], false);

    $this->blade('<x-ui.form-section title="Profile"><p>fields</p></x-ui.form-section>')
        ->assertSee('lg:grid-cols-[280px_minmax(0,1fr)]', false)
        ->assertSee('max-w-[640px]', false)
        ->assertSee('data-panel', false);
});

it('shows the error summary only past three errors', function () {
    $fields = "['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D']";

    $this->withViewErrors(['a' => 'x', 'b' => 'x', 'c' => 'x'])
        ->blade('<x-ui.error-summary :fields="'.$fields.'" />')
        ->assertDontSee('data-error-summary', false);

    $this->withViewErrors(['a' => 'Need A', 'b' => 'x', 'c' => 'x', 'd' => 'x'])
        ->blade('<x-ui.error-summary :fields="'.$fields.'" />')
        ->assertSee('role="alert"', false)
        ->assertSee('4 fields need attention')
        ->assertSee('href="#field-a"', false)
        ->assertSee('Need A');
});

it('renders a dense table with right-aligned numeric cells and row hover', function () {
    $this->blade('<x-ui.table caption="Payouts"><x-slot:head><x-ui.table.th>Name</x-ui.table.th><x-ui.table.th numeric>Amount</x-ui.table.th></x-slot:head><x-ui.table.row wire:key="r1"><x-ui.table.td strong>Ada</x-ui.table.td><x-ui.table.td numeric>1,000</x-ui.table.td></x-ui.table.row></x-ui.table>')
        ->assertSee('<caption class="sr-only">Payouts</caption>', false)
        ->assertSee('scope="col"', false)
        ->assertSee('type-label', false)
        ->assertSee('hover:bg-panel-footer', false)
        ->assertSee('wire:key="r1"', false)
        ->assertSee('type-numeric text-ink text-right', false)
        ->assertSee('whitespace-nowrap', false);
});

it('renders the page header, section label and arrow link', function () {
    $this->blade('<x-ui.page-header eyebrow="Finance" title="Payouts" description="Paid every Friday."><x-slot:actions><button>Export</button></x-slot:actions></x-ui.page-header>')
        ->assertSeeInOrder(['Finance', '<h1 class="type-title min-w-0 wrap-anywhere">', 'Payouts', 'Paid every Friday.', '<button>Export</button>'], false);

    $this->blade('<x-ui.section-label as="h2">Queue</x-ui.section-label>')->assertSee('<h2 class="type-label">Queue</h2>', false);

    $this->blade('<x-ui.link-arrow href="/admin/transactions">All transactions</x-ui.link-arrow>')
        ->assertSee('href="/admin/transactions"', false)
        ->assertSee('All transactions')
        ->assertSee('<svg', false);
});

describe('metrics and lists', function () {
    it('renders a stat strip with figure values, money through privacy mode and an empty dash', function () {
        $this->blade('<x-ui.stat-strip :columns="3"><x-ui.stat label="Users" sub="Registered" :value="1200" /><x-ui.stat label="Revenue" :value="5000" money /><x-ui.stat label="Unknown" /></x-ui.stat-strip>')
            ->assertSee('xl:grid-cols-3', false)
            ->assertSee('border-y border-l border-line', false)
            ->assertSee('border-l border-line', false)
            ->assertSee('type-label', false)
            ->assertSee('type-figure', false)
            ->assertSee('type-caption', false)
            ->assertSee('1,200')
            ->assertSee('Registered')
            ->assertSee('data-money', false)
            ->assertSee('₦5,000')
            ->assertSee('&ndash;', false);

        $this->blade('<x-ui.stat label="Custom"><span>slot value</span></x-ui.stat>')->assertSee('<span>slot value</span>', false);
    });

    it('builds the stats skeleton from the same strip and cells', function () {
        $html = (string) $this->blade('<x-ui.skeleton.stats :count="3" />');

        expect($html)->toContain('data-stat-strip')->toContain('xl:grid-cols-3')
            ->and(substr_count($html, 'animate-pulse'))->toBe(9);
    });

    it('colours a delta by direction and whether up is good', function (int $current, int $previous, bool $upIsGood, string $text, string $tone) {
        $html = (string) $this->blade('<x-ui.delta :current="$current" :previous="$previous" :up-is-good="$upIsGood" period="30d" />', compact('current', 'previous', 'upIsGood'));
        $numeric = str_ends_with($text, '%');

        // Words in the meta role; only a percentage is in the numeric role.
        expect($html)->toContain('class="type-meta"')->not->toContain('whitespace-nowrap')
            ->toContain('class="'.($numeric ? 'type-numeric ' : '').$tone.'">'.$text.'</span> vs prior 30d</span>')
            ->and(substr_count($html, 'type-numeric'))->toBe($numeric ? 1 : 0);
    })->with([
        'rise' => [1124, 1000, true, '+12%', 'text-success-text'],
        'small rise keeps a decimal' => [1050, 1000, true, '+5.0%', 'text-success-text'],
        'fall' => [500, 1000, true, "\u{2212}50%", 'text-danger-text'],
        'rise that is bad' => [1124, 1000, false, '+12%', 'text-danger-text'],
        'flat' => [10, 10, true, 'No change', 'text-ink-muted'],
        'from zero' => [5, 0, true, 'New', 'text-success-text'],
    ]);

    it('renders key and value rows with a sub line', function () {
        $this->blade('<x-ui.kv-list><x-ui.kv label="Pending payouts" sub="3 requests"><x-ui.money :value="9000" /></x-ui.kv></x-ui.kv-list>')
            ->assertSeeInOrder(['<dl', 'Pending payouts', '3 requests', '₦9,000'], false)
            ->assertSee('border-line', false)
            ->assertSee('type-numeric', false);
    });

    it('draws a segment bar from chart tokens and rejects anything else', function () {
        $html = (string) $this->blade('<x-ui.segment-bar label="By type" :segments="$segments" />', ['segments' => [
            ['label' => 'Playlist', 'value' => 3, 'color' => 'chart-1'],
            ['label' => 'Blog', 'value' => 1, 'color' => 'red;background:url(x)'],
            ['label' => 'Social', 'value' => 0, 'color' => 'chart-2'],
        ]]);

        expect($html)->toContain('aria-label="By type: Playlist 3 (75%), Blog 1 (25%), Social 0 (0%)"')
            ->toContain('var(--color-chart-1)')
            ->toContain('var(--color-chart-2)')
            ->not->toContain('url(x)');

        $this->blade('<x-ui.segment-bar empty="No campaigns yet." />')->assertSee('No campaigns yet.');
    });

    it('renders ranked rows, share bars and the empty message', function () {
        $this->blade('<x-ui.ranked-list><x-ui.ranked-item :rank="1" label="Ada" sub="Curator" :value="1500" /></x-ui.ranked-list>')
            ->assertSeeInOrder(['<ol', '1', 'Ada', 'Curator', '1,500'], false);

        $this->blade('<x-ui.ranked-item label="Live" :value="4" :share="0.5" />')
            ->assertSee('width: 50%', false)
            ->assertSee('bg-surface-muted', false)
            ->assertSee('bg-chart-2', false);

        $this->blade('<x-ui.ranked-list empty="Nobody yet.">@foreach ([] as $x) <li></li> @endforeach</x-ui.ranked-list>')->assertSee('Nobody yet.');
    });

    it('renders an alert with a 1px tinted border, a light fill, tone text and no icon', function () {
        $this->blade('<x-ui.alert tone="danger">Failed.</x-ui.alert>')
            ->assertSee('role="alert"', false)
            ->assertSee('rounded-md border px-3 py-2.5 border-danger-edge bg-danger-tint text-danger-strong', false)
            ->assertSee('Failed.')
            ->assertDontSee('border-l-', false)
            ->assertDontSee('<svg', false);

        $this->blade('<x-ui.alert>Note.</x-ui.alert>')
            ->assertSee('role="status"', false)
            ->assertSee('border-line bg-panel-footer text-ink', false);
    });

    it('marks a checked radio with a fill and a choice card with a 1px accent border', function () {
        $html = (string) $this->blade('<x-ui.radio-group name="plan" variant="cards" :options="[\'a\' => \'A\', \'b\' => \'B\']" selected="a" />');

        expect($html)->toContain('checked:border-secondary checked:bg-secondary')
            ->toContain('peer-checked:opacity-100')
            ->toContain('has-checked:border-primary-text! has-checked:bg-primary/5')
            ->not->toContain('border-[5px]')
            ->not->toContain('has-checked:ring');
    });

    it('lets a table cell wrap and puts head slot attributes on the thead', function () {
        $this->blade('<x-ui.table flush><x-slot:head class="hidden md:table-header-group"><x-ui.table.th>Name</x-ui.table.th></x-slot:head><x-ui.table.row><x-ui.table.td wrap>Two lines</x-ui.table.td><x-ui.table.td>One</x-ui.table.td></x-ui.table.row></x-ui.table>')
            ->assertSee('<thead class="hidden md:table-header-group">', false)
            ->assertSee('h-10 px-4 wrap-break-word py-2 type-body', false)
            ->assertDontSee('whitespace-nowrap type-body', false);
    });
});

describe('segmented control', function () {
    it('renders text segments with radio semantics and a roving tab stop', function () {
        $html = (string) $this->blade('<x-ui.segmented wire:model.live="period" label="Period" :options="[\'7\' => \'7d\', \'30\' => \'30d\']" value="30" />');

        expect($html)->toContain('role="radiogroup"')
            ->toContain('aria-label="Period"')
            ->toContain('wire:model.live="period"')
            ->toContain('x-modelable="value"')
            ->toContain('data-segmented="text"')
            ->toContain('border border-line bg-surface-subtle')
            ->toContain('aria-checked:border-control aria-checked:bg-surface aria-checked:text-ink')
            ->not->toContain('<svg')
            ->and(substr_count($html, 'role="radio"'))->toBe(2)
            ->and($html)->toMatch('/data-value="30"[^>]*aria-checked="true"[^>]*tabindex="0"/s')
            ->toMatch('/data-value="7"[^>]*aria-checked="false"[^>]*tabindex="-1"/s');
    });

    it('renders an icon left of each label', function () {
        $html = (string) $this->blade('<x-ui.segmented x-model="layout" label="Layout" :options="[\'list\' => [\'label\' => \'List\', \'icon\' => \'list\'], \'board\' => [\'label\' => \'Board\', \'icon\' => \'kanban\']]" />');

        expect($html)->toContain('data-segmented="icon"')
            ->toContain('x-model="layout"')
            ->toContain('gap-1.5')
            ->toContain('List')
            ->and(substr_count($html, '<svg'))->toBe(2)
            ->and($html)->not->toContain('role="tooltip"');
    });

    it('names icon-only segments with aria-label and a tooltip', function () {
        $html = (string) $this->blade('<x-ui.segmented size="sm" label="Show as" :options="[\'table\' => [\'label\' => \'Table\', \'icon\' => \'table\', \'iconOnly\' => true], \'chart\' => [\'label\' => \'Chart\', \'icon\' => \'chart-column\', \'iconOnly\' => true]]" />');

        expect($html)->toContain('aria-label="Table"')
            ->toContain('aria-label="Chart"')
            ->toContain('size-6')
            ->and(substr_count($html, 'role="tooltip"'))->toBe(2);
    });

    it('disables a segment so the arrow keys skip it and it cannot be the default', function () {
        $html = (string) $this->blade('<x-ui.segmented label="Range" :options="[\'7\' => [\'label\' => \'7d\', \'disabled\' => true], \'30\' => \'30d\']" />');

        expect($html)->toContain('[role=radio]:not(:disabled)')
            ->toMatch('/data-value="7"[^>]*disabled/s')
            ->toMatch('/data-value="30"[^>]*aria-checked="true"/s');
    });

    it('refuses options that mix icon and plain segments', function () {
        $this->blade('<x-ui.segmented label="Mixed" :options="[\'list\' => [\'label\' => \'List\', \'icon\' => \'list\'], \'board\' => \'Board\']" />');
    })->throws(Exception::class, 'give every option an icon or none');
});
