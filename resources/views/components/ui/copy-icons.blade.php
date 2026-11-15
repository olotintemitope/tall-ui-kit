{{--
    Internal to the x-ui.copy-* components: the copy and check icons stacked in one
    16px cell, cross-fading over 150ms (opacity only; instant under reduced motion).
    Props: state (idle, copied or failed, as first drawn).
--}}
@props(['state' => 'idle'])

<span class="inline-grid size-4 shrink-0 [&>svg]:[grid-area:1/1]" data-copy-icons>
    <x-ui.icon name="copy" @class(['transition-opacity duration-150 motion-reduce:transition-none', 'opacity-0' => $state === 'copied']) x-bind:class="{ 'opacity-0': copied }" />
    <x-ui.icon name="check" @class(['transition-opacity duration-150 motion-reduce:transition-none', 'opacity-0' => $state !== 'copied']) x-bind:class="{ 'opacity-0': ! copied }" />
</span>
