{{-- Portal page: breadcrumbs, boxed header, overflow menu --}}
<x-ui.page-header :level="4" boxed title="Payouts" description="Requests waiting for review."
    :breadcrumbs="[['label' => 'Dashboard', 'href' => '#'], ['label' => 'Finance', 'href' => '#'], ['label' => 'Payouts']]">
    <x-slot:more>
        <x-ui.dropdown.item><x-ui.icon name="download" /> Export CSV</x-ui.dropdown.item>
        <x-ui.dropdown.item><x-ui.icon name="refresh-cw" /> Refresh</x-ui.dropdown.item>
    </x-slot:more>
    <x-slot:actions>
        <x-ui.button variant="outline">Settings</x-ui.button>
        <x-ui.button>Approve all</x-ui.button>
    </x-slot:actions>
</x-ui.page-header>
