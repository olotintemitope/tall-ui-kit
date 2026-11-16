{{--
    The Follow toggle of x-ui.member-card and x-ui.profile-header: "Follow" on the accent
    fill, "Following" in the outline look once on (aria-pressed="true"). The text, not
    only the colour, says the state, and the name always matches what is shown. It is
    optimistic (resources/js/community-core.js): the closest [data-follow-action] (the
    card or header) names the Livewire method, called with the key and the new state; a
    false return or a failed request puts it back with a toast. Without an action a
    bubbling `ui-follow` event, detail { key, on }.
    Props: key (required), following (false), label ("Follow"), name (the person, for
    screen readers: "Follow Ada Obi"), size (sm | md | lg: 32, 36, 40px; sm).
--}}
@props(['key', 'following' => false, 'label' => 'Follow', 'name' => null, 'size' => 'sm'])

@php
    $sizes = ['sm' => 'min-h-8 px-2.5 py-1 text-meta font-medium', 'md' => 'min-h-9 px-3 py-1.5 text-body-strong', 'default' => 'min-h-9 px-3 py-1.5 text-body-strong', 'lg' => 'min-h-10 px-4 py-2 text-body-strong'];
@endphp

<button
    type="button"
    {{ $attributes->class([
        'ui-follow group/follow inline-flex items-center justify-center gap-1.5 rounded-md border font-body transition-colors duration-100 focus-ring',
        'border-primary-hover bg-primary text-on-primary hover:bg-primary-fill-hover active:bg-primary-fill-active',
        'aria-pressed:border-control aria-pressed:bg-surface aria-pressed:text-ink aria-pressed:hover:bg-surface-subtle aria-pressed:active:bg-surface-muted',
        $sizes[$size] ?? $sizes['sm'],
    ]) }}
    aria-pressed="{{ $following ? 'true' : 'false' }}"
    data-follow
    data-key="{{ $key }}"
><span class="group-aria-pressed/follow:hidden">{{ $label }}</span><span class="hidden group-aria-pressed/follow:inline">Following</span>@if ($name)<span class="sr-only"> {{ $name }}</span>@endif</button>
