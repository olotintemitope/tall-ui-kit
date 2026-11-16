{{-- Small, bound with x-model --}}
<div x-data="{ hide: false }" class="flex flex-col gap-3">
    <x-ui.switch size="sm" x-model="hide" label="Hide amounts" />
    <p class="type-body">Balance: <span class="type-numeric" x-text="hide ? '₦••••••' : '₦1,250,000.00'">₦1,250,000.00</span></p>
</div>
