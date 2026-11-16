{{-- Beside a label, and icon-only buttons --}}
<div class="flex flex-wrap items-center gap-2">
    <x-ui.button variant="outline"><x-ui.icon name="download" /> Export CSV</x-ui.button>
    <x-ui.button variant="ghost" size="icon" aria-label="Refresh payouts"><x-ui.icon name="refresh-cw" /></x-ui.button>
    <x-ui.button variant="ghost" size="icon" aria-label="Edit invoice INV-2048"><x-ui.icon name="pencil" /></x-ui.button>
    <span class="type-meta inline-flex items-center gap-1"><x-ui.icon name="calendar" class="size-4" /> Paid on 14 Nov 2026</span>
</div>
