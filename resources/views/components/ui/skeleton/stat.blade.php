{{--
    Loading placeholder for one x-ui.stat cell, same box and rules, and the same height
    as a stat with a sub line (label 16, figure 26, caption 16). It sits in the stat
    strip's <dl>, so it is a dt/dd group: "Loading" for screen readers, the bars hidden.
    Props: sub (default true; false matches a stat without a sub line).
--}}
@props(['sub' => true])

<div {{ $attributes->class(['border-r border-b border-line px-4 py-3']) }} data-skeleton="stat">
    <dt class="sr-only">Loading</dt>
    <dd aria-hidden="true">
        <x-ui.skeleton.line role="label" width="w-20" />
        <x-ui.skeleton.line role="figure" width="w-16" class="mt-1" />
        @if ($sub)
            <x-ui.skeleton.line role="caption" width="w-12" class="mt-1" />
        @endif
    </dd>
</div>
