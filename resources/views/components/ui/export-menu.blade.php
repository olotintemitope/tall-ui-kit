{{--
    Export menu for a Livewire component using App\Livewire\Concerns\WithExports.
    Trigger: an outline sm button (download icon, "Export", chevron) that keeps its
    width and shows a spinner while the export runs. Levels:
      CSV          > Current view (filtered), All records, Selected rows (with a selection)
      PDF          > Summary report, Detailed report
      Date range   > All dates, This month, Last month, Last 90 days, Custom... (dialog)
      Choose columns... (x-ui.export-columns), hint "8 of 12 columns"
    Props: name (prefix for the dialogs, default "export"), columns (exportColumnOptions(),
    true to fetch them with $wire.exportColumnOptions() when the menu is first opened,
    which keeps the page's own queries unchanged, or null to hide the picker), rows (current view count, hint), total (all records
    count, hint), selection (name of the Livewire array property holding selected ids),
    formats (csv, pdf), ranges (show the date range level), range (initial preset or
    null for all dates), align, label.
    Calls $wire.export(format, scope, range, columns); the server toasts "Export ready"
    or "We'll email you when it's ready".
--}}
@props([
    'name' => 'export',
    'columns' => null,
    'rows' => null,
    'total' => null,
    'selection' => null,
    'formats' => ['csv', 'pdf'],
    'ranges' => true,
    'range' => null,
    'align' => 'right',
    'label' => 'Export',
])

@php
    use TallUiKit\Support\Exports\ExportRange;

    $presets = ['all' => 'All dates'] + collect(ExportRange::PRESETS)->except('custom')->all();
    $range = is_string($range) && array_key_exists($range, $presets) ? $range : 'all';
    $count = fn (?int $n): ?string => $n === null ? null : number_format($n).' '.($n === 1 ? 'row' : 'rows');
    $lazy = $columns === true;
    $columns = is_array($columns) ? array_values($columns) : ($lazy ? [] : null);
    $checked = $columns === null ? 0 : collect($columns)->where('checked', true)->count();
    $selectionExpr = $selection ? '($wire.'.$selection.' ?? []).length' : '0';

    $alpine = '{'
        .'preset: '.\Illuminate\Support\Js::from($range).','
        .'presets: '.\Illuminate\Support\Js::from($presets).','
        .'from: "", to: "", draftFrom: "", draftTo: "", rangeError: "",'
        .'columnsChecked: '.$checked.','
        .'columnsTotal: '.($columns === null ? 0 : count($columns)).','
        .'columnsLoaded: '.($lazy ? 'false' : 'true').','
        .'columnsLoading: false,'
        .'dialogs: '.\Illuminate\Support\Js::from(['range' => $name.'-range', 'columns' => $name.'-columns']).','
        .<<<JS
        get selectedCount() { return {$selectionExpr} },
        get rangeLabel() {
            if (this.preset !== 'custom') return this.presets[this.preset] ?? 'All dates';
            const fmt = (d) => new Date(d + 'T00:00:00').toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
            return fmt(this.from) + ' to ' + fmt(this.to);
        },
        rangePayload() {
            if (this.preset === 'all') return null;
            return this.preset === 'custom' ? { preset: 'custom', from: this.from, to: this.to } : { preset: this.preset };
        },
        choose(preset) { this.preset = preset; this.closeSub(true) },
        openCustom() {
            this.draftFrom = this.from; this.draftTo = this.to; this.rangeError = '';
            this.close(true);
            this.\$dispatch('open-dialog', this.dialogs.range);
        },
        applyCustom() {
            if (! this.draftFrom || ! this.draftTo) { this.rangeError = 'Enter a start and an end date.'; return }
            if (this.draftFrom > this.draftTo) { this.rangeError = 'The start date must be on or before the end date.'; return }
            this.from = this.draftFrom; this.to = this.draftTo; this.preset = 'custom'; this.rangeError = '';
            this.\$dispatch('close-dialog', this.dialogs.range);
        },
        async loadColumns() {
            if (this.columnsLoaded || this.columnsLoading) return;
            this.columnsLoading = true;
            try {
                const options = await this.\$wire.exportColumnOptions();
                this.columnsChecked = options.filter((c) => c.checked).length;
                this.columnsTotal = options.length;
                this.columnsLoaded = true;
                window.dispatchEvent(new CustomEvent('export-columns-load', { detail: { name: this.dialogs.columns, columns: options } }));
            } finally {
                this.columnsLoading = false;
            }
        },
        async openColumns() {
            this.close(true);
            await this.loadColumns();
            this.\$dispatch('open-dialog', this.dialogs.columns);
        },
        runExport(format, scope, columns = null) {
            if (columns !== null) this.columnsChecked = columns.length;
            return this.\$wire.export(format, scope, this.rangePayload(), columns);
        },
    }
    JS;
@endphp

<div
    x-data="{{ $alpine }}"
    @if ($lazy) x-on:pointerenter.once="loadColumns()" x-on:focusin.once="loadColumns()" @endif
    data-export-menu="{{ $name }}"
    {{ $attributes->class(['inline-flex']) }}
