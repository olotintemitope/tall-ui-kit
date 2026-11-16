{{-- Period, bound with x-model --}}
<div x-data="{ period: '30d' }" class="flex flex-wrap items-center gap-3">
    <x-ui.segmented label="Period" x-model="period" :options="['7d' => '7 days', '30d' => '30 days', '90d' => '90 days', '12m' => '12 months']" />
    <x-ui.text role="meta">Showing revenue for <span x-text="period"></span>.</x-ui.text>
</div>
