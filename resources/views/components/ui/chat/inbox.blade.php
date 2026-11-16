{{--
    A list of conversations (docs/components/community.md, "Inbox"). Each row: an avatar
    (or x-ui.avatar-group for a group chat), the name, the time of the last message, a
    preview of it (wraps; never cut with an ellipsis, so pass a short preview) and an
    unread count pill. The open conversation has aria-current and a `surface-muted` fill;
    unread rows have the name in body-strong and the preview in ink-body (read rows are
    meta), so unread never relies on the pill's colour alone ("3 unread" is spoken).

    Keyboard: the list is one Tab stop (roving tabindex): ArrowUp and ArrowDown move
    between rows, Home and End jump, Enter opens. Rows are links (`href`, with
    wire:navigate when `navigate`) or buttons that call `action` with the key.

    Props: conversations (list of [key, name, avatar (photo URL), people (names, for a
    group), preview, time, unread (count), active, href, muted]), label
    ("Conversations"), action (Livewire method, called with the key), navigate (false),
    empty ("No conversations yet."), tz (Africa/Lagos), now, id.
    Slots: search (above the list, e.g. x-ui.search), filters (e.g. x-ui.segmented).
--}}
@props([
    'conversations' => [], 'label' => 'Conversations', 'action' => null, 'navigate' => false,
    'empty' => 'No conversations yet.', 'tz' => 'Africa/Lagos', 'now' => null, 'id' => null,
])

@php
    use TallUiKit\Support\Format;

    $id ??= 'inbox-'.substr(md5($label), 0, 6);
    $rows = collect($conversations)->values();
    $current = $rows->search(fn ($c) => ! empty($c['active']));
    $tabStop = $current === false ? 0 : $current;
@endphp

<nav {{ $attributes->class(['ui-inbox flex min-w-0 flex-col']) }} aria-label="{{ $label }}" id="{{ $id }}" data-chat-inbox>
    @isset($search)
        <div class="px-3 pt-3">{{ $search }}</div>
    @endisset
    @isset($filters)
        <div class="px-3 pt-3">{{ $filters }}</div>
    @endisset
    @if ($rows->isEmpty())
        <p class="type-meta px-3 py-4">{{ $empty }}</p>
    @else
        <ul class="flex min-w-0 flex-col gap-px p-1.5" x-data x-on:keydown="window.uiInboxKey?.($event)" data-inbox-list>
            @foreach ($rows as $i => $c)
                @php
                    $key = (string) ($c['key'] ?? $i);
                    $unread = (int) ($c['unread'] ?? 0);
                    $people = $c['people'] ?? [];
                    $classes = 'flex w-full min-w-0 items-start gap-3 rounded-md px-2.5 py-2.5 text-left hover:bg-surface-subtle focus-ring-inset aria-[current=true]:bg-surface-muted';
                @endphp

{{-- Markup kept on few lines: whitespace runs are nodes that every morph walks and bytes in every response. --}}
<li class="min-w-0" wire:key="{{ $id }}-{{ $key }}"> @if (! empty($c['href'])) <a href="{{ $c['href'] }}" @if ($navigate) wire:navigate @endif class="{{ $classes }}" tabindex="{{ $i === $tabStop ? 0 : -1 }}" @if (! empty($c['active'])) aria-current="true" @endif data-inbox-row data-key="{{ $key }}"> @else <button type="button" @if ($action) wire:click="{{ $action }}({{ \Illuminate\Support\Js::from($key) }})" @else x-on:click="$dispatch('ui-inbox-open', { key: {{ \Illuminate\Support\Js::from($key) }} })" @endif class="{{ $classes }}" tabindex="{{ $i === $tabStop ? 0 : -1 }}" @if (! empty($c['active'])) aria-current="true" @endif data-inbox-row data-key="{{ $key }}"> @endif <span class="flex w-9 shrink-0 justify-center pt-0.5" aria-hidden="true">@if (count($people) > 1)<x-ui.avatar-group :people="$people" :max="2" size="sm" :focusable="false" />@else<x-ui.avatar :name="$c['name'] ?? ''" :src="$c['avatar'] ?? null" alt="" size="lg" />@endif</span> <span class="flex min-w-0 flex-1 flex-col gap-0.5"> <span class="flex min-w-0 items-baseline justify-between gap-2"> <span @class(['min-w-0 wrap-anywhere text-ink', 'type-body-strong' => $unread > 0, 'type-body' => $unread === 0])>{{ $c['name'] ?? 'Conversation' }}</span> @if (! empty($c['time']))<span class="type-meta shrink-0 whitespace-nowrap"><x-ui.time :value="$c['time']" mode="relative" :tz="$tz" :now="$now" /></span>@endif </span> <span class="flex min-w-0 items-start justify-between gap-2"> <span @class(['type-body min-w-0 wrap-anywhere', 'text-ink-body' => $unread > 0, 'text-ink-muted' => $unread === 0])>{{ $c['preview'] ?? '' }}</span> @if ($unread > 0)<x-ui.badge tone="info" size="sm" class="mt-0.5">{{ Format::compact($unread) }}<span class="sr-only"> unread</span></x-ui.badge>@elseif (! empty($c['muted']))<span class="type-caption shrink-0">Muted</span>@endif </span> </span> @if (! empty($c['href']))</a>@else</button>@endif </li> @endforeach </ul> @endif </nav>
