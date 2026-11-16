{{-- Custom values, and the native datalist --}}
<div class="flex flex-col gap-4">
    <x-ui.field label="Expense category" name="comboCategory" hint="Pick one or type a new category.">
        <x-ui.combobox allow-custom placeholder="Category" :options="['travel' => 'Travel', 'software' => 'Software', 'studio' => 'Studio hire', 'marketing' => 'Marketing']" />
    </x-ui.field>
    <x-ui.field label="City" name="comboCity">
        <x-ui.combobox native :options="['Lagos', 'Abuja', 'Port Harcourt', 'Ibadan', 'Kano']" />
    </x-ui.field>
</div>
