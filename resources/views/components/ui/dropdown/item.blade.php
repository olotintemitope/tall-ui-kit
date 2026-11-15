{{--
    Menu item. Props: href (renders <a>), type (button), variant (default|danger), hint
    (meta text on the right, e.g. a row count), role (menuitem, or menuitemradio /
    menuitemcheckbox with aria-checked). Icons in the slot are sized to 16px.
    data-keep-open keeps the menu open when the item is clicked.
--}}
@props(['href' => null, 'type' => 'button', 'variant' => 'default', 'hint' => null, 'role' => 'menuitem'])

@php
    $classes = [
        'relative flex w-full min-h-8 py-1.5 select-none items-center gap-2 wrap-anywhere rounded-md px-2 font-body text-body text-left outline-none transition-colors duration-100',
        'focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-focus disabled:cursor-not-allowed disabled:bg-transparent disabled:text-ink-muted [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0',
        'text-danger-text hover:bg-danger-tint focus:bg-danger-tint [&_svg]:text-danger-text' => $variant === 'danger',
        'text-ink hover:bg-surface-subtle focus:bg-surface-subtle [&_svg]:text-ink-muted' => $variant !== 'danger',
    ];
    $role = in_array($role, ['menuitem', 'menuitemradio', 'menuitemcheckbox'], true) ? $role : 'menuitem';
@endphp

@if ($href)
    <a href="{{ $href }}" role="{{ $role }}" tabindex="-1" {{ $attributes->class($classes) }}>{{ $slot }}@if (filled($hint))<span class="ml-auto pl-4 type-meta">{{ $hint }}</span>@endif</a>
@else
    <button type="{{ $type }}" role="{{ $role }}" tabindex="-1" {{ $attributes->class($classes) }}>{{ $slot }}@if (filled($hint))<span class="ml-auto pl-4 type-meta">{{ $hint }}</span>@endif</button>
@endif
