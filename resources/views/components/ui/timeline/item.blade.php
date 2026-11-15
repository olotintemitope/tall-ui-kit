{{--
    One entry of x-ui.timeline: marker (the actor's avatar, or a small `control` ring),
    "Actor text" in body (the actor in body-strong), an optional status pill (x-ui.badge,
    one line; it drops below the text on narrow rows instead of being clipped), the time
    (x-ui.time, meta) and an optional body or attachment below. A 1px `line` rail runs
    from the marker to the next entry.

    Props: time, actor, avatar (photo URL), text, status (a label, or [label, variant]),
    attachment ([name, href, meta]), timeMode (relative | absolute), tz, now, extra
    (hidden until the feed's "Show more"), firstExtra (focus target after the reveal).
    Slot: the body (a comment, a diff, a file row); escaped like any Blade slot.
--}}
@props([
    'time' => null, 'actor' => null, 'avatar' => null, 'text' => '', 'status' => null, 'attachment' => null,
    'timeMode' => 'relative', 'tz' => 'Africa/Lagos', 'now' => null, 'extra' => false, 'firstExtra' => false,
])

@php
    [$statusLabel, $statusVariant] = is_array($status) ? [$status['label'] ?? $status[0] ?? null, $status['variant'] ?? $status[1] ?? 'neutral'] : [$status, 'neutral'];
@endphp

{{-- Compact markup: every whitespace run between tags is a text node each morph walks. --}}
<li {{ $attributes->class(['relative flex min-w-0 gap-3 pb-5 outline-none last:pb-1']) }} @if ($extra) data-timeline-extra @endif @if ($firstExtra) data-timeline-first-extra tabindex="-1" @endif data-timeline-item><span class="absolute top-7 bottom-0 left-3 w-px -translate-x-1/2 bg-line" aria-hidden="true" data-timeline-rail></span><span class="flex h-6 w-6 shrink-0 items-center justify-center" aria-hidden="true">@if ($actor)<x-ui.avatar :name="$actor" :src="$avatar" alt="" size="sm" />@else<span class="size-2 rounded-full border border-control bg-surface"></span>@endif</span><div class="min-w-0 flex-1 pt-0.5"><div class="flex flex-wrap items-baseline gap-x-2 gap-y-1"><p class="type-body min-w-0 wrap-anywhere text-ink-body">@if ($actor)<span class="type-body-strong text-ink">{{ $actor }}</span> @endif{{ $text }}@if ($time)<span class="sr-only">, </span>@endif</p>@if ($statusLabel)<x-ui.badge :variant="$statusVariant" size="sm">{{ $statusLabel }}</x-ui.badge>@endif</div>@if ($time)<p class="type-meta"><x-ui.time :value="$time" :mode="$timeMode" :tz="$tz" :now="$now" with-time /></p>@endif @if ($slot->isNotEmpty())<div class="mt-2 min-w-0">{{ $slot }}</div>@endif @if ($attachment)<a href="{{ $attachment['href'] ?? '#' }}" class="mt-2 inline-flex max-w-full items-center gap-2 rounded-md border border-line px-3 py-2 hover:bg-surface-subtle focus-ring"><x-ui.icon name="paperclip" class="size-4 shrink-0 text-ink-muted" /><span class="type-body min-w-0 wrap-anywhere text-ink">{{ $attachment['name'] ?? 'Attachment' }}</span>@if (! empty($attachment['meta']))<span class="type-meta shrink-0">{{ $attachment['meta'] }}</span>@endif</a>@endif</div></li>
