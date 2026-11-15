{{--
    An activity feed (docs/components/workflow.md, "Timeline"): who did what, when. Each
    entry has a time (x-ui.time, machine-readable), an actor, a line of text, an optional
    status pill and an optional body or attachment. Flat: a 1px `line` rail joins the
    entries' markers (the actor's avatar, or a small `control` ring), no cards, no icons
    in tiles.

    Data mode (`items`): each item is [key, time, actor, avatar (photo URL), text, status
    (a label, or [label, variant] with an x-ui.badge variant), body (plain text), attachment
    ([name, href, meta])]. `groupByDay` splits them under day headings ("Today",
    "Yesterday", "14 Nov 2026" in `tz`). `limit` shows the first N and a "Show N more"
    button that reveals the rest in place (no round trip; focus moves to the first new
    entry). For server paging pass `total` and `moreAction` instead: an x-ui.load-more row
    ("Showing 20 of 134") calls the Livewire method.

    Slot mode: x-ui.timeline.item rows in the slot (each with a body slot for rich
    content), optionally inside x-ui.timeline.day groups; `extra` on an item hides it
    until "Show more" (pass `limit` as the number shown so the button can count).

    Accessibility: one <ol> per day under an <hN> (headingLevel, 3); each entry is a <li>
    whose text reads "Ada Obi approved the payout, 2h ago". The reveal button has
    aria-expanded and aria-controls. The hidden entries are display:none (out of the
    accessibility tree) until revealed. One Alpine component per feed, none per entry:
    the reveal is a data attribute on the feed and a CSS rule.

    Props: items, groupByDay (false), limit (null), moreLabel ("Show :count more"),
    total, moreAction, timeMode (relative | absolute, relative), tz (Africa/Lagos), now
    (fixes "now" for tests and the gallery), headingLevel (3), label ("Activity": the
    list's name when there are no day headings), empty ("No activity yet."), id.
--}}
@props([
    'items' => null,
    'groupByDay' => false,
    'limit' => null,
    'moreLabel' => 'Show :count more',
    'total' => null,
    'moreAction' => null,
    'timeMode' => 'relative',
    'tz' => 'Africa/Lagos',
    'now' => null,
    'headingLevel' => 3,
    'label' => 'Activity',
    'empty' => 'No activity yet.',
    'id' => null,
])

@php
    use Illuminate\Support\Carbon;

    // Not from the items: new entries must not change every row's wire:key.
    $id ??= 'timeline-'.substr(md5($label), 0, 6);
    $tag = 'h'.max(2, min(6, (int) $headingLevel));
    $nowAt = ($now ? Carbon::parse($now) : Carbon::now())->setTimezone($tz);
    $parse = fn ($v) => $v === null || $v === '' ? null : (is_numeric($v) ? Carbon::createFromTimestamp((int) $v) : Carbon::parse($v))->setTimezone($tz);

    $rows = $items === null ? null : collect($items)->values()->map(fn (array $item, int $i): array => [
        'key' => (string) ($item['key'] ?? $i),
        'time' => $item['time'] ?? null,
        'at' => $parse($item['time'] ?? null),
        'actor' => $item['actor'] ?? null,
        'avatar' => $item['avatar'] ?? null,
        'text' => (string) ($item['text'] ?? ''),
        'status' => $item['status'] ?? null,
        'body' => $item['body'] ?? null,
        'attachment' => $item['attachment'] ?? null,
        'extra' => $limit !== null && $i >= (int) $limit,
    ]);

    $dayLabel = function (?Carbon $at) use ($nowAt): string {
        if (! $at) {
            return 'Undated';
        }
        $days = (int) $at->copy()->startOfDay()->diffInDays($nowAt->copy()->startOfDay(), false);

        return match ($days) {
            0 => 'Today',
            1 => 'Yesterday',
            default => $at->format('j M Y'),
        };
    };

    $groups = $rows === null ? null : ($groupByDay
        ? $rows->groupBy(fn (array $r): string => $r['at']?->format('Y-m-d') ?? 'none')->map(fn ($g) => ['label' => $dayLabel($g->first()['at']), 'rows' => $g->values()])->values()
        : collect([['label' => null, 'rows' => $rows]]));

    $hiddenCount = $rows?->where('extra', true)->count() ?? 0;
    $more = str_replace(':count', (string) $hiddenCount, $moreLabel);
@endphp

<div
    {{ $attributes->class(['ui-timeline min-w-0']) }}
    id="{{ $id }}"
    data-timeline
    @if ($limit !== null) x-data="{ expanded: false }" x-bind:data-expanded="expanded ? '' : null" @endif
>
    @if ($rows !== null && $rows->isEmpty())
        <p class="type-meta py-2">{{ $empty }}</p>
    @elseif ($rows !== null)
        @foreach ($groups as $g => $group)
            @php($allExtra = $group['rows']->every(fn ($r) => $r['extra']))
            <div class="min-w-0" @if ($allExtra) data-timeline-extra @endif>
                @if ($group['label'])
                    <{{ $tag }} id="{{ $id }}-day-{{ $g }}" class="type-label pt-1 pb-2 @if (! $loop->first) mt-4 @endif">{{ $group['label'] }}</{{ $tag }}>
                @endif
                <ol @if ($group['label']) aria-labelledby="{{ $id }}-day-{{ $g }}" @else aria-label="{{ $label }}" @endif class="min-w-0">
                    @foreach ($group['rows'] as $row)
                        <x-ui.timeline.item
                            :time="$row['time']"
                            :actor="$row['actor']"
                            :avatar="$row['avatar']"
                            :text="$row['text']"
                            :status="$row['status']"
                            :attachment="$row['attachment']"
                            :extra="$row['extra']"
                            :time-mode="$timeMode"
                            :tz="$tz"
                            :now="$now"
                            :first-extra="$row['extra'] && $rows->firstWhere('extra', true)['key'] === $row['key']"
                            wire:key="{{ $id }}-{{ $row['key'] }}"
                        >@if ($row['body'] !== null)<p class="type-body wrap-anywhere whitespace-pre-line">{{ $row['body'] }}</p>@endif</x-ui.timeline.item>
                    @endforeach
                </ol>
            </div>
        @endforeach
    @else
        <ol aria-label="{{ $label }}" class="min-w-0">{{ $slot }}</ol>
    @endif

    @if ($limit !== null && ($hiddenCount > 0 || $rows === null))
        <div class="pt-2 pl-9" x-show="! expanded">
            <x-ui.button
                variant="link"
                size="sm"
                aria-controls="{{ $id }}"
                aria-expanded="false"
                x-bind:aria-expanded="expanded ? 'true' : 'false'"
                x-on:click="expanded = true; $nextTick(() => $root.querySelector('[data-timeline-first-extra]')?.focus())"
                data-timeline-more
            >{{ $rows === null ? 'Show more' : $more }}</x-ui.button>
        </div>
    @endif

    @if ($moreAction && $total !== null && $rows !== null)
        <x-ui.load-more :shown="$rows->count()" :total="$total" :action="$moreAction" label="Show more" noun="entries" class="pl-9" />
    @endif
</div>
