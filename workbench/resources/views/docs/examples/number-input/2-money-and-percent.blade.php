{{-- Money and percent --}}
<div class="flex flex-col gap-4">
    <x-ui.field label="Minimum payout" name="numMinPayout" hint="Grouped with its sign when you leave the field.">
        <x-ui.number-input format="money" :min="5000" :step="1000" :value="150000" width="md" />
    </x-ui.field>
    <x-ui.field label="Card fee" name="numCardFee">
        <x-ui.number-input format="percent" :min="0" :max="10" :step="0.1" :decimals="1" :value="1.5" />
    </x-ui.field>
    <x-ui.field label="Dollar budget" name="numUsd" error="Enter at most $10,000.">
        <x-ui.number-input format="money" currency="USD" :max="10000" :value="12500" width="md" />
    </x-ui.field>
</div>