>
    <x-ui.dropdown :align="$align" width="w-max min-w-60 max-w-[calc(100vw-2rem)]">
        <x-slot:trigger>
            <x-ui.button variant="outline" size="sm" loading="export" data-export-trigger>
                <x-ui.icon name="download" />
                {{ $label }}
                <x-ui.icon name="chevron-down" class="text-ink-muted" />
            </x-ui.button>
        </x-slot:trigger>

        @if (in_array('csv', $formats, true))
            <x-ui.dropdown.sub label="CSV" data-export-level="csv">
                <x-ui.dropdown.item :hint="$count($rows)" x-on:click="runExport('csv', 'filtered')" data-export="csv-filtered">Current view (filtered)</x-ui.dropdown.item>
                <x-ui.dropdown.item :hint="$count($total)" x-on:click="runExport('csv', 'all')" data-export="csv-all">All records</x-ui.dropdown.item>
                @if ($selection)
                    <x-ui.dropdown.item x-show="selectedCount > 0" x-cloak x-on:click="runExport('csv', 'selected')" data-export="csv-selected">
                        Selected rows
                        <x-slot:hint><span x-text="selectedCount.toLocaleString('en-GB') + (selectedCount === 1 ? ' row' : ' rows')"></span></x-slot:hint>
                    </x-ui.dropdown.item>
                @endif
            </x-ui.dropdown.sub>
        @endif

        @if (in_array('pdf', $formats, true))
            <x-ui.dropdown.sub label="PDF" data-export-level="pdf">
                <x-ui.dropdown.item hint="Totals" x-on:click="runExport('pdf', 'summary')" data-export="pdf-summary">Summary report</x-ui.dropdown.item>
                <x-ui.dropdown.item hint="Table" x-on:click="runExport('pdf', 'detailed')" data-export="pdf-detailed">Detailed report</x-ui.dropdown.item>
            </x-ui.dropdown.sub>
        @endif

        @if ($ranges)
            <x-ui.dropdown.separator />
            <x-ui.dropdown.sub label="Date range" data-export-level="range">
                <x-slot:hint><span x-text="rangeLabel">{{ $presets[$range] }}</span></x-slot:hint>
                @foreach ($presets as $key => $presetLabel)
                    <x-ui.dropdown.item
                        role="menuitemradio"
                        data-keep-open
                        x-bind:aria-checked="preset === '{{ $key }}' ? 'true' : 'false'"
                        aria-checked="{{ $range === $key ? 'true' : 'false' }}"
                        x-on:click="choose('{{ $key }}')"
                        data-range="{{ $key }}"
                    >
                        {{ $presetLabel }}
                        <x-slot:hint><x-ui.icon name="check" class="text-ink" x-show="preset === '{{ $key }}'" /></x-slot:hint>
                    </x-ui.dropdown.item>
                @endforeach
                <x-ui.dropdown.item
                    role="menuitemradio"
                    x-bind:aria-checked="preset === 'custom' ? 'true' : 'false'"
                    aria-checked="false"
                    x-on:click="openCustom()"
                    data-range="custom"
                >
                    Custom…
                    <x-slot:hint><x-ui.icon name="check" class="text-ink" x-show="preset === 'custom'" x-cloak /></x-slot:hint>
                </x-ui.dropdown.item>
            </x-ui.dropdown.sub>
        @endif

        @if ($columns !== null)
            <x-ui.dropdown.separator />
            <x-ui.dropdown.item x-on:click="openColumns()" data-export="columns">
                Choose columns…
                <x-slot:hint><span x-text="columnsLoaded ? columnsChecked + ' of ' + columnsTotal + ' columns' : ''">@unless ($lazy){{ $checked }} of {{ count($columns) }} columns @endunless</span></x-slot:hint>
            </x-ui.dropdown.item>
        @endif
    </x-ui.dropdown>

    @if ($ranges)
        <x-ui.dialog :name="$name.'-range'" title="Custom date range" description="Dates are in Lagos time (WAT)." width="sm">
            <div class="flex flex-col gap-4">
                <div class="grid grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1.5">
                        <label for="{{ $name }}-range-from" class="type-field-label">From</label>
                        <x-ui.date id="{{ $name }}-range-from" x-model="draftFrom" width="full" x-bind:aria-invalid="rangeError ? 'true' : null" aria-describedby="{{ $name }}-range-error" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label for="{{ $name }}-range-to" class="type-field-label">To</label>
                        <x-ui.date id="{{ $name }}-range-to" x-model="draftTo" width="full" x-bind:aria-invalid="rangeError ? 'true' : null" aria-describedby="{{ $name }}-range-error" />
                    </div>
                </div>
                <p id="{{ $name }}-range-error" class="type-caption text-danger-text!" x-show="rangeError" x-text="rangeError" aria-live="polite"></p>
            </div>
            <x-slot:footer>
                <x-ui.button variant="outline" size="sm" x-on:click="$dispatch('close-dialog', dialogs.range)">Cancel</x-ui.button>
                <x-ui.button size="sm" x-on:click="applyCustom()">Apply range</x-ui.button>
            </x-slot:footer>
        </x-ui.dialog>
    @endif

    @if ($columns !== null)
        <x-ui.export-columns :name="$name.'-columns'" :columns="$columns" :formats="$formats" />
    @endif
</div>
