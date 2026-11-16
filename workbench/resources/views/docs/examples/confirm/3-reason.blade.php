{{-- With a required reason --}}
<x-ui.button variant="outline" x-data x-on:click="$dispatch('open-dialog', 'suspend-curator')">Suspend curator</x-ui.button>

<x-ui.confirm.reason
    name="suspend-curator"
    title="Suspend Bola Adewale?"
    description="Their playlists stop taking submissions until you lift it."
    confirm-label="Suspend curator"
    variant="destructive"
    :reasons="['spam' => 'Spam or fake streams', 'payment' => 'Payment dispute', 'terms' => 'Broke the curator terms']"
/>
