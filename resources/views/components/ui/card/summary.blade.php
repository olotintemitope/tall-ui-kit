{{--
    Summary card: one metric. Label (label role), figure, optional x-ui.delta, optional
    sparkline (only when the trend means something), optional sub line (caption).
    Three or more metrics in a row read better as an x-ui.stat-strip.
    Props: label, value (null shows a muted dash), money, currency, previous (adds the
    delta), period ("30d"), upIsGood, sparkline (list of numbers), sparklineLabel,
    sub, loading, error (message). Slot: value (replaces `value`).
--}}
@props(['label', 'value' => null, 'money' => false, 'currency' => 'NGN', 'previous' => null, 'period' => null, 'upIsGood' => true, 'sparkline' => null, 'sparklineLabel' => null, 'sub' => null, 'loading' => false, 'error' => null])

<x-ui.card {{ $attributes->class(['p-4']) }} data-card-summary>
    @if ($loading)
        <div role="status">
            <span class="sr-only">Loading {{ $label }}</span>
            <x-ui.skeleton.line role="label" width="w-20" />
            <x-ui.skeleton.line role="figure" width="w-28" class="mt-1" />
            <x-ui.skeleton.line role="meta" width="w-24" class="mt-1" />
        </div>
    @else
        <dl>
            <dt class="type-label wrap-anywhere">{{ $label }}</dt>
            @if ($error)
                <dd class="mt-2"><x-ui.alert tone="danger">{{ $error }}</x-ui.alert></dd>
            @else
                <dd class="mt-1 flex items-end justify-between gap-3">
                    <span class="type-figure min-w-0 wrap-anywhere">
                        @if ($slot->hasActualContent())
                            {{ $slot }}
                        @elseif ($value === null)
                            <span class="text-ink-muted">&ndash;</span>
                        @elseif ($money)
                            <x-ui.money :value="$value" :currency="$currency" />
                        @else
                            {{ \TallUiKit\Support\Format::number($value) }}
                        @endif
                    </span>
                    @if ($sparkline)
                        <x-ui.chart.sparkline :points="$sparkline" :label="$sparklineLabel ?? $label" class="mb-0.5" />
                    @endif
                </dd>
                @if ($previous !== null && $value !== null)
                    <dd class="type-meta mt-1"><x-ui.delta :current="$value" :previous="$previous" :period="$period" :up-is-good="$upIsGood" /></dd>
                @endif
                @if ($sub)
                    <dd class="type-caption mt-1">{{ $sub }}</dd>
                @endif
            @endif
        </dl>
    @endif
</x-ui.card>
