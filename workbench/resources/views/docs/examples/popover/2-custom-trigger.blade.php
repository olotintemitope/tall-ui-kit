{{-- A custom trigger and small controls inside --}}
<x-ui.popover label="Column help" placement="bottom-end" width="w-64">
    <x-slot:trigger>
        <x-ui.button variant="ghost" size="icon-sm" aria-label="What is net revenue?"><x-ui.icon name="circle-help" /></x-ui.button>
    </x-slot:trigger>
    <x-ui.text role="meta">Net revenue is gross payments minus refunds, chargebacks and fees.</x-ui.text>
    <x-ui.link href="#revenue" class="type-meta">Read the revenue guide</x-ui.link>
</x-ui.popover>
