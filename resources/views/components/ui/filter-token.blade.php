{{--
    Removable active-filter token: the filter's label in body text and an × button,
    in a 1px `control` border (3.65:1) with a 6px radius. Not a pill: pills are status
    badges. The × is a 24px target named "Remove filter: <label>".
    Props: label, removeLabel (overrides the button's accessible name). Click handlers
    (wire:click, x-on:click, @click) go on the remove button; other attributes on the token.
--}}
@props(['label', 'removeLabel' => null])

@php
    $handlers = $attributes->filter(fn ($v, $key) => str_starts_with($key, 'wire:click') || str_starts_with($key, 'x-on:click') || str_starts_with($key, '@click'));
@endphp

<span {{ $attributes->except(array_keys($handlers->getAttributes()))->class(['type-body inline-flex min-h-7 py-0.5 wrap-anywhere max-w-full items-center gap-0.5 rounded-md border border-control bg-surface pl-2 pr-0.5 text-ink']) }} data-filter-token>
    <span class="wrap-anywhere">{{ $label }}</span>
    <button
        type="button"
        aria-label="{{ $removeLabel ?? 'Remove filter: '.$label }}"
        class="inline-flex size-6 shrink-0 items-center justify-center rounded-sm text-ink-muted transition-colors duration-100 hover:bg-surface-subtle hover:text-ink focus-ring"
        {{ $handlers }}
    ><x-ui.icon name="x" class="size-3.5" /></button>
</span>
