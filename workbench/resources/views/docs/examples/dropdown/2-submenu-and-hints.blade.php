{{-- Submenu, hints and a radio group that keeps the menu open --}}
<div x-data="{ sort: 'newest' }">
    <x-ui.dropdown align="left">
        <x-slot:trigger>
            <x-ui.button variant="outline">Export and sort <x-ui.icon name="chevron-down" /></x-ui.button>
        </x-slot:trigger>
        <x-ui.dropdown.sub label="Export" hint="1,204 rows">
            <x-ui.dropdown.item>CSV</x-ui.dropdown.item>
            <x-ui.dropdown.item>Excel</x-ui.dropdown.item>
            <x-ui.dropdown.item>PDF</x-ui.dropdown.item>
        </x-ui.dropdown.sub>
        <x-ui.dropdown.separator />
        <x-ui.dropdown.label>Sort by</x-ui.dropdown.label>
        @foreach (['newest' => 'Newest first', 'oldest' => 'Oldest first', 'amount' => 'Largest amount'] as $key => $label)
            <x-ui.dropdown.item role="menuitemradio" data-keep-open
                x-bind:aria-checked="sort === '{{ $key }}' ? 'true' : 'false'"
                x-on:click="sort = '{{ $key }}'">
                <x-ui.icon name="check" x-bind:class="sort === '{{ $key }}' ? '' : 'invisible'" /> {{ $label }}
            </x-ui.dropdown.item>
        @endforeach
    </x-ui.dropdown>
</div>
