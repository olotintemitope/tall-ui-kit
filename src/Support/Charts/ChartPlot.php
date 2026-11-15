<?php

namespace TallUiKit\Support\Charts;

use Illuminate\Support\Carbon;
use TallUiKit\Support\Format;

/**
 * Geometry for x-ui.chart's line, bar and stacked-bar plots, computed on the server so
 * the plot is plain SVG and HTML that the browser draws at device resolution.
 *
 * The plot is a fixed height (the chart's height minus the 20px x-axis row), so every
 * y position is in real CSS pixels: gridlines sit on half pixels and draw exactly one
 * pixel thick. Only x stretches with the width, so line paths use a 1000-unit wide
 * viewBox with `preserveAspectRatio="none"` and `vector-effect="non-scaling-stroke"`,
 * bars are boxes sized in percentages with a 24px cap, and ticks are HTML text placed
 * in percentages. Nothing is measured or redrawn on resize.
 */
final class ChartPlot
{
    /** Height of the x-axis label row under the plot. */
    public const AXIS_ROW = 20;

    /** Room above the top gridline for half a tick label. */
    public const TOP = 8;

    /** Width of the path viewBox; x positions are fractions of it. */
    public const VIEW_WIDTH = 1000;

    /** @var array{min: float, max: float, step: float, ticks: list<float>}|null */
    private ?array $scale = null;

    /**
     * @param  'line'|'bar'|'stacked-bar'  $type
     * @param  list<array{label: string, data: list<int|float>, color: string, dashed: bool}>  $series
     * @param  list<string|int>  $labels
     */
    public function __construct(
        public readonly string $type,
        public readonly array $series,
        public readonly array $labels,
        public readonly int $height = 160,
        public readonly string $yFormat = 'number',
        public readonly bool $money = false,
        public readonly string $currency = 'NGN',
    ) {}

    /**
     * Pixel height of a Tailwind height class: h-40 is 160px, h-[200px] is 200px.
     */
    public static function heightOf(string $class): int
    {
        return match (true) {
            (bool) preg_match('/^h-(\d+(?:\.\d+)?)$/', $class, $m) => (int) round((float) $m[1] * 4),
            (bool) preg_match('/^h-\[(\d+)px\]$/', $class, $m) => (int) $m[1],
            default => 160,
        };
    }

    public function plotHeight(): int
    {
        return max(40, $this->height - self::AXIS_ROW);
    }

    /** The zero line for bars: the top of the 1px axis line. */
    public function base(): int
    {
        return $this->plotHeight() - 1;
    }

    public function count(): int
    {
        return count($this->labels);
    }

    public function stacked(): bool
    {
        return $this->type === 'stacked-bar';
    }

    /**
     * @return array{min: float, max: float, step: float, ticks: list<float>}
     */
    public function scale(): array
    {
        if ($this->scale !== null) {
            return $this->scale;
        }

        $values = [];
        if ($this->stacked()) {
            for ($i = 0, $n = $this->count(); $i < $n; $i++) {
                $values[] = $this->stackTotal($i);
            }
        } else {
            foreach ($this->series as $s) {
                array_push($values, ...$s['data']);
            }
        }

        $percent = $this->yFormat === 'percent';

        return $this->scale = NiceScale::for($values, 4, $percent ? 0.01 : 1.0, $percent ? 2 : 0);
    }

    public function stackTotal(int $index): float
    {
        return (float) array_sum(array_map(fn (array $s): float => max(0, (float) ($s['data'][$index] ?? 0)), $this->series));
    }

    /**
     * Y in CSS pixels from the top of the plot.
     */
    public function y(float $value): float
    {
        ['min' => $min, 'max' => $max] = $this->scale();
        $span = $max - $min ?: 1;

        return self::TOP + ($max - $value) / $span * ($this->base() - self::TOP);
    }

    /**
     * Pixel length of a bar from zero.
     */
    public function length(float $value): int
    {
        if ($value <= 0) {
            return 0;
        }

        return max(1, (int) round($this->base() - $this->y($value)));
    }

    /**
     * Horizontal gridlines on half pixels (crisp 1px lines), with their tick labels.
     * The lowest tick is the axis line, drawn separately.
     *
     * @return list<array{y: float, value: float, label: string, grid: bool}>
     */
    public function yTicks(): array
    {
        $scale = $this->scale();

        return array_map(fn (float $t): array => [
            'y' => $t === $scale['min'] ? $this->plotHeight() - 0.5 : floor($this->y($t)) + 0.5,
            'value' => $t,
            'label' => $this->tickLabel($t),
            'grid' => $t !== $scale['min'],
        ], array_reverse($scale['ticks']));
    }

    /** The x position of a point or a bar's centre, as a fraction of the plot width. */
    public function x(int $index): float
    {
        $n = $this->count();

        if ($this->type === 'line') {
            return $n > 1 ? $index / ($n - 1) : 0.5;
        }

        return ($index + 0.5) / max(1, $n);
    }

