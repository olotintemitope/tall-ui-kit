{{--
    Kit chart. One look per type (docs/ui-kit.md "Charts"):
      line            time series, 1.5px lines, no fill, points and crosshair on hover;
                      the third series is dashed
      bar             categorical or daily counts, bars at most 24px, 2px top corners,
                      y from 0; one series or up to 3 grouped
      stacked-bar     composition over time, up to 3 segments with a 1px white gap
      bar-horizontal  ranked categories: label (body) left, value (numeric) right;
                      the ranked-list share rows up to 8 rows, denser rows above that
      sparkline       24px trend line for tables and stat strips (x-ui.chart.sparkline)
    Line, bar and stacked-bar are server-rendered SVG and HTML (geometry from
    TallUiKit\Support\Charts\ChartPlot); the `uiChart` Alpine component (resources/js/charts.js)
    only draws the hover crosshair, dots and tooltip and the keyboard read-out.
    Horizontal bars and sparklines are plain markup.

    Anatomy, each part only when asked for: header (title in the label role, period in
    meta, `actions` slot), summary (total in the figure role and x-ui.delta, or the
    `summary` slot), legend (square swatches, label in meta, total in numeric; under the
    summary, never in the plot), plot, and a data table: visually hidden, or shown in
    place of the plot by the chart/table toggle (`toggle`).

    The accessible name is a summary: "Revenue, last 30 days, ₦15 total, down 99% vs
    prior period". Money charts mask axis values, tooltip amounts, table cells and the
    summary's amount under privacy mode.

    Props:
      type, series (list of {name|label, token|color (chart token), points|data,
      total?}), labels (ISO days or category names), height (default 160, sm 120, or
      an h-* class), money, currency, stacked (bar + stacked = stacked-bar), yFormat
      (number | compact | percent), empty (message over an all-zero plot), title,
      period ("Last 30 days"), label (accessible name; defaults to the title), total
      (defaults to the sum of the points when there is a title), previous (prior
      period total, for the delta), deltaPeriod ("30d"), upIsGood, legend (bool),
      toggle (chart/table switch), loading, error (message), alpine (component name),
      motion (enter | none: line and bar plots draw in once, the first time they come
      into view; `none`, or data-motion-static on the chart or an ancestor, shows them
      at once. Never replayed by a morph; resources/js/motion.js, "Chart entrance").
    Slots: actions, summary.
    More than 3 series, or an unknown type, throws in local and testing.
--}}
@props([
    'type' => 'line',
    'series' => [],
    'labels' => [],
    'height' => 'default',
    'money' => false,
    'currency' => 'NGN',
    'stacked' => false,
    'yFormat' => 'number',
    'empty' => 'No data in this period.',
    'title' => null,
    'period' => null,
    'label' => null,
    'total' => null,
    'previous' => null,
    'deltaPeriod' => null,
    'upIsGood' => true,
    'legend' => true,
    'toggle' => false,
    'loading' => false,
    'error' => null,
    'alpine' => 'uiChart',
    'motion' => 'enter',
])

