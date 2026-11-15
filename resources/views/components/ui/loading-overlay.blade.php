{{--
    A region that refreshes while it already shows content. While the target Livewire
    request runs (after a 150ms delay, so fast responses never flicker) the content
    stays exactly as it is (no fading: every figure keeps its contrast), the region gets
    aria-busy="true" and a small spinner is pinned top-right on a white chip with a 1px
    `line` border. No veil, no blur, no centred spinner. The spinner's status text is
    announced once, politely.
    Props: target (wire:target, e.g. "period" or "refresh,period"), label (the
    spinner's status text), busy (draw the loading state statically). Slot: content.
--}}
@props(['target' => null, 'label' => 'Updating', 'busy' => false])

<div
    {{ $attributes->class(['relative']) }}
    @if ($busy) aria-busy="true" @else wire:loading.delay.short.attr="aria-busy" @if ($target) wire:target="{{ $target }}" @endif @endif
    data-loading-overlay
>
    {{ $slot }}
    <span
        @class(['pointer-events-none absolute right-0 top-0 rounded-md border border-line bg-white p-1', 'hidden' => ! $busy, 'flex' => $busy])
        @unless ($busy) wire:loading.delay.short.flex @if ($target) wire:target="{{ $target }}" @endif @endunless
    >
        <x-ui.spinner size="sm" :label="$label" />
    </span>
</div>
