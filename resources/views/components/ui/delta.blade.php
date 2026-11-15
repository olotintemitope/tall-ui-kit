{{--
    Change against the previous period, e.g. "+12.4% vs prior 30d". The words are
    in the meta role; only the percentage is in the numeric role. The lead (the
    percentage, "New" or "No change") is emerald-700 when the move is good, red-700
    when it is bad, ink-muted when flat. No chip, no icon. "New" when the prior
    period was zero. Under 10% keeps one decimal.
    Props: current, previous, period (e.g. "30d", printed as "vs prior 30d"), upIsGood.
--}}
@props(['current', 'previous', 'period' => null, 'upIsGood' => true])

@php
    $percent = null;
    if ((float) $previous === 0.0) {
        $direction = $current > 0 ? 1 : 0;
        $text = $current > 0 ? 'New' : 'No change';
    } else {
        $pct = ($current - $previous) / abs($previous) * 100;
        $direction = $pct <=> 0;
        $abs = abs($pct);
        $percent = $direction === 0 ? null : ($direction > 0 ? '+' : "\u{2212}").number_format($abs, $abs < 10 ? 1 : 0).'%';
        $text = $percent ?? 'No change';
    }
    $tone = match (true) {
        $direction === 0 => 'text-ink-muted',
        ($direction > 0) === $upIsGood => 'text-emerald-700',
        default => 'text-red-700',
    };
@endphp

<span {{ $attributes->class(['type-meta']) }} data-delta="{{ $direction }}"><span @class(['type-numeric' => $percent !== null, $tone])>{{ $text }}</span>@if ($period) vs prior {{ $period }}@endif</span>
