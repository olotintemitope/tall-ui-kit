{{-- Spinner sizes and an inline loading line --}}
<div class="flex flex-col gap-4">
    <div class="flex flex-wrap items-center gap-4">
        <x-ui.spinner size="xs" label="Loading payouts" />
        <x-ui.spinner size="sm" label="Loading payouts" />
        <x-ui.spinner label="Loading payouts" />
        <x-ui.spinner size="lg" label="Loading payouts" />
    </div>
    <x-ui.loading-inline label="Loading transactions" />
</div>
