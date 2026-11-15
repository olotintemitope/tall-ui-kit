{{--
    x-data-table.toolbar: search, filters, column visibility, density, export and the
    result count. Props: table (TableView), initial (true on the first, non-Livewire
    render). Slot: extra controls after the filters.

    Search: x-ui.search, wire:model.live.debounce.300ms="search", placeholder from
    tableSearchPlaceholder() ("Search transactions…"), clear button from the kit.
    Filters: 1 to 3 sit inline (from 640px); with more, or below 640px, a "Filters" button
    with a count badge opens a side sheet holding every filter, plus (below 640px) the
    columns and density controls.
    Right: result count in meta, column menu (checkbox items, required columns disabled),
    density (icon-only segmented), x-ui.export-menu when the component exports.

    The controls are rendered once (initial) inside wire:ignore and read their live state
    from $wire (wire:model, filterHas(), colShown()), so later responses do not resend them,
    a morph never replaces the focused control and typing never loses the caret. Only the
    result count and the Filters badge are re-rendered.
--}}
@props(['table', 'initial' => true])

@php
    /** @var \TallUiKit\Support\DataTable\TableView $table */
    $inline = count($table->filters) > 0 && count($table->filters) <= 3;
    $activeFilters = collect($table->tokens)->pluck('key')->unique()->count();
    $hasColumns = count($table->allColumns) > 1;
@endphp

<div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between" x-data="{ sheet: false }" data-dt-toolbar>
<div class="flex min-w-0 flex-1 flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
<div wire:ignore class="flex min-w-0 flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center" data-dt-toolbar-static>@if ($initial)
    <x-ui.search wire:model.live.debounce.300ms="search" size="sm" width="full" class="max-w-full sm:w-(--dt-search)" style="--dt-search: {{ max(18, mb_strlen($table->placeholder) + 7) }}ch" :placeholder="$table->placeholder" :short-placeholder="'Search '.$table->noun" :aria-label="$table->placeholder" data-fk="search" />
    @if ($inline)
        <div class="hidden flex-wrap items-center gap-2 sm:flex" data-dt-inline-filters>
            @foreach ($table->filters as $filter)
                <x-data-table.filter :filter="$filter" context="inline" :name="$table->name" />
            @endforeach
        </div>
    @endif
@endif</div>
@if ($table->filters !== [] || $hasColumns)
<x-ui.button variant="outline" size="sm" @class(['self-start sm:self-auto', 'sm:hidden' => $inline || $table->filters === []]) x-on:click="sheet = true" aria-haspopup="dialog" x-bind:aria-expanded="sheet ? 'true' : 'false'" data-fk="filters-button" data-dt-filters-button wire:key="{{ $table->name }}-filters-button">
    <x-ui.icon name="sliders-horizontal" />
    {{ $table->filters === [] ? 'View' : 'Filters' }}
    @if ($activeFilters > 0)<x-ui.badge size="sm" variant="neutral"><span class="sr-only">, </span>{{ $activeFilters }}<span class="sr-only"> active</span></x-ui.badge>@endif
</x-ui.button>
@endif
{{ $slot }}
</div>
<div class="flex items-center gap-2">
@if ($table->loaded && $table->total !== null)<p class="type-meta tabular-nums mr-1" data-dt-count>{{ $table->count($table->total) }}</p>@endif
<div wire:ignore class="flex items-center gap-2" data-dt-toolbar-right>@if ($initial)
    @if ($hasColumns)
        <div class="hidden sm:block" data-dt-columns-menu>
            <x-ui.dropdown align="right" width="min-w-56">
                <x-slot:trigger>
                    <x-ui.button variant="outline" size="sm" data-fk="columns-button"><x-ui.icon name="table" />Columns</x-ui.button>
                </x-slot:trigger>
                <x-ui.dropdown.label>Show columns</x-ui.dropdown.label>
                @foreach ($table->allColumns as $column)
                    <x-ui.dropdown.item
                        role="menuitemcheckbox"
                        x-bind:aria-checked="colShown({{ \Illuminate\Support\Js::from($column->key) }}) ? 'true' : 'false'"
                        :aria-disabled="$column->required ? 'true' : null"
                        data-keep-open
                        :disabled="$column->required"
                        :hint="$column->required ? 'Required' : null"
                        wire:click="toggleColumn('{{ $column->key }}')"
                        data-column="{{ $column->key }}"
                    >
                        <x-ui.icon name="check" class="text-ink" x-bind:class="colShown({{ \Illuminate\Support\Js::from($column->key) }}) ? '' : 'invisible'" />
                        {{ $column->label }}
                    </x-ui.dropdown.item>
                @endforeach
                <x-ui.dropdown.separator />
                <x-ui.dropdown.item data-keep-open wire:click="resetColumns">Reset to default</x-ui.dropdown.item>
            </x-ui.dropdown>
        </div>
        <div class="hidden sm:block" data-dt-density><x-ui.segmented :options="['comfortable' => ['label' => 'Comfortable rows', 'icon' => 'menu', 'iconOnly' => true], 'compact' => ['label' => 'Compact rows', 'icon' => 'list', 'iconOnly' => true]]" :value="$table->density" size="sm" label="Row density" wire:model.live="density" /></div>
    @endif
    @if ($table->exports)
        <x-ui.export-menu :name="$table->name.'-export'" :columns="true" selection="selected" />
    @endif
