{{-- Names for icon-only buttons, on four sides --}}
<div class="flex flex-wrap items-center gap-2">
    <x-ui.tooltip text="Copy reference">
        <x-ui.button variant="outline" size="icon" aria-label="Copy reference"><x-ui.icon name="copy" /></x-ui.button>
    </x-ui.tooltip>
    <x-ui.tooltip text="Download receipt" position="bottom">
        <x-ui.button variant="outline" size="icon" aria-label="Download receipt"><x-ui.icon name="download" /></x-ui.button>
    </x-ui.tooltip>
    <x-ui.tooltip text="Previous payout" position="left">
        <x-ui.button variant="outline" size="icon" aria-label="Previous payout"><x-ui.icon name="chevron-left" /></x-ui.button>
    </x-ui.tooltip>
    <x-ui.tooltip text="Next payout" position="right">
        <x-ui.button variant="outline" size="icon" aria-label="Next payout"><x-ui.icon name="chevron-right" /></x-ui.button>
    </x-ui.tooltip>
</div>
