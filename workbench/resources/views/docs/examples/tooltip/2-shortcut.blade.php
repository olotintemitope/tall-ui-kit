{{-- Rich content: a label with its shortcut --}}
<x-ui.tooltip position="bottom">
    <x-ui.button variant="outline" size="sm"><x-ui.icon name="search" /> Search</x-ui.button>
    <x-slot:content>Search everything <x-ui.kbd tone="dark">⌘K</x-ui.kbd></x-slot:content>
</x-ui.tooltip>
