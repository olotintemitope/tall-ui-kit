{{-- Workbench /workflow: SortableDemo. --}}
<div class="min-w-0 space-y-2" data-sortable-demo>
    <x-ui.sortable
        label="Payment methods, in checkout order"
        action="reorder"
        :items="collect($order)->map(fn ($k) => ['key' => $k, 'label' => $methods[$k], 'description' => $k === 'mobile' ? 'MTN MoMo, Airtel Money and OPay, settled within the hour.' : null])->all()"
    />
    <p class="type-meta" data-sortable-saved>Server order: <span class="type-numeric text-ink-body">{{ implode(', ', $order) }}</span> · saved {{ $saves }} {{ $saves === 1 ? 'time' : 'times' }}</p>
</div>
