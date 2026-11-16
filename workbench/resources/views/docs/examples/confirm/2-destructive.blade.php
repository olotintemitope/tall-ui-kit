{{-- Destructive, with a name to type first --}}
<x-ui.button variant="destructive" x-data x-on:click="$dispatch('open-dialog', 'delete-campaign')">Delete campaign</x-ui.button>

<x-ui.confirm.destructive
    name="delete-campaign"
    title="Delete this campaign?"
    description="Its 42 submissions and their messages are removed for everyone."
    confirm-label="Delete campaign"
    confirm-text="Afrobeats Q4 push"
/>
