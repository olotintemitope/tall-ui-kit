{{-- Workbench /workflow: BannerDemo. --}}
<div class="min-w-0 space-y-2" data-banner-demo>
    <x-ui.banner tone="warning" title="Payouts pause on 20 November." name="payouts-2026-11" persist="server" dismissible variant="inset" action-label="Read the notice" action-href="#banners">
        Requests made that day go out on 21 November.
    </x-ui.banner>
    <x-ui.button variant="ghost" size="sm" wire:click="resetDemo" x-on:click="$nextTick(() => window.location.reload())" data-reset-banner>
        <x-ui.icon name="refresh-cw" />
        Bring it back
    </x-ui.button>
</div>
