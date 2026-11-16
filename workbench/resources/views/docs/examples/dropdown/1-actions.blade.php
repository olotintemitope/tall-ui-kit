{{-- Actions menu with a label, separator and a danger item --}}
<x-ui.dropdown align="left">
    <x-slot:trigger>
        <x-ui.button variant="outline">Actions <x-ui.icon name="chevron-down" /></x-ui.button>
    </x-slot:trigger>
    <x-ui.dropdown.label>Payout PAY-9F3C21AB</x-ui.dropdown.label>
    <x-ui.dropdown.item><x-ui.icon name="eye" /> View details</x-ui.dropdown.item>
    <x-ui.dropdown.item><x-ui.icon name="copy" /> Copy reference</x-ui.dropdown.item>
    <x-ui.dropdown.item href="#statements"><x-ui.icon name="file-text" /> Download statement</x-ui.dropdown.item>
    <x-ui.dropdown.separator />
    <x-ui.dropdown.item variant="danger"><x-ui.icon name="ban" /> Cancel payout</x-ui.dropdown.item>
</x-ui.dropdown>
