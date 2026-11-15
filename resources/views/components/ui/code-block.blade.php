{{--
    Block of code or a payload: mono 13/20, panel-footer fill, 1px line border, 8px
    radius, scrolls sideways (the scroll area takes focus so keyboards can scroll it).
    Props: label (caption above the code, e.g. "Webhook payload"), copy (an
    x-ui.copy-button in the caption row: the same copied and failed states as every
    other copy in the kit, focus stays on it). Print the code in the slot with {{ }}
    so Blade escapes it.
--}}
@props(['label' => null, 'copy' => false])

<div
    {{ $attributes->class(['overflow-hidden rounded-lg border border-line bg-panel-footer']) }}
    data-code-block
>
    @if ($label || $copy)
        <div class="flex min-h-10 flex-wrap items-center justify-between gap-x-3 gap-y-1 border-b border-line py-1 pl-3 pr-1">
            <span class="type-caption wrap-anywhere">{{ $label }}</span>
            @if ($copy)
                <x-ui.copy-button size="sm" variant="label" :value="html_entity_decode(strip_tags((string) $slot), ENT_QUOTES | ENT_HTML5)" :aria-label="$label ? 'Copy '.$label : 'Copy code'" />
            @endif
        </div>
    @endif
    <pre
        tabindex="0"
        @if ($label) aria-label="{{ $label }}" @endif
        class="overflow-x-auto p-3 type-numeric leading-5 text-ink focus-visible:outline-2 focus-visible:outline-focus focus-visible:-outline-offset-2"
    ><code>{{ $slot }}</code></pre>
</div>