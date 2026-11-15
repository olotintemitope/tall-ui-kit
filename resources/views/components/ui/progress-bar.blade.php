{{--
    Page and request progress: a 2px `chart-1` (ink, 15.8:1) bar fixed to the top of
    the main area, indeterminate (a solid segment travelling left to right; no
    gradient, glow or tail). Driven by the `progress` Alpine store in resources/js/ui.js,
    which listens to Livewire's request hook and wire:navigate events and shows the bar
    only after 200ms, so quick requests show nothing. Reduced motion: a still bar that
    pulses. Announces nothing itself: the region being loaded carries aria-busy and
    the result, if any, a toast. It sits once in partials/shell/frame, directly under the sticky top bar (56px, 44px in
    focus mode).
--}}
<div
    x-data
    x-show="$store.progress.visible"
    x-cloak
    {{ $attributes->class(['pointer-events-none sticky top-14 z-20 -mb-0.5 h-0.5 w-full overflow-hidden lg:focus-mode:top-11']) }}
    aria-hidden="true"
    data-progress-bar
>
    <div class="h-full w-2/5 bg-chart-1 forced-colors:bg-[CanvasText] animate-progress motion-reduce:w-full motion-reduce:animate-spinner-reduced"></div>
</div>