@endif</div>
</div>
<div wire:ignore data-dt-sheet-holder>@if ($initial)
    {{-- Side sheet: every filter; below 640px also columns and density. --}}
    <template x-teleport="body">
        <div x-show="sheet" x-cloak class="fixed inset-0 z-[80]" x-on:keydown.escape.prevent.stop="sheet = false" data-dt-sheet="{{ $table->name }}">
            <div class="fixed inset-0 bg-scrim" x-show="sheet" x-transition.opacity.duration.150ms x-on:click="sheet = false" aria-hidden="true"></div>
            <div
                x-show="sheet"
                x-transition:enter="transition ease-out duration-150 motion-reduce:transition-none"
                x-transition:enter-start="opacity-0 translate-x-2"
                x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-100 motion-reduce:transition-none"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                x-trap.noscroll.inert="sheet"
                {{-- First focus goes to the first filter control, never the close button (the kit's dialog rule). --}}
                x-effect="sheet && setTimeout(() => [...$el.querySelectorAll('input, button, [tabindex]:not([tabindex=\'-1\'])')].find((node) => ! node.hasAttribute('data-sheet-close') && node.getClientRects().length)?.focus(), 0)"
                role="dialog"
                aria-modal="true"
                aria-labelledby="{{ $table->name }}-sheet-title"
                class="fixed inset-y-0 right-0 flex w-full max-w-sm flex-col border-l border-panel-border bg-popover shadow-dialog"
            >
                <div class="flex items-center justify-between border-b border-line px-4 py-3">
                    <h2 id="{{ $table->name }}-sheet-title" class="type-heading">{{ $table->filters === [] ? 'View' : 'Filters' }}</h2>
                    <button type="button" x-on:click="sheet = false" class="flex size-8 items-center justify-center rounded-md text-ink-muted hover:bg-surface-subtle hover:text-ink focus-ring" aria-label="Close" data-sheet-close>
                        <x-ui.icon name="x" class="size-4" />
                    </button>
                </div>
                <div class="flex flex-1 flex-col gap-5 overflow-y-auto px-4 py-4">
                    @foreach ($table->filters as $filter)
                        <x-data-table.filter :filter="$filter" context="sheet" :name="$table->name" />
                    @endforeach

                    @if ($hasColumns)
                        <fieldset class="flex flex-col gap-2 sm:hidden">
                            <legend class="type-field-label mb-1.5">Columns</legend>
                            @foreach ($table->allColumns as $column)
                                <x-ui.checkbox
                                    :id="$table->name.'-sheet-col-'.$column->key"
                                    :label="$column->label"
                                    x-bind:checked="colShown({{ \Illuminate\Support\Js::from($column->key) }})"
                                    :disabled="$column->required"
                                    :description="$column->required ? 'Required' : null"
                                    wire:click="toggleColumn('{{ $column->key }}')"
                                />
                            @endforeach
                        </fieldset>
                        <div class="flex flex-col gap-1.5 sm:hidden">
                            <p class="type-field-label">Row density</p>
                            <x-ui.segmented :options="['comfortable' => 'Comfortable', 'compact' => 'Compact']" :value="$table->density" size="sm" label="Row density" wire:model.live="density" class="self-start" />
                        </div>
                    @endif
                </div>
                <div class="flex items-center justify-between gap-2 border-t border-line px-4 py-3">
                    @if ($table->filters !== [])
                        <x-ui.button variant="ghost" size="sm" wire:click="clearFilters">Clear filters</x-ui.button>
                    @else
                        <span></span>
                    @endif
                    <x-ui.button size="sm" x-on:click="sheet = false">Done</x-ui.button>
                </div>
            </div>
        </div>
    </template>
@endif</div>
</div>
