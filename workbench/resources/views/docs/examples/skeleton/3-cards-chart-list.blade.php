{{-- Summary cards, a chart and list rows --}}
<div class="flex flex-col gap-6">
    <x-ui.skeleton.grid :count="2" :columns="2" label="Loading balances" />
    <x-ui.skeleton.chart height="sm" label="Loading the revenue chart" />
    <x-ui.skeleton.list :rows="3" label="Loading recent customers" />
</div>
