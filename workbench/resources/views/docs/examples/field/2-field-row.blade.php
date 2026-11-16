{{-- Side by side with field-row --}}
<div class="flex flex-col gap-4">
    <x-ui.field-row>
        <x-ui.field label="First name" name="rowFirstName">
            <x-ui.input value="Chiamaka" />
        </x-ui.field>
        <x-ui.field label="Last name" name="rowLastName" hint="As on your bank account.">
            <x-ui.input value="Okafor" />
        </x-ui.field>
    </x-ui.field-row>
    <x-ui.field-row cols="3">
        <x-ui.field label="City" name="rowCity"><x-ui.input value="Lagos" /></x-ui.field>
        <x-ui.field label="State" name="rowState"><x-ui.input value="Lagos" /></x-ui.field>
        <x-ui.field label="Postcode" name="rowPostcode"><x-ui.input value="101233" /></x-ui.field>
    </x-ui.field-row>
</div>
