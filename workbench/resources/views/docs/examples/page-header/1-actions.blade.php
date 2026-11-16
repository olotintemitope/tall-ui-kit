{{-- Title, description and actions --}}
<x-ui.page-header :level="4" title="Payouts" description="Requests waiting for review. Approved payouts leave at 3pm.">
    <x-slot:actions>
        <x-ui.button variant="outline" size="sm"><x-ui.icon name="download" /> Export</x-ui.button>
        <x-ui.button size="sm">Approve all</x-ui.button>
    </x-slot:actions>
</x-ui.page-header>
