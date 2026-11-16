{{-- States: disabled, busy and toggle --}}
<div class="flex flex-wrap items-center gap-2">
    <x-ui.button disabled>Approve</x-ui.button>
    <x-ui.button variant="outline" busy>Exporting</x-ui.button>
    <x-ui.button variant="outline" :pressed="true"><x-ui.icon name="eye-off" /> Hide amounts</x-ui.button>
    <x-ui.button href="#" variant="outline">A link that looks like a button</x-ui.button>
</div>
