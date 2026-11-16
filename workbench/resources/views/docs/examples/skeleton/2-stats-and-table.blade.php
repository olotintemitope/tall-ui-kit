{{-- A metrics strip and a table --}}
<div class="flex flex-col gap-6">
    <x-ui.skeleton.stats :count="3" />
    <x-ui.skeleton.table :rows="4" :columns="4" label="Loading payouts" />
</div>
