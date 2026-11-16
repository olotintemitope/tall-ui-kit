{{-- A dialog with a footer --}}
<x-ui.button variant="outline" x-data x-on:click="$dispatch('open-dialog', 'edit-campaign')">Edit campaign</x-ui.button>

<x-ui.dialog name="edit-campaign" title="Edit campaign" description="Changes apply to new submissions only.">
    <x-ui.field label="Campaign name" name="campaignName">
        <x-ui.input value="Afrobeats Q4 push" width="full" />
    </x-ui.field>
    <x-slot:footer>
        <x-ui.button variant="outline" size="sm" x-on:click="$dispatch('close-dialog', 'edit-campaign')">Cancel</x-ui.button>
        <x-ui.button size="sm" x-on:click="$toast({ variant: 'success', title: 'Campaign saved.' }); $dispatch('close-dialog', 'edit-campaign')">Save changes</x-ui.button>
    </x-slot:footer>
</x-ui.dialog>
