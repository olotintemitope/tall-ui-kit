{{-- Icon trigger for a row menu, aligned right --}}
<div class="flex items-center justify-between gap-3 border-y border-line py-2">
    <div class="min-w-0">
        <x-ui.text role="body-strong">Chiamaka Okafor</x-ui.text>
        <x-ui.text role="meta" class="wrap-anywhere">chiamaka.okafor@lagosbeats.ng</x-ui.text>
    </div>
    <x-ui.dropdown>
        <x-slot:trigger>
            <x-ui.button variant="ghost" size="icon" aria-label="Actions for Chiamaka Okafor"><x-ui.icon name="ellipsis" /></x-ui.button>
        </x-slot:trigger>
        <x-ui.dropdown.item>Edit role</x-ui.dropdown.item>
        <x-ui.dropdown.item>Resend invite</x-ui.dropdown.item>
        <x-ui.dropdown.separator />
        <x-ui.dropdown.item variant="danger">Remove from team</x-ui.dropdown.item>
    </x-ui.dropdown>
</div>