@php
    use TallUiKit\Support\Format;

    $type = $type === 'bar' && $stacked ? 'stacked-bar' : $type;
    $types = ['line', 'bar', 'stacked-bar', 'bar-horizontal', 'sparkline'];
    $strict = app()->environment('local', 'testing');

    if (! in_array($type, $types, true)) {
        if ($strict) {
            throw new InvalidArgumentException("x-ui.chart: unknown type [{$type}]. Use ".implode(', ', $types).'. Pie, donut, gauge, radar and area charts are not part of the kit.');
        }
        $type = 'line';
    }

    if (count($series) > 3 && $strict) {
        throw new InvalidArgumentException('x-ui.chart: at most 3 series, got '.count($series).'. Fold the rest into "Other" or split the chart.');
    }

    $tokenOf = fn (?string $token): string => preg_match('/^chart-[a-z0-9-]+$/', (string) $token) ? $token : 'chart-1';
    $normalised = collect($series)->take(3)->values()->map(function (array $s, int $i) use ($tokenOf, $type): array {
        $points = array_values(array_map(fn ($v): int|float => is_numeric($v) ? $v + 0 : 0, $s['points'] ?? $s['data'] ?? []));

        return [
            'label' => (string) ($s['name'] ?? $s['label'] ?? 'Series '.($i + 1)),
            'data' => $points,
            'color' => $tokenOf($s['token'] ?? $s['color'] ?? null),
            'dashed' => $type === 'line' && $i === 2,
            'total' => $s['total'] ?? array_sum($points),
        ];
    });

    $labels = array_values($labels);
    $isEmpty = $normalised->every(fn (array $s): bool => collect($s['data'])->every(fn ($v): bool => (float) $v === 0.0));
    $sum = $normalised->sum('total');
    $total = $total ?? ($title !== null ? $sum : null);

    $heights = ['default' => 'h-40', 'sm' => 'h-30'];
    $heightClass = $heights[$height] ?? (is_string($height) && str_starts_with($height, 'h-') ? $height : 'h-40');

    $fmt = function (int|float $value) use ($money, $currency, $yFormat): string {
        return match (true) {
            (bool) $money => Format::money($value, $currency),
            $yFormat === 'percent' => Format::percent($value),
            $yFormat === 'compact' => Format::compact($value),
            default => Format::number($value),
        };
    };

    $deltaText = null;
    if ($total !== null && $previous !== null) {
        if ((float) $previous === 0.0) {
            $deltaText = $total > 0 ? 'new vs prior period' : 'no change vs prior period';
        } else {
            $pct = ($total - $previous) / abs($previous) * 100;
            $abs = abs($pct);
            $deltaText = round($pct, 1) == 0 ? 'no change vs prior period' : ($pct > 0 ? 'up ' : 'down ').number_format($abs, $abs < 10 ? 1 : 0).'% vs prior period';
        }
    }

    $summaryParts = fn (string $amount): string => collect([
        $label ?? $title ?? 'Chart',
        $period ? \Illuminate\Support\Str::lcfirst($period) : null,
        $total !== null ? ($amount === '' ? 'total hidden' : $amount.' total') : null,
        $deltaText,
    ])->filter()->implode(', ').($isEmpty ? '. '.$empty : '');
    $ariaLabel = $summaryParts($total !== null ? $fmt($total) : 'x');
    $maskedLabel = $money ? $summaryParts('') : null;

    $swatch = fn (string $token): string => 'var(--color-'.$token.')';
    $isoDay = fn ($l): string => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $l) ? \Illuminate\Support\Carbon::parse($l)->format('j M Y') : (string) $l;
    $plotted = in_array($type, ['line', 'bar', 'stacked-bar'], true);
    $showLegend = $legend && $normalised->count() > 1 && $plotted;
    $hasHeader = $title !== null || isset($actions) || $toggle;
    $hasSummary = isset($summary) || ($title !== null && $total !== null);
    // The entrance hook: plotted, with data, and not opted out. The empty plot never animates.
    $enters = $plotted && ! $isEmpty && $motion !== 'none' && ! $attributes->has('data-motion-static');
@endphp

@if ($type === 'sparkline')
    <x-ui.chart.sparkline :points="$normalised->first()['data'] ?? []" :label="$label ?? $title ?? 'Trend'" {{ $attributes }} />
@else
<div
    @if ($plotted && ! $loading && ! $error) x-data="{{ $alpine }}" @else x-data="{ view: 'chart' }" @endif
    {{ $attributes->class(['min-w-0']) }}
    data-ui-chart="{{ $type }}"
