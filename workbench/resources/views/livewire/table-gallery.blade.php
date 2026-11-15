{{--
    Workbench /tables: x-data-table on 320 invented payments.
    Demo switches: layout (scroll or stack, the stacked mobile example) and state (live,
    empty, error). The Properties panel below is generated from the component's own
    definitions (WithDataTable::tableProperties()).
--}}
<div data-table-gallery>
    <x-ui.page-header eyebrow="UI kit" title="Tables" description="Server-driven data table on sample payments.">
        <x-slot:actions>
            <x-ui.button variant="ghost" size="sm" wire:click="resetDemo" data-reset-demo>
                <x-ui.icon name="refresh-cw" />
                Reset sample data
            </x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <div wire:ignore class="mb-4 flex flex-wrap items-center gap-x-6 gap-y-3 border-b border-line pb-4">@unless (app('livewire')->isLivewireRequest())
        <div class="flex items-center gap-2">
            <span class="type-meta">Layout</span>
            <x-ui.segmented :options="['scroll' => 'Scroll', 'stack' => 'Stack on phones']" :value="$demoLayout" size="sm" label="Layout" wire:model.live="demoLayout" />
        </div>
        <div class="flex items-center gap-2">
            <span class="type-meta">State</span>
            <x-ui.segmented :options="['live' => 'Live', 'empty' => 'Empty', 'error' => 'Error']" :value="$demoState" size="sm" label="State" wire:model.live="demoState" />
        </div>
    @endunless</div>

    <x-data-table :table="$table" />

    <x-data-table.properties :properties="$properties" name="sample-payments" />
</div>
