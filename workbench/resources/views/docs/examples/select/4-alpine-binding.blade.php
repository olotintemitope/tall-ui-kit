{{-- Bound with x-model, small size --}}
<div x-data="{ period: '30d' }" class="flex flex-wrap items-center gap-3">
    <x-ui.select size="sm" x-model="period" aria-label="Period" :options="['7d' => 'Last 7 days', '30d' => 'Last 30 days', '90d' => 'Last 90 days']" />
    <p class="type-meta">Showing payouts for <span class="text-ink" x-text="period"></span>.</p>
</div>
