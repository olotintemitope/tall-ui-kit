{{-- Dates and times: absolute, relative and a table format --}}
<x-ui.kv-list>
    <x-ui.kv label="Issued"><x-ui.time value="2026-11-14 10:00:00" /></x-ui.kv>
    <x-ui.kv label="Paid"><x-ui.time value="2026-11-14 16:42:00" with-time /></x-ui.kv>
    <x-ui.kv label="Last sync"><x-ui.time value="2026-11-15 12:00:00" mode="relative" now="2026-11-15 14:00:00" /></x-ui.kv>
    <x-ui.kv label="Due"><x-ui.time value="2026-11-28" format="d/m/Y" /></x-ui.kv>
    <x-ui.kv label="Settled"><x-ui.time :value="null" /></x-ui.kv>
</x-ui.kv-list>
