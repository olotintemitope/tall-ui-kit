{{--
    One notification row. Title in body-strong (600 while unread), the message in meta
    (two lines at most), then the type and a relative time ("2h ago") whose full
    timestamp is in the <time> title and datetime. Unread shows by weight, a dot and
    "Unread:" for screen readers. Clicking the row opens its target and marks it read
    (`open` action); without a target it only marks it read. A row menu marks it read
    or unread and deletes it.
    Props: id, title, message, time (Carbon or ISO string), read, url, type (label),
    open, toggle, delete (Livewire method names; "" hides that action), selectable
    (a Livewire array property: adds a row checkbox bound to it, for bulk actions), now.
--}}
@props(['id', 'title', 'message' => null, 'time' => null, 'read' => false, 'url' => null, 'type' => null, 'open' => 'openItem', 'toggle' => 'toggleRead', 'delete' => 'delete', 'selectable' => null, 'now' => null])

@php
    $at = $time instanceof \DateTimeInterface ? \Illuminate\Support\Carbon::instance($time) : ($time ? \Illuminate\Support\Carbon::parse($time) : null);
    $relative = $at?->diffForHumans($now ?? now(), \Carbon\CarbonInterface::DIFF_RELATIVE_TO_NOW, true);
    $js = \Illuminate\Support\Js::from((string) $id);
@endphp

<li
    {{ $attributes->merge(['wire:key' => 'notification-'.$id])->class(['group/notification relative flex items-start gap-3 px-4 py-3 hover:bg-panel-footer']) }}
    data-notification-item="{{ $id }}"
    data-read="{{ $read ? 'true' : 'false' }}"
>
    @if ($selectable)
        <span class="relative z-10 flex h-5 w-4 shrink-0 items-center justify-center">
            <input type="checkbox" wire:model.live="{{ $selectable }}" value="{{ $id }}" aria-label="Select: {{ $title }}" data-ui="checkbox" class="peer size-4 cursor-pointer appearance-none rounded-sm border border-control bg-surface checked:border-secondary checked:bg-secondary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus forced-colors:appearance-auto" data-notification-select>
            <x-ui.icon name="check" :stroke-width="3" class="pointer-events-none absolute size-3 text-on-secondary opacity-0 peer-checked:opacity-100 forced-colors:hidden" />
        </span>
    @endif
    <span @class(['mt-[7px] size-2 shrink-0 rounded-full', 'bg-primary-text' => ! $read, 'bg-transparent' => $read]) aria-hidden="true" data-unread-dot></span>

    <div class="min-w-0 flex-1">
        @php $titleClass = 'font-body text-body-strong text-ink '.($read ? 'font-medium' : 'font-semibold'); @endphp
        @if ($open)
            {{-- A real <button> when there is no URL, so Space opens it as well as Enter. --}}
            <{{ $url ? 'a' : 'button' }}
                @if ($url) href="{{ $url }}" @else type="button" @endif
                wire:click.prevent="{{ $open }}({{ $js }})"
                class="{{ $titleClass }} rounded-sm text-left after:absolute after:inset-0 after:content-[''] focus-ring"
                data-notification-open
            >@unless ($read)<span class="sr-only">Unread: </span>@endunless{{ $title }}</{{ $url ? 'a' : 'button' }}>
        @else
            <p class="{{ $titleClass }}">@unless ($read)<span class="sr-only">Unread: </span>@endunless{{ $title }}</p>
        @endif
        @if ($message)
            <p class="type-meta">{{ $message }}</p>
        @endif
        <p class="type-caption mt-0.5">
            @if ($type)<span>{{ $type }}</span><span aria-hidden="true"> · </span>@endif
            @if ($at)<time datetime="{{ $at->toIso8601String() }}" title="{{ $at->format('j M Y, H:i') }}" class="tabular-nums">{{ $relative }}</time>@endif
        </p>
    </div>

    @if ($toggle || $delete)
        <x-ui.dropdown placement="bottom-end" width="w-48" class="relative z-10 shrink-0">
            <x-slot:trigger>
                <button type="button" class="flex size-8 items-center justify-center rounded-md text-ink-muted hover:bg-surface-subtle hover:text-ink focus-ring" aria-label="Actions for {{ $title }}" data-notification-menu>
                    <x-ui.icon name="ellipsis" class="size-4" />
                </button>
            </x-slot:trigger>
            @if ($toggle)
                <x-ui.dropdown.item wire:click="{{ $toggle }}({{ $js }})">
                    <x-ui.icon :name="$read ? 'circle' : 'circle-check'" />
                    {{ $read ? 'Mark as unread' : 'Mark as read' }}
                </x-ui.dropdown.item>
            @endif
            @if ($delete)
                <x-ui.dropdown.item variant="danger" wire:click="{{ $delete }}({{ $js }})">
                    <x-ui.icon name="trash-2" />
                    Delete
                </x-ui.dropdown.item>
            @endif
        </x-ui.dropdown>
    @endif
</li>
