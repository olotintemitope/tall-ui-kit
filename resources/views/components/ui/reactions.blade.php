{{--
    Reactions (docs/components/community.md, "Reactions"): a summary row of the reactions
    something has, each a toggle button with its symbol and count ("👍 3"; your own are
    pressed: aria-pressed, a `control-hover` edge and `surface-muted` fill), and an "Add
    reaction" button that opens a picker below it. Symbols are content here, not
    decoration: each has a text label ("Like") that is part of the button's name, and
    the picker shows the label under each symbol.

    The picker is a Popover API panel (light dismiss, Escape and the top layer for free),
    placed below its button and never flipped (the page scrolls to show it). Keyboard:
    opening focuses the first reaction; Arrow keys move, Home and End jump, Enter or Space
    picks, Escape closes and focus returns to the button. No Alpine per row: one delegated
    listener for the page (resources/js/community-core.js, loaded when a page has one).

    Picking or toggling updates the row at once (optimistic), then calls `action` with the
    subject and the reaction key, e.g. react('post-12', 'love'); a false return or a
    failed request puts the row back and shows a toast. Without `action` it dispatches a
    bubbling `ui-react` event, detail { subject, reaction, on }.

    Props: reactions (list of [key, count, mine]; keys from `options`), options (key =>
    [symbol, label]; Like, Love, Funny, Wow, Sad, Celebrate by default), action, subject
    (the thing reacted to, passed first to `action`), picker (true), size (sm 32px |
    md 36px), label ("Reactions": the group's name), id.
--}}
@props([
    'reactions' => [], 'options' => null, 'action' => null, 'subject' => null, 'picker' => true,
    'size' => 'md', 'label' => 'Reactions', 'id' => null,
])

@php
    $options ??= [
        'like' => ['👍', 'Like'],
        'love' => ['❤️', 'Love'],
        'funny' => ['😂', 'Funny'],
        'wow' => ['😮', 'Wow'],
        'sad' => ['😢', 'Sad'],
        'celebrate' => ['🎉', 'Celebrate'],
    ];
    $id ??= 'reactions-'.substr(md5($label.$subject.json_encode($reactions)), 0, 8);
    $shown = collect($reactions)->filter(fn ($r) => (int) ($r['count'] ?? 0) > 0 && isset($options[$r['key'] ?? '']))->values();
@endphp

<div
    {{ $attributes->class(['ui-reactions flex min-w-0 flex-wrap items-center gap-1.5']) }}
    role="group"
    aria-label="{{ $label }}"
    id="{{ $id }}"
    data-reactions
    @if ($size === 'sm') data-size="sm" @endif
    @if ($action) data-action="{{ $action }}" @endif
    @if ($subject !== null) data-subject="{{ $subject }}" @endif
>@foreach ($shown as $r)<button type="button" class="ui-reaction" aria-pressed="{{ ! empty($r['mine']) ? 'true' : 'false' }}" data-reaction="{{ $r['key'] }}" data-count="{{ (int) $r['count'] }}" wire:key="{{ $id }}-{{ $r['key'] }}"><span class="text-[15px] leading-none" aria-hidden="true">{{ $options[$r['key']][0] }}</span><span class="sr-only">{{ $options[$r['key']][1] }}</span><span class="type-numeric text-ink" data-reaction-count>{{ \TallUiKit\Support\Format::compact((int) $r['count']) }}</span></button>@endforeach @if ($picker)<button type="button" class="ui-reaction-add" popovertarget="{{ $id }}-picker" aria-haspopup="true" aria-expanded="false" aria-label="Add reaction" data-reaction-trigger><x-ui.icon name="smile-plus" class="size-4" /></button><div id="{{ $id }}-picker" popover class="ui-reaction-picker m-0 rounded-lg border border-panel-border bg-popover p-1.5 shadow-popover" role="group" aria-label="Choose a reaction" data-reaction-picker><div class="grid grid-cols-3 gap-1 sm:grid-cols-6">@foreach ($options as $key => [$symbol, $name])<button type="button" class="ui-reaction-option" tabindex="{{ $loop->first ? 0 : -1 }}" data-reaction-pick="{{ $key }}"><span class="text-[18px] leading-6" aria-hidden="true">{{ $symbol }}</span><span class="type-caption text-ink-body">{{ $name }}</span></button>@endforeach</div></div>@endif</div>
