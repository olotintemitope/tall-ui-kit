{{-- An alert to acknowledge --}}
<x-ui.button variant="outline" x-data x-on:click="$dispatch('open-dialog', 'export-started')">Export statements</x-ui.button>

<x-ui.alert-dialog name="export-started" title="Export started" description="We email the file to finance@kudipay.ng when it is ready." acknowledge-label="Close" />
