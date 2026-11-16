{{-- Bounds and steps --}}
<div class="flex flex-col gap-4">
    <x-ui.field label="Seats" name="numSeats" hint="From 1 to 50.">
        <x-ui.number-input :min="1" :max="50" :value="5" noun="seats" />
    </x-ui.field>
    <x-ui.field label="Retry attempts" name="numRetries">
        <x-ui.number-input :min="0" :max="5" :value="5" size="sm" />
    </x-ui.field>
</div>
