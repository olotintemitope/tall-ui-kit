{{--
    A day separator in x-ui.chat: a 1px `line` rule either side of the day in the label
    role ("Today", "Yesterday", "Monday", "14 Nov 2026"). It is a heading (headingLevel,
    3), so screen reader users can jump between days in a long conversation.
    Props: date (any date x-ui.time accepts; the label is worked out in `tz` against
    `now`), label (overrides the worked-out text), headingLevel (3), tz (Africa/Lagos), now.
--}}
@props(['date' => null, 'label' => null, 'headingLevel' => 3, 'tz' => 'Africa/Lagos', 'now' => null])

@php
    use Illuminate\Support\Carbon;

    $tag = 'h'.max(2, min(6, (int) $headingLevel));
    if ($label === null && $date !== null) {
        $at = Carbon::parse($date)->setTimezone($tz)->startOfDay();
        $today = ($now ? Carbon::parse($now) : Carbon::now())->setTimezone($tz)->startOfDay();
        $days = (int) $at->diffInDays($today, false);
        $label = match (true) {
            $days === 0 => 'Today',
            $days === 1 => 'Yesterday',
            $days > 1 && $days < 7 => $at->format('l'),
            default => $at->format('j M Y'),
        };
    }
@endphp

<div {{ $attributes->class(['ui-chat-day flex items-center gap-3 pt-6 pb-2 first:pt-0']) }} data-chat-day><span class="h-px flex-1 bg-line" aria-hidden="true"></span><{{ $tag }} class="type-label shrink-0">{{ $label ?? 'Earlier' }}</{{ $tag }}><span class="h-px flex-1 bg-line" aria-hidden="true"></span></div>
