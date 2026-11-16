{{-- A loading toast that resolves --}}
<div x-data>
    <x-ui.button variant="outline" x-on:click="$toast.promise(
        new Promise((resolve) => setTimeout(resolve, 1500)),
        { loading: 'Exporting 1,204 payouts', success: 'Export ready.', error: 'Export failed.' }
    )"><x-ui.icon name="download" /> Export payouts</x-ui.button>
</div>
