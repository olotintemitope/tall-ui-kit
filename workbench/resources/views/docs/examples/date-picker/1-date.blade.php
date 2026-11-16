{{-- Date --}}
<div class="flex flex-wrap gap-4">
    <x-ui.field label="Due date" name="dpDue" hint="Type 15/11/2026 or pick a day.">
        <x-ui.date value="2026-11-30" min="2026-11-01" />
    </x-ui.field>
    <x-ui.field label="Delivery day" name="dpDelivery" hint="Weekdays only.">
        <x-ui.date disabled-dates="weekends" />
    </x-ui.field>
</div>