>
    @if ($hasHeader)
        <div class="flex min-h-7 items-center justify-between gap-3">
            <div class="flex min-w-0 flex-wrap items-baseline gap-x-2">
                @if ($title)
                    <h3 class="type-label wrap-anywhere">{{ $title }}</h3>
                @endif
                @if ($period)
                    <span class="type-meta">{{ $period }}</span>
                @endif
            </div>
            @if (isset($actions) || ($toggle && ! $loading && ! $error))
                <div class="flex shrink-0 items-center gap-2">
                    {{ $actions ?? '' }}
                    @if ($toggle && ! $loading && ! $error)
                        <x-ui.segmented size="sm" :label="($title ?? 'Chart').' view'" x-model="view" :options="[
                            'chart' => ['label' => 'Chart', 'icon' => 'chart-line', 'iconOnly' => true],
                            'table' => ['label' => 'Table', 'icon' => 'table', 'iconOnly' => true],
                        ]" data-chart-toggle />
                    @endif
                </div>
            @endif
        </div>
    @endif

    @if ($error)
        <x-ui.alert tone="danger" @class(['mt-2' => $hasHeader])>{{ $error }}</x-ui.alert>
    @elseif ($loading)
        <div role="status" @class(['mt-1' => $hasHeader])>
            <span class="sr-only">Loading {{ $title ?? 'chart' }}</span>
            @if ($hasSummary)
                <x-ui.skeleton.line role="figure" width="w-28" />
            @endif
            <x-ui.skeleton @class([$heightClass, 'w-full', 'mt-3' => $hasSummary]) aria-hidden="true" data-chart-skeleton />
        </div>
    @else
        @if ($hasSummary)
            <div @class(['flex flex-wrap items-baseline gap-x-3 gap-y-1', 'mt-1' => $hasHeader]) data-chart-summary>
                @isset($summary)
                    {{ $summary }}
                @else
                    <p class="type-figure">
                        @if ($money)
                            <x-ui.money :value="$total" :currency="$currency" />
                        @else
                            {{ $fmt($total) }}
                        @endif
                    </p>
                    @if ($previous !== null)
                        <x-ui.delta :current="$total" :previous="$previous" :period="$deltaPeriod" :up-is-good="$upIsGood" />
                    @endif
                @endisset
            </div>
        @endif

        @if ($showLegend)
            <ul class="mt-2 flex flex-wrap gap-x-4 gap-y-1" data-chart-legend>
                @foreach ($normalised as $s)
                    <li class="type-meta flex items-center gap-1.5">
                        @if ($s['dashed'])
                            {{-- The dashed line in the plot, so the key is not colour alone. --}}
                            <svg class="h-2 w-3 shrink-0 forced-color-adjust-none" viewBox="0 0 12 8" aria-hidden="true"><line x1="0" x2="12" y1="4" y2="4" stroke="{{ $swatch($s['color']) }}" stroke-width="2" stroke-dasharray="4 2" /></svg>
                        @else
                            <span class="inline-block size-2 shrink-0 forced-color-adjust-none" style="background-color: {{ $swatch($s['color']) }}" aria-hidden="true"></span>
                        @endif
                        {{ $s['label'] }}
                        <span class="type-numeric text-ink">@if ($money)<x-ui.money :value="$s['total']" :currency="$currency" />@else{{ $fmt($s['total']) }}@endif</span>
                    </li>
                @endforeach
            </ul>
        @endif

        @if ($plotted)
            @php
                $plot = new \TallUiKit\Support\Charts\ChartPlot($type, $normalised->all(), $labels, \TallUiKit\Support\Charts\ChartPlot::heightOf($heightClass), $yFormat, (bool) $money, $currency);
                $plotHeight = $plot->plotHeight();
                $vw = \TallUiKit\Support\Charts\ChartPlot::VIEW_WIDTH;
                $line = $type === 'line';
                // Series colours as custom properties on the bars row, so each bar only names its index.
                $seriesIndex = $normalised->pluck('color')->flip()->all();
                $paint = fn (string $token): string => 'background:var(--s'.($seriesIndex[$token] ?? 0).')';
                $seriesVars = $normalised->values()->map(fn (array $s, int $i): string => '--s'.$i.':var(--color-'.e($s['color']).')')->implode(';');
                /*
                 * Ticks, lines and bars are built as single strings (values escaped with e()), not
                 * Blade loops, so a 90-day chart carries no per-item morph markers and stays small
                 * in a Livewire round trip. Every class is literal here so Tailwind finds it; the
                 * band classes are container queries on the axis row (TickThinning::BANDS).
                 */
                $bandClass = [192 => 'hidden @min-[192px]:block', 224 => 'hidden @min-[224px]:block', 256 => 'hidden @min-[256px]:block', 288 => 'hidden @min-[288px]:block', 320 => 'hidden @min-[320px]:block', 352 => 'hidden @min-[352px]:block', 384 => 'hidden @min-[384px]:block', 416 => 'hidden @min-[416px]:block', 448 => 'hidden @min-[448px]:block', 480 => 'hidden @min-[480px]:block', 512 => 'hidden @min-[512px]:block', 576 => 'hidden @min-[576px]:block', 640 => 'hidden @min-[640px]:block', 704 => 'hidden @min-[704px]:block', 768 => 'hidden @min-[768px]:block', 896 => 'hidden @min-[896px]:block', 1024 => 'hidden @min-[1024px]:block', 1280 => 'hidden @min-[1280px]:block'];
                $yTicksHtml = collect($plot->yTicks())->map(fn (array $t): string => '<span style="transform:translateY(calc('.$t['y'].'px - 50%))">'.e($t['label']).'</span>')->implode('');
                $xTicksHtml = collect($plot->xTicks())->map(fn (array $t): string => '<span'.($t['minWidth'] ? ' class="'.($bandClass[$t['minWidth']] ?? 'hidden').'"' : '').' style="left:clamp('.$t['min'].'px,'.$t['left'].',calc(100% - '.$t['max'].'px))">'.e($t['label']).'</span>')->implode('');
                $gridPath = collect($plot->yTicks())->where('grid', true)->map(fn (array $t): string => 'M0 '.$t['y'].'H'.$vw)->implode('');
                $linesHtml = $line ? collect($plot->paths())->map(fn (array $p): string => '<path d="'.$p['d'].'" fill="none" style="stroke:var(--color-'.e($p['color']).')" stroke-width="1.5" stroke-linejoin="round"'.($p['dashed'] ? ' stroke-dasharray="4 3"' : '').' vector-effect="non-scaling-stroke" data-chart-line />')->implode('') : '';
                // One slot per category, bars at most 24px wide with heights in whole pixels.
                // Stacks grow up from the first series; a single series darkens to chart-1 on hover.
                $barWidth = [1 => '*:*:w-[72%]', 2 => '*:*:w-[34%]', 3 => '*:*:w-[22.6%]'][$normalised->count()] ?? '*:*:w-[72%]';
                $single = $type === 'bar' && $normalised->count() === 1;
                $barsHtml = $line ? '' : collect($plot->columns())->map(fn (array $column): string => $type === 'stacked-bar'
                    ? '<div><div>'.collect($column)->filter(fn (array $seg): bool => $seg['height'] > 0)->map(fn (array $seg): string => '<div style="height:'.$seg['height'].'px;'.$paint($seg['color']).'"></div>')->implode('').'</div></div>'
                    : '<div>'.collect($column)->map(fn (array $bar): string => '<div style="height:'.$bar['height'].'px;'.$paint($bar['color']).'"></div>')->implode('').'</div>'
                )->implode('');
            @endphp
            {{--
                SVG for the gridlines, axis and lines; HTML for bars and every piece of text.
                Heights are fixed, so y is in real pixels (gridlines on half pixels, 1px thick at
                any DPR); only x stretches, through a viewBox with preserveAspectRatio="none" and
                non-scaling strokes. Ticks are page text (caption role) placed in percentages and
                thinned with container queries, so nothing is measured or redrawn on resize.
            --}}
            <div class="{{ $heightClass }} grid grid-cols-[auto_minmax(0,1fr)] grid-rows-[minmax(0,1fr)_20px] forced-color-adjust-none {{ $hasHeader || $hasSummary || $showLegend ? 'mt-3' : '' }}" x-show="view === 'chart'" data-chart-frame @if ($enters) data-motion-enter @endif>
                <div class="grid content-start pr-1.5 *:type-caption *:col-start-1 *:row-start-1 *:justify-self-end *:whitespace-nowrap{!! $money ? ' [html[data-privacy=hidden]_&]:invisible' : '' !!}" aria-hidden="true" data-chart-yticks>{!! $yTicksHtml !!}</div>
                <div
                    x-ref="plot"
                    class="focus-ring relative min-w-0 rounded-xs"
                    role="img"
                    tabindex="0"
                    aria-label="{{ $ariaLabel }}"
                    @if ($money) x-bind:aria-label="$store.privacy.hidden ? @js($maskedLabel) : @js($ariaLabel)" @endif
                    x-on:pointermove="move($event)"
                    x-on:pointerdown="move($event)"
                    x-on:pointerleave="hide()"
                    x-on:keydown="key($event)"
                    x-on:blur="hide()"
                    data-plot='{!! json_encode($plot->payload(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) !!}'
                >
                    <svg class="absolute inset-x-0 top-0 block w-full overflow-visible" height="{{ $plotHeight }}" viewBox="0 0 {{ $vw }} {{ $plotHeight }}" preserveAspectRatio="none" aria-hidden="true" focusable="false" data-chart-svg><path d="{{ $gridPath }}" style="stroke:var(--color-chart-grid)" stroke-width="1" shape-rendering="crispEdges" vector-effect="non-scaling-stroke" data-chart-grid /><path d="M0 {{ $plotHeight - 0.5 }}H{{ $vw }}" style="stroke:var(--color-chart-axis)" stroke-width="1" shape-rendering="crispEdges" vector-effect="non-scaling-stroke" data-chart-axis />{!! $linesHtml !!}</svg>
                    <div
                        @class([
                            'absolute inset-x-0 top-0 flex *:flex *:flex-1 *:items-end *:justify-center *:gap-[4%]',
                            // Stacks: up to 3 segments upwards from the first series, a 1px white gap under
                            // each segment with another above it, only the top one rounded.
                            '*:*:flex *:*:w-[72%] *:*:max-w-6 *:*:flex-col-reverse *:*:*:not-last:border-t *:*:*:border-surface *:*:*:last:rounded-t-xs' => $type === 'stacked-bar',
                            // Single or grouped bars, each at most 24px with 2px top corners; a single series
                            // darkens to chart-1 under the pointer (the script marks the slot data-active).
                            '*:*:max-w-6 *:*:rounded-t-xs '.$barWidth => $type === 'bar',
                            '*:group *:*:group-data-active:bg-chart-1!' => $single,
                        ])
                        style="height: {{ $plot->base() }}px; {{ $seriesVars }}"
                        x-ref="bars"
                        data-chart-bars
                    >{!! $barsHtml !!}</div>
                    {{-- Drawn by the hover script only, so Livewire leaves them alone: the crosshair and dots under the empty message, the tooltip over it. --}}
                    <div wire:ignore class="pointer-events-none absolute inset-0" aria-hidden="true">
                        <div x-ref="crosshair" class="absolute left-0 top-0 hidden w-px bg-chart-axis" style="height: {{ $plotHeight }}px" data-chart-crosshair></div>
                        <div x-ref="dots"></div>
                    </div>
                    {{-- The empty message sits on the plot above the zero line; pointer-events off so hover still works. --}}
                    <p class="type-caption pointer-events-none absolute left-1/2 top-[38%] w-max max-w-[90%] -translate-x-1/2 -translate-y-1/2 bg-surface px-2 text-center" @unless ($isEmpty) hidden @endunless data-chart-empty>{{ $isEmpty ? $empty : '' }}</p>
                    <div wire:ignore x-ref="tooltip" class="pointer-events-none absolute z-10 min-w-24 rounded-md border border-panel-border bg-popover px-2 py-1.5 opacity-0 shadow-popover transition-opacity duration-100" aria-hidden="true" data-chart-tooltip></div>
                </div>
                <div class="@container relative col-start-2 *:type-caption *:absolute *:top-1 *:-translate-x-1/2 *:whitespace-nowrap" aria-hidden="true" data-chart-xticks>{!! $xTicksHtml !!}</div>
            </div>
            <p class="sr-only" aria-live="polite" x-ref="live" data-chart-live></p>
        @else
            {{-- bar-horizontal: rows of label, bar and value. --}}
            @php
                $rows = collect($labels)->map(fn ($l, $i): array => ['label' => (string) $l, 'value' => $normalised->first()['data'][$i] ?? 0]);
                $max = max(1, $rows->max('value'));
                $dense = $rows->count() > 8;
                $token = $normalised->first()['color'] ?? 'chart-2';
            @endphp
            @if ($isEmpty)
                <p class="type-meta {{ $hasHeader || $hasSummary ? 'mt-2' : '' }} py-2" role="img" aria-label="{{ $ariaLabel }}">{{ $empty }}</p>
            @else
                <div
                    role="img"
                    aria-label="{{ $ariaLabel }}"
                    @if ($money) x-bind:aria-label="$store.privacy.hidden ? @js($maskedLabel) : @js($ariaLabel)" @endif
                    @class(['mt-2' => $hasHeader || $hasSummary])
                    x-show="view === 'chart'"
                    data-chart-rows="{{ $dense ? 'dense' : 'ranked' }}"
                >
                    @foreach ($rows as $row)
                        <div @class(['flex items-center gap-3', 'border-b border-line py-1.5' => ! $dense, 'h-6' => $dense])>
                            <span class="type-body w-32 shrink-0 wrap-anywhere" title="{{ $row['label'] }}">{{ $row['label'] }}</span>
                            <span @class(['flex-1 bg-surface-muted', 'h-1' => ! $dense, 'h-2' => $dense]) aria-hidden="true">
                                <span class="block h-full forced-color-adjust-none" style="width: {{ $row['value'] > 0 ? max(2, round($row['value'] / $max * 100)) : 0 }}%; background-color: {{ $swatch($token) }}"></span>
                            </span>
                            <span class="type-numeric w-20 shrink-0 text-right text-ink">@if ($money)<x-ui.money :value="$row['value']" :currency="$currency" />@else{{ $fmt($row['value']) }}@endif</span>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif

        {{--
            Data table: hidden for screen readers, or shown in place of the plot by the toggle.
            Without the toggle it is never drawn, so its rows are bare cells (a 90-day chart
            stays a few KB in a Livewire response, docs/ui-kit.md Performance); under privacy
            mode a money table leaves the accessibility tree, as the plot's name already says
            the total is hidden.
        --}}
        <div
            class="sr-only"
            @if ($toggle) x-bind:class="{ 'sr-only': view !== 'table', 'mt-3 overflow-x-auto rounded-lg border border-line': view === 'table' }" @endif
            @if (! $toggle && $money) x-bind:aria-hidden="$store.privacy.hidden ? 'true' : null" @endif
            data-chart-table
        >
            <table class="w-full border-collapse text-left">
                <caption class="sr-only">{{ ($title ?? $label ?? 'Chart').' data' }}</caption>
                <thead>
                    <tr class="border-b border-line bg-panel-footer">
                        <x-ui.table.th>{{ $type === 'bar-horizontal' ? 'Name' : 'Date' }}</x-ui.table.th>
                        @foreach ($normalised as $s)
                            <x-ui.table.th numeric>{{ $type === 'bar-horizontal' && $normalised->count() === 1 ? 'Value' : $s['label'] }}</x-ui.table.th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @if (! $toggle)
                        {{--
                            One escaped string and no Blade loop, so no per-row morph markers. HTML's optional
                            </td> and </tr> are left out (each <tr> closes the row before it); the last row is
                            closed so Livewire's block marker after it stays a child of the tbody.
                        --}}
                        {!! collect($labels)->map(fn ($l, $i): string => '<tr><td>'.e($isoDay($l)).$normalised->map(fn (array $s): string => '<td>'.e($fmt($s['data'][$i] ?? 0)))->implode(''))->implode('').(count($labels) > 0 ? '</tr>' : '') !!}
                    @else
                    @foreach ($labels as $i => $l)
                        <x-ui.table.row>
                            <x-ui.table.td>{{ $isoDay($l) }}</x-ui.table.td>
                            @foreach ($normalised as $s)
                                <x-ui.table.td numeric>@if ($money)<x-ui.money :value="$s['data'][$i] ?? 0" :currency="$currency" />@else{{ $fmt($s['data'][$i] ?? 0) }}@endif</x-ui.table.td>
                            @endforeach
                        </x-ui.table.row>
                    @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    @endif
</div>
@endif
