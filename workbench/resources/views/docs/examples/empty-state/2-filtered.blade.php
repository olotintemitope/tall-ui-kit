{{-- No results for a filter, with a button action --}}
<x-ui.empty-state title="No transactions match these filters." description="Status is Failed and the date is in the last 7 days.">
    <x-slot:action>
        <x-ui.button variant="outline" size="sm">Clear filters</x-ui.button>
    </x-slot:action>
</x-ui.empty-state>
