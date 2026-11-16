{{-- A region refreshing while it shows content (busy drawn statically) --}}
<x-ui.loading-overlay busy label="Updating revenue">
    <x-ui.stat-strip :columns="3">
        <x-ui.stat label="Revenue" :value="15250000" money sub="Last 30 days" />
        <x-ui.stat label="Payments" :value="1204" />
        <x-ui.stat label="Refunds" :value="12" />
    </x-ui.stat-strip>
</x-ui.loading-overlay>
