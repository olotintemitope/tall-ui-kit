{{-- A calculated output --}}
<div x-data="{ budget: 250000 }" class="flex flex-col gap-3">
    <x-ui.field label="Campaign budget" name="budget" id="campaign-budget">
        <x-ui.number-input x-model="budget" format="money" :min="5000" :step="5000" :value="250000" />
    </x-ui.field>
    <x-ui.output for="campaign-budget" label="Service fee (5%)" x-text="'₦' + Math.round((budget || 0) * 0.05).toLocaleString('en-NG')">₦12,500</x-ui.output>
</div>
