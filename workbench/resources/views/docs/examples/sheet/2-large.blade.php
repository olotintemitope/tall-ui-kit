{{-- A wider sheet (lg, 640px) for filters --}}
<x-ui.button variant="outline" x-data x-on:click="$dispatch('open-dialog', 'filter-sheet')"><x-ui.icon name="sliders-horizontal" /> Filters</x-ui.button>

<x-ui.sheet name="filter-sheet" title="Filter payouts" size="lg">
    <div class="flex flex-col gap-4">
        <x-ui.field label="Status" name="filterStatus">
            <x-ui.select :options="['any' => 'Any status', 'pending' => 'Pending', 'paid' => 'Paid', 'failed' => 'Failed']" selected="any" />
        </x-ui.field>
        <x-ui.field label="Curator" name="filterCurator">
            <x-ui.input placeholder="Name or email" width="full" />
        </x-ui.field>
    </div>
    <x-slot:footer>
        <x-ui.button variant="ghost" size="sm" x-on:click="$dispatch('close-dialog', 'filter-sheet')">Cancel</x-ui.button>
        <x-ui.button size="sm" x-on:click="$dispatch('close-dialog', 'filter-sheet')">Apply filters</x-ui.button>
    </x-slot:footer>
</x-ui.sheet>
