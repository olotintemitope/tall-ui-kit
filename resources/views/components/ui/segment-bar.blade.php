{{--
    One whole split into parts: an 8px bar with 2px gaps and square ends, then a
    legend with each part's count and share. Parts at zero draw nothing in the bar
    but stay in the legend. Colours are chart tokens (chart-1, chart-2, chart-3,
    chart-accent); anything else falls back to chart-1.
    Props: segments (list of {label, value, color}), label (accessible name), empty.
--}}
@props(['segments' => [], 'label' => 'Breakdown', 'empty' => 'Nothing to show.'])

@php
    $total = collect($segments)->sum('value');
    $share = fn (int|float $value): string => $total > 0 ? round($value / $total * 100).'%' : '0%';
    $summary = collect($segments)->map(fn (array $s): string => $s['label'].' '.number_format($s['value']).' ('.$share($s['value']).')')->implode(', ');
    $swatch = fn (?string $color): string => 'var(--color-'.(preg_match('/^chart-[a-z0-9-]+$/', (string) $color) ? $color : 'chart-1').')';
@endphp

<div {{ $attributes }}>
    @if ($total <= 0)
        <p class="type-meta py-2">{{ $empty }}</p>
    @else
        <div role="img" aria-label="{{ $label }}: {{ $summary }}" class="flex h-2 gap-0.5">
            @foreach ($segments as $s)
                @if ($s['value'] > 0)
                    <span class="block h-full forced-color-adjust-none" style="flex: {{ $s['value'] }} 1 0%; background-color: {{ $swatch($s['color'] ?? null) }}" title="{{ $s['label'] }}: {{ number_format($s['value']) }} ({{ $share($s['value']) }})"></span>
                @endif
            @endforeach
        </div>
        <ul class="mt-2 flex flex-wrap gap-x-4 gap-y-1" aria-hidden="true">
            @foreach ($segments as $s)
                <li class="type-caption flex items-center gap-1.5">
                    <span class="inline-block size-2 forced-color-adjust-none" style="background-color: {{ $swatch($s['color'] ?? null) }}"></span>
                    {{ $s['label'] }}
                    <span class="type-numeric text-ink">{{ number_format($s['value']) }}</span>
                    <span class="type-numeric">{{ $share($s['value']) }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
