{{-- Phone, password and search --}}
<div class="flex max-w-md flex-col gap-4">
    <x-ui.field label="Phone number" name="inputPhone">
        <x-ui.tel country="+234" value="803 123 4567" />
    </x-ui.field>
    <x-ui.field label="New password" name="inputPassword">
        <x-ui.password autocomplete="new-password" strength />
    </x-ui.field>
    <x-ui.search aria-label="Search invoices" placeholder="Search invoices by number or customer" short-placeholder="Search invoices" width="full" />
</div>
