{{--
    Stacked avatars with a "+N" overflow (docs/components/workflow.md, "Avatar group").
    The first `max` people show as x-ui.avatar (overlapping by a third, each with a 1px
    white edge so the stack reads); the rest are counted in a "+N" disc of the same
    size (ink on `surface-muted`).

    Accessibility: the group is one image named after everyone ("Ada Obi, Bola Ade,
    Chidi Eze and 2 others" lists all of them, `label` prefixes it, e.g. "Reviewers:"),
    so screen readers hear every name and the avatars inside are hidden. It takes
    focus (`focusable`, true) so keyboard users get the same tooltip as the mouse: the
    full list on hover or focus, in the x-ui.tooltip look, placed above the group
    (below when there is no room), fixed so no scroll box clips it, and dismissed with
    Escape (WCAG 1.4.13). No Alpine per group: one delegated listener for the page
    (resources/js/avatar-group.js), so a table full of groups costs nothing.

    Props: people (list of names, or [name, src]), max (4), size (xs | sm | default | lg,
    "sm"), label (prefix for the accessible name), focusable (true), id.
--}}
@props(['people' => [], 'max' => 4, 'size' => 'sm', 'label' => null, 'focusable' => true, 'id' => null])

@php
    $list = collect($people)->map(fn ($p) => is_array($p) ? ['name' => (string) ($p['name'] ?? ''), 'src' => $p['src'] ?? null] : ['name' => (string) $p, 'src' => null])->values();
    $max = max(1, (int) $max);
    // Show the "+N" disc only when it hides two or more: one hidden person takes the disc's place.
    $shown = $list->count() > $max ? $list->take($max - 1) : $list;
    $rest = $list->count() - $shown->count();
    $names = $list->pluck('name');
    $all = $names->count() > 1 ? $names->slice(0, -1)->implode(', ').' and '.$names->last() : $names->implode('');
    $name = ($label ? $label.': ' : '').($all !== '' ? $all : 'Nobody');
    $id ??= 'avatars-'.substr(md5($name), 0, 6);
    $disc = ['xs' => 'size-5 text-[10px]', 'sm' => 'size-6 text-[11px]', 'default' => 'size-7 text-[11px]', 'lg' => 'size-9 text-[12px]'][$size] ?? 'size-6 text-[11px]';
@endphp

<span
    {{ $attributes->class(['ui-avatar-group inline-flex max-w-full items-center rounded-md align-middle', 'focus-ring' => $focusable]) }}
    role="img"
    aria-label="{{ $name }}"
    @if ($focusable) tabindex="0" @endif
    id="{{ $id }}"
    data-avatar-group
><span class="flex items-center" aria-hidden="true">@foreach ($shown as $person)<x-ui.avatar :name="$person['name']" :src="$person['src']" alt="" :size="$size" @class(['outline outline-1 outline-surface', '-ml-1.5' => ! $loop->first]) />@endforeach @if ($rest > 0)<span @class(['-ml-1.5 inline-flex shrink-0 items-center justify-center rounded-full bg-surface-muted px-0.5 font-body font-semibold leading-none text-ink outline outline-1 outline-surface ring-1 ring-inset ring-line', $disc]) data-avatar-more>+{{ $rest }}</span>@endif</span><span class="ui-avatar-tip" aria-hidden="true" data-avatar-tip>{{ $all !== '' ? $all : 'Nobody' }}</span></span>
