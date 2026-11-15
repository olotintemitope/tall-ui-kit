{{--
    A single show/hide region on native <details>/<summary>: it works without
    JavaScript, the summary is the button (Enter and Space toggle, the browser announces
    expanded or collapsed), and find-in-page opens it. With Alpine the height animates
    with x-collapse (150ms, instant under reduced motion) and the 16px chevron turns 90°.
    Look: summary in body-strong (ink), chevron ink-muted on the left; no box, no fill.
    `bordered` adds 1px `line` rules above and below (accordion items use them).
    Focus stays on the summary when it toggles.
    Props: summary (text; or a `summary` slot), open, meta (a meta-role line on the
    right of the summary, e.g. "3 items"), bordered, name (an exclusive group: opening
    one closes the others with the same name; x-ui.accordion sets it).
--}}
@props(['summary' => null, 'open' => false, 'meta' => null, 'bordered' => false, 'name' => null])

<details
    x-data="uiDisclosure(@js((bool) $open))"
    x-bind:data-open="open ? 'true' : 'false'"
    x-on:toggle="synced()"
    @if ($name) data-group="{{ $name }}" x-on:ui-disclosure-open.window="if ($event.target !== $el && $event.target.dataset?.group === @js($name)) collapse()" @endif
    @if ($open) open @endif
    {{ $attributes->class(['group/disclosure min-w-0', 'border-b border-line first-of-type:border-t' => $bordered]) }}
    data-disclosure
>
    <summary
        x-on:click.prevent="toggle()"
        @class([
            'ui-summary flex cursor-pointer items-center gap-2 rounded-sm text-left focus-ring',
            'py-3' => $bordered,
            'py-1' => ! $bordered,
        ])
    >
        <x-ui.icon name="chevron-right" class="ui-chevron size-4 shrink-0 text-ink-muted motion-reduce:transition-none" />
        <span class="type-body-strong min-w-0 flex-1">{{ $summary ?? '' }}@isset($summaryText){{ $summaryText }}@endisset</span>
        @if ($meta)
            <span class="type-meta shrink-0">{{ $meta }}</span>
        @endif
    </summary>
    <div x-show="open" x-collapse.duration.150ms @unless ($open) x-cloak @endunless data-disclosure-panel>
        <div @class(['type-body pl-6', 'pb-4' => $bordered, 'pt-1 pb-2' => ! $bordered])>{{ $slot }}</div>
    </div>
</details>
