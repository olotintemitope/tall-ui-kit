{{-- Select all with a live mixed state --}}
<div x-data="{ picked: ['INV-2048'], all: ['INV-2047', 'INV-2048', 'INV-2049'] }" class="flex flex-col gap-3">
    <x-ui.checkbox name="cbPickAll" label="Select all"
        x-bind:checked="picked.length === all.length"
        x-effect="$el.indeterminate = picked.length > 0 && picked.length < all.length"
        x-on:change="picked = $event.target.checked ? [...all] : []" />
    <div class="flex flex-col gap-2 pl-7">
        <x-ui.checkbox name="cbPick" value="INV-2047" label="INV-2047, Ada Foods" x-model="picked" />
        <x-ui.checkbox name="cbPick" value="INV-2048" label="INV-2048, Kora Studio" x-model="picked" />
        <x-ui.checkbox name="cbPick" value="INV-2049" label="INV-2049, Eko Logistics" x-model="picked" />
    </div>
</div>
