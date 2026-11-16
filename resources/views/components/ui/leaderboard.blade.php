{{--
    A ranking (docs/components/community.md, "Leaderboard"), built on x-ui.ranked-list and
    x-ui.ranked-item: rank, name with a sub line, the value, and the rank change since the
    last period as an arrow plus words ("Up 2", "Down 1", "Same", "New"), so the change
    never relies on colour (up is success text, down danger text, both 4.5:1). The
    viewer's own row is highlighted with a `surface-muted` fill, "You" in its sub line
    and aria-current="true". No medals, no avatars, no podium.

    Props: rows (list of [key, rank, label, sub, value, change (places moved: positive is
    up, 0 same, null unknown), new (false), current (false), href]), label ("Leaderboard":
    the list's name), valueLabel (spoken after each value, e.g. "streams"), format
    (number | compact | none: how a numeric value is shown, number), empty ("Nothing to
    rank yet."), id.
--}}
@props(['rows' => [], 'label' => 'Leaderboard', 'valueLabel' => null, 'format' => 'number', 'empty' => 'Nothing to rank yet.', 'id' => null])

@php
    use TallUiKit\Support\Format;

    $id ??= 'leaderboard-'.substr(md5($label), 0, 6);
    $list = collect($rows)->values();
    $show = fn ($v) => is_numeric($v) ? match ($format) { 'compact' => Format::compact($v), 'none' => (string) $v, default => Format::number($v) } : (string) $v;
@endphp

<x-ui.ranked-list :empty="$empty" {{ $attributes->class(['ui-leaderboard min-w-0']) }} aria-label="{{ $label }}" id="{{ $id }}" data-leaderboard>
    @foreach ($list as $i => $row)
        @php
            $change = $row['change'] ?? null;
            $current = ! empty($row['current']);
            $sub = trim(($current ? 'You' : '').(! empty($row['sub']) ? ($current ? ' · ' : '').$row['sub'] : ''));
        @endphp
        <x-ui.ranked-item
            :rank="$row['rank'] ?? $i + 1"
            :label="$row['label'] ?? ''"
            :sub="$sub !== '' ? $sub : null"
            :href="$row['href'] ?? null"
            :class="$current ? 'px-2 bg-surface-muted' : 'px-2'"
            :aria-current="$current ? 'true' : null"
            wire:key="{{ $id }}-{{ $row['key'] ?? $i }}"
            data-leaderboard-row
        ><span class="flex items-baseline justify-end gap-3"><span>{{ $show($row['value'] ?? '') }}@if ($valueLabel)<span class="sr-only"> {{ $valueLabel }}</span>@endif</span><span class="type-caption inline-flex w-16 shrink-0 items-center justify-end gap-1 whitespace-nowrap" data-rank-change>@if (! empty($row['new']))<span class="text-ink-body">New</span>@elseif ($change === null)<span class="sr-only">No change data</span>@elseif ((int) $change > 0)<x-ui.icon name="arrow-up" class="size-3.5 text-success-text" /><span class="text-success-text">Up {{ (int) $change }}</span>@elseif ((int) $change < 0)<x-ui.icon name="arrow-down" class="size-3.5 text-danger-text" /><span class="text-danger-text">Down {{ abs((int) $change) }}</span>@else<x-ui.icon name="minus" class="size-3.5 text-ink-muted" /><span class="text-ink-muted">Same</span>@endif</span></span></x-ui.ranked-item>
    @endforeach
</x-ui.ranked-list>