    /**
     * One path per line series, in viewBox units (x 0 to 1000, y in pixels).
     *
     * @return list<array{d: string, color: string, dashed: bool}>
     */
    public function paths(): array
    {
        return array_map(function (array $s): array {
            $d = '';
            foreach (array_values($s['data']) as $i => $v) {
                $d .= ($i === 0 ? 'M' : 'L').self::num($this->x($i) * self::VIEW_WIDTH, 1).' '.self::num($this->y((float) $v), 1);
            }

            return ['d' => $d, 'color' => $s['color'], 'dashed' => $s['dashed']];
        }, $this->series);
    }

    /**
     * Bars per category: grouped bars side by side, or one stack whose segment heights
     * add up to the stack's total (rounded once, at each running total, so the stack is
     * never a pixel off). Only the top non-zero segment is rounded; each segment with
     * another above it carries the 1px white gap.
     *
     * @return list<list<array{height: int, color: string, rounded: bool, gap: bool}>>
     */
    public function columns(): array
    {
        $columns = [];

        for ($i = 0, $n = $this->count(); $i < $n; $i++) {
            if (! $this->stacked()) {
                $columns[] = array_map(fn (array $s): array => [
                    'height' => $this->length((float) ($s['data'][$i] ?? 0)),
                    'color' => $s['color'],
                    'rounded' => true,
                    'gap' => false,
                ], $this->series);

                continue;
            }

            $running = 0.0;
            $previousTop = 0;
            $segments = [];
            foreach ($this->series as $s) {
                $running += max(0, (float) ($s['data'][$i] ?? 0));
                $top = $this->length($running);
                $segments[] = ['height' => $top - $previousTop, 'color' => $s['color'], 'rounded' => false, 'gap' => false];
                $previousTop = $top;
            }

            $visible = array_keys(array_filter($segments, fn (array $seg): bool => $seg['height'] > 0));
            if ($visible !== []) {
                $segments[end($visible)]['rounded'] = true;
                foreach (array_slice($visible, 0, -1) as $k) {
                    $segments[$k]['gap'] = true;
                }
            }

            $columns[] = $segments;
        }

        return $columns;
    }

    /**
     * X-axis labels: position, text and the width from which each one shows.
     *
     * @return list<array{index: int, left: string, min: int, max: int, label: string, minWidth: int|null}>
     */
    public function xTicks(): array
    {
        $texts = array_map(self::shortLabel(...), $this->labels);
        $minWidths = TickThinning::minWidths($texts, $this->type !== 'line');

        $ticks = [];
        foreach ($texts as $i => $text) {
            if ($minWidths[$i] === null) {
                continue;
            }

            $ticks[] = [
                'index' => $i,
                'left' => self::num($this->x($i) * 100).'%',
                // Clamped inside the plot; the first may hang into the y-axis column.
                'min' => max(0, (int) ceil(TickThinning::width($text) / 2) - TickThinning::HANG),
                'max' => (int) ceil(TickThinning::width($text) / 2),
                'label' => $text,
                'minWidth' => $minWidths[$i],
            ];
        }

        return $ticks;
    }

    /**
     * What the hover and keyboard script needs: each index's label and formatted
     * values, and each line's y in pixels for the hover dot. The script derives each
     * index's x from the type and count, as x() does.
     *
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        $n = $this->count();

        return [
            'type' => $this->type,
            'money' => $this->money,
            'labels' => array_map(self::shortLabel(...), $this->labels),
            'series' => array_map(fn (array $s): array => [
                'name' => $s['label'],
                'color' => $s['color'],
                'values' => array_map(fn (int $i): string => $this->valueLabel((float) ($s['data'][$i] ?? 0)), $n ? range(0, $n - 1) : []),
                'y' => $this->type === 'line' ? array_map(fn (int $i): int|float => self::compact($this->y((float) ($s['data'][$i] ?? 0))), $n ? range(0, $n - 1) : []) : [],
            ], $this->series),
        ];
    }

    /** ISO days read as "14 Nov"; any other label is shown as given. */
    public static function shortLabel(string|int|float|null $label): string
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $label) ? Carbon::parse((string) $label)->format('j M') : (string) $label;
    }

    /** A value in the tooltip: money as x-ui.money prints it, else the y format. */
    public function valueLabel(float $value): string
    {
        return match (true) {
            $this->money => Format::money($value + 0, $this->currency),
            $this->yFormat === 'percent' => Format::percent($value),
            $this->yFormat === 'compact' => Format::compact($value),
            default => Format::number($value, floor($value) == $value ? 0 : 2),
        };
    }

    /** A y-axis tick: compact from 10,000 (or always for compact charts). */
    public function tickLabel(float $value): string
    {
        if ($this->yFormat === 'percent') {
            return Format::percent($value);
        }

        $text = $this->yFormat === 'compact' || abs($value) >= 10000 ? Format::compact($value) : Format::number($value);

        return $this->money ? ($this->currency === 'USD' ? '$' : '₦').$text : $text;
    }

    /** One decimal, and a whole number without one, to keep the payload short. */
    private static function compact(float $value): int|float
    {
        $rounded = round($value, 1);

        return $rounded == (int) $rounded ? (int) $rounded : $rounded;
    }

    private static function num(float $value, int $decimals = 2): string
    {
        return rtrim(rtrim(number_format(round($value, $decimals), $decimals, '.', ''), '0'), '.') ?: '0';
    }
}
