{{--
    A list of steps and where the user is in them (docs/components/workflow.md, "Stepper").
    Generic: steps are data. An <ol> inside a <nav> named by `label`; the current step has
    aria-current="step", and every step starts with a hidden state word ("Completed",
    "Current", "Upcoming", "Error"), so state never rests on colour or the marker alone.

    Variants:
      default  a 24px marker per step (number; a check when complete; an x on error) with
               the label (body) and an optional description (meta). Markers: complete is a
               1px ink ring with an ink check, current an ink disc with a white number,
               upcoming a 1px `control` ring with an ink-muted number, error a 1px red-700
               ring with a red-700 x and the label in red-700. A 1px `line` rule joins
               the steps. No fills other than the current disc, no glow, no progress bar.
      inline   the text list of x-ui.form-layout.wizard: "1 Song · 2 Targets Done · 3 ...",
               collapsing to "Step 2 of 5" below sm (the wizard renders its header with it).

    Orientation (default variant): horizontal lays the steps in a row once its own box is
    at least 42rem wide (a container query, so it follows the panel it sits in, not the
    window) and stacks them vertically below that; vertical always stacks.

    Clicking: `clickable` (true: complete and error steps; "all": every step but the
    current one). A step then renders as a link (its `href`), a Livewire call (`action`,
    called with the step's 1-based number, or the step's own `action`), or a button that
    dispatches a bubbling `ui-step` event, detail { step, key }. Steps that cannot be
    clicked stay text. Focus rings on every clickable step.

    Props: steps (list of strings or [label, description, state (complete | current |
    upcoming | error), href, action, key]), current (1-based; fills in states that are
    not given), variant (default | inline), orientation (horizontal | vertical),
    clickable (false | true | "all"), action (Livewire method), label ("Progress": the
    nav's name), id. Other attributes go on the <nav>.
--}}
@props([
    'steps' => [],
    'current' => null,
    'variant' => 'default',
    'orientation' => 'horizontal',
    'clickable' => false,
    'action' => null,
    'label' => 'Progress',
    'id' => null,
])

@php
    $states = ['complete', 'current', 'upcoming', 'error'];
    $words = ['complete' => 'Completed', 'current' => 'Current', 'upcoming' => 'Upcoming', 'error' => 'Error'];
    $current = $current === null ? null : (int) $current;

    $list = collect($steps)->values()->map(function ($step, int $i) use ($current, $states): array {
        $step = is_array($step) ? $step : ['label' => (string) $step];
        $n = $i + 1;
        $state = $step['state'] ?? null;
        if (! in_array($state, $states, true)) {
            $state = match (true) {
                $current === null => $n === 1 ? 'current' : 'upcoming',
                $n < $current => 'complete',
                $n === $current => 'current',
                default => 'upcoming',
            };
        }

        return [
            'n' => $n,
            'key' => (string) ($step['key'] ?? $n),
            'label' => (string) ($step['label'] ?? ''),
            'description' => $step['description'] ?? null,
            'state' => $state,
            'href' => $step['href'] ?? null,
            'action' => $step['action'] ?? null,
        ];
    });

    $total = $list->count();
    $at = $list->firstWhere('state', 'current')['n'] ?? $current ?? 1;
    $canClick = fn (array $s): bool => $s['state'] !== 'current' && ($clickable === 'all' || ($clickable && in_array($s['state'], ['complete', 'error'], true)));
    $needsEvent = $list->contains(fn ($s) => $canClick($s) && ! $s['href'] && ! $s['action'] && ! $action);
    $id ??= 'stepper-'.substr(md5($list->pluck('label')->implode('|')), 0, 6);
    $vertical = $orientation === 'vertical';
@endphp

<nav
    aria-label="{{ $label }}"
    {{ $attributes->class(['@container min-w-0']) }}
    id="{{ $id }}"
    data-stepper="{{ $variant === 'inline' ? 'inline' : ($vertical ? 'vertical' : 'horizontal') }}"
    @if ($needsEvent) x-data @endif
>
@if ($variant === 'inline')
    <p class="type-meta sm:hidden" data-stepper-compact>Step {{ $at }} of {{ $total }}</p>
    <ol class="hidden flex-wrap items-center gap-x-2 gap-y-1 sm:flex" data-stepper-list>
        @foreach ($list as $s)
            <li class="flex items-center gap-2" data-step-state="{{ $s['state'] }}" @if ($s['state'] === 'current') aria-current="step" @endif>
                @if ($s['n'] > 1)
                    <span class="text-ink-muted" aria-hidden="true">·</span>
                @endif
                @php
                    $inner = '<span class="type-numeric">'.$s['n'].'</span> '.e($s['label']).($s['state'] === 'complete' ? ' <span class="text-emerald-700">Done</span>' : '').($s['state'] === 'error' ? ' <span class="text-red-700">Error</span>' : '');
                @endphp
                @if ($canClick($s) && $s['href'])
                    <a href="{{ $s['href'] }}" class="rounded-sm type-meta text-ink-body hover:text-ink focus-ring">{!! $inner !!}</a>
                @elseif ($canClick($s) && ($s['action'] || $action))
                    <button type="button" wire:click="{{ $s['action'] ?? $action.'('.$s['n'].')' }}" class="rounded-sm type-meta text-ink-body hover:text-ink focus-ring">{!! $inner !!}</button>
                @elseif ($canClick($s))
                    <button type="button" x-on:click="$dispatch('ui-step', { step: {{ $s['n'] }}, key: @js($s['key']) })" class="rounded-sm type-meta text-ink-body hover:text-ink focus-ring">{!! $inner !!}</button>
                @else
                    <span @class(['type-meta', 'font-medium text-ink!' => $s['state'] === 'current', 'text-ink-body' => $s['state'] === 'complete'])>{!! $inner !!}</span>
                @endif
            </li>
        @endforeach
    </ol>
@else
    <ol @class(['flex flex-col', '@2xl:flex-row @2xl:items-start @2xl:gap-4' => ! $vertical]) data-stepper-list>
        @foreach ($list as $s)
            @php
                $last = $s['n'] === $total;
                $tag = $canClick($s) ? ($s['href'] ? 'a' : 'button') : 'div';
                $marker = [
                    'complete' => 'border border-secondary bg-white text-ink',
                    'current' => 'bg-secondary text-white',
                    'upcoming' => 'border border-control bg-white text-ink-muted',
                    'error' => 'border border-red-700 bg-white text-red-700',
                ][$s['state']];
            @endphp
            <li @class(['relative flex min-w-0 gap-3', 'pb-5' => ! $last, '@2xl:flex-1 @2xl:items-start @2xl:gap-2 @2xl:pb-0' => ! $vertical]) data-step-state="{{ $s['state'] }}" @if ($s['state'] === 'current') aria-current="step" @endif>
                @unless ($last)
                    {{-- Vertical rule under the marker (stacked) or after the text (in a row). --}}
                    <span @class(['absolute top-7 bottom-1 left-3 w-px -translate-x-1/2 bg-line', '@2xl:hidden' => ! $vertical]) aria-hidden="true"></span>
                @endunless
                <{{ $tag }}
                    @if ($tag === 'a') href="{{ $s['href'] }}" @endif
                    @if ($tag === 'button')
                        type="button"
                        @if ($s['action'] || $action) wire:click="{{ $s['action'] ?? $action.'('.$s['n'].')' }}"
                        @else x-on:click="$dispatch('ui-step', { step: {{ $s['n'] }}, key: @js($s['key']) })" @endif
                    @endif
                    @class([
                        'group flex min-w-0 items-start gap-3 text-left',
                        '@2xl:gap-2' => ! $vertical,
                        'rounded-md focus-ring -m-1 p-1' => $tag !== 'div',
                    ])
                    data-step-target
                >
                    <span @class(['flex size-6 shrink-0 items-center justify-center rounded-full type-numeric font-medium', $marker]) aria-hidden="true">
                        @if ($s['state'] === 'complete')
                            <x-ui.icon name="check" :stroke-width="2.5" class="size-3.5" />
                        @elseif ($s['state'] === 'error')
                            <x-ui.icon name="x" :stroke-width="2.5" class="size-3.5" />
                        @else
                            {{ $s['n'] }}
                        @endif
                    </span>
                    <span class="flex min-w-0 flex-col pt-0.5">
                        <span @class([
                            'wrap-anywhere',
                            'type-body-strong text-ink' => $s['state'] === 'current',
                            'type-body text-ink-body' => $s['state'] === 'complete',
                            'type-body text-ink-muted' => $s['state'] === 'upcoming',
                            'type-body-strong text-red-700' => $s['state'] === 'error',
                            'group-hover:underline underline-offset-3' => $tag !== 'div',
                        ])><span class="sr-only">{{ $words[$s['state']] }}: </span>{{ $s['label'] }}</span>
                        @if ($s['description'])
                            <span @class(['type-meta wrap-anywhere', 'text-red-700!' => $s['state'] === 'error'])>{{ $s['description'] }}</span>
                        @endif
                    </span>
                </{{ $tag }}>
                @unless ($last || $vertical)
                    <span class="mt-3 hidden h-px min-w-4 flex-1 bg-line @2xl:block" aria-hidden="true"></span>
                @endunless
            </li>
        @endforeach
    </ol>
@endif
</nav>
