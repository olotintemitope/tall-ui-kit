{{--
    One panel of x-ui.tabs. Props: name (the tab key it belongs to), focusable (default
    true: tabindex="0", so a panel whose first content is text can be reached; false
    for a panel full of small controls such as switches, which a focusable panel around
    them would crowd under WCAG 2.2 target size).
--}}
@props(['name', 'focusable' => true])
@aware(['active' => null, 'tabs' => []])

@php
    // Hidden from the first paint unless it is the active tab, so other panels never flash before Alpine starts.
    $current = $active ?? array_key_first((array) $tabs);
    $hidden = $current !== null && (string) $current !== (string) $name;
@endphp

<div
    role="tabpanel"
    data-tab-panel="{{ $name }}"
    @if ($focusable) tabindex="0" @endif
    @if ($hidden) style="display: none" @endif
    x-show="tab === @js((string) $name)"
    x-bind:id="$id('tabs', @js((string) $name) + '-panel')"
    x-bind:aria-labelledby="$id('tabs', @js((string) $name))"
    {{ $attributes->class(['pt-5 rounded-sm focus-ring']) }}
>{{ $slot }}</div>
