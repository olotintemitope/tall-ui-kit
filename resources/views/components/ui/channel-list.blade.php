{{--
    Groups and channels (docs/components/community.md, "Channel list"), in the
    x-ui.sidebar section look: sections with a label-role heading that collapses
    (a disclosure button with aria-expanded and a turning chevron; x-collapse, 150ms,
    instant under reduced motion), and rows with a prefix ("#", or a lock for a private
    channel), the name and its state. Unread rows have the name in body-strong and a
    small `ink` dot; mentions show a count pill ("3", spoken "3 mentions"); so neither
    relies on colour. The open channel has aria-current="page" and a `surface-muted` fill.
    One Alpine component for the list, none per row; a collapsed section with unread
    channels says so beside its heading ("2 unread").

    Props: sections (list of [key, label, collapsible (true), open (true), channels: list
    of [key, name, href, unread (false), mentions (0), active (false), private (false),
    muted (false)]]), label ("Channels": the nav's name), action (Livewire method,
    called with the key, when a channel has no href), navigate (false: wire:navigate on
    the links), id.
--}}
@props(['sections' => [], 'label' => 'Channels', 'action' => null, 'navigate' => false, 'id' => null])

@php
    $id ??= 'channels-'.substr(md5($label), 0, 6);
    $list = collect($sections)->values()->map(fn ($s, $i) => $s + ['key' => (string) ($s['key'] ?? $i)]);
    $closed = $list->filter(fn ($s) => ($s['collapsible'] ?? true) && ($s['open'] ?? true) === false)->mapWithKeys(fn ($s) => [$s['key'] => true])->all();
@endphp

<nav x-data="{ closed: @js((object) $closed) }" {{ $attributes->class(['ui-channel-list min-w-0']) }} aria-label="{{ $label }}" id="{{ $id }}" data-channel-list>
    @foreach ($list as $section)
        @php
            $skey = $section['key'];
            $js = \Illuminate\Support\Js::from($skey);
            $channels = collect($section['channels'] ?? [])->values();
            $unreadCount = $channels->filter(fn ($c) => ! empty($c['unread']) || ! empty($c['mentions']))->count();
            $collapsible = $section['collapsible'] ?? true;
            $listId = $id.'-'.\Illuminate\Support\Str::slug($skey);
        @endphp
        <div @class(['pt-4 first:pt-1' => ! empty($section['label']), 'pt-1' => empty($section['label'])]) wire:key="{{ $listId }}" data-channel-section="{{ $skey }}">
            @if (! empty($section['label']))
                @if ($collapsible)
                    <button type="button" class="mb-1 flex min-h-6 w-full items-center gap-1.5 rounded-sm px-2.5 text-ink-muted hover:text-ink focus-ring" x-on:click="closed[{{ $js }}] = ! closed[{{ $js }}]" x-bind:aria-expanded="closed[{{ $js }}] ? 'false' : 'true'" aria-expanded="{{ ($section['open'] ?? true) ? 'true' : 'false' }}" aria-controls="{{ $listId }}" data-channel-toggle>
                        <x-ui.icon name="chevron-right" class="motion-chevron size-3.5" />
                        <span class="type-label">{{ $section['label'] }}</span>
                        @if ($unreadCount > 0)<span class="type-caption ml-auto" x-show="closed[{{ $js }}]" @if (($section['open'] ?? true) !== false) x-cloak @endif>{{ $unreadCount }} unread</span>@endif
                    </button>
                @else
                    <p class="type-label px-2.5 pb-2">{{ $section['label'] }}</p>
                @endif
            @endif
            <ul id="{{ $listId }}" class="flex flex-col gap-px" @if (! empty($section['label'])) aria-label="{{ $section['label'] }}" @endif @if ($collapsible) x-show="! closed[{{ $js }}]" x-collapse.duration.150ms @if (($section['open'] ?? true) === false) x-cloak @endif @endif>
                @foreach ($channels as $c)
                    @php
                        $ckey = (string) ($c['key'] ?? $loop->index);
                        $unread = ! empty($c['unread']);
                        $mentions = (int) ($c['mentions'] ?? 0);
                        $classes = 'flex min-h-8 w-full min-w-0 items-center gap-2 rounded-md px-2.5 py-1 text-left hover:bg-surface-subtle focus-ring-inset aria-[current=page]:bg-surface-muted';
                    @endphp

{{-- Markup kept on few lines: whitespace runs are nodes that every morph walks and bytes in every response. --}}
<li class="min-w-0" wire:key="{{ $listId }}-{{ $ckey }}"> @if (! empty($c['href']))<a href="{{ $c['href'] }}" @if ($navigate) wire:navigate @endif class="{{ $classes }}" @if (! empty($c['active'])) aria-current="page" @endif data-channel="{{ $ckey }}">@else<button type="button" @if ($action) wire:click="{{ $action }}({{ \Illuminate\Support\Js::from($ckey) }})" @else x-on:click="$dispatch('ui-channel-open', { key: {{ \Illuminate\Support\Js::from($ckey) }} })" @endif class="{{ $classes }}" @if (! empty($c['active'])) aria-current="page" @endif data-channel="{{ $ckey }}">@endif<span class="flex w-4 shrink-0 justify-center text-ink-muted" aria-hidden="true">@if (! empty($c['private']))<x-ui.icon name="lock" class="size-3.5" />@else<span class="type-body">#</span>@endif</span>@if (! empty($c['private']))<span class="sr-only">Private: </span>@endif<span @class(['min-w-0 flex-1 wrap-anywhere', 'type-body-strong text-ink' => $unread || $mentions > 0, 'type-body text-ink-body' => ! $unread && $mentions === 0 && empty($c['muted']), 'type-body text-ink-muted' => ! $unread && $mentions === 0 && ! empty($c['muted'])])>{{ $c['name'] ?? $ckey }}</span>@if ($mentions > 0)<x-ui.badge tone="info" size="sm">{{ \TallUiKit\Support\Format::compact($mentions) }}<span class="sr-only"> {{ $mentions === 1 ? 'mention' : 'mentions' }}</span></x-ui.badge>@elseif ($unread)<span class="size-2 shrink-0 rounded-full bg-ink" aria-hidden="true" data-channel-unread></span><span class="sr-only">, unread</span>@endif @if (! empty($c['href']))</a>@else</button>@endif </li> @endforeach </ul> </div> @endforeach </nav>
