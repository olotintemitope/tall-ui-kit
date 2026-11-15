{{--
    Stepped progress (docs/components/workflow.md, "Progress steps"): "Step 3 of 5" as N
    equal segments with 2px gaps. Compact and text-light: use it where the steps need no
    names (a short form flow, onboarding cards, a quiz). When the user must see and pick
    the steps by name, use x-ui.stepper.

    role="progressbar" (aria-valuemin 0, aria-valuemax N, aria-valuenow the steps done or
    current, aria-valuetext "Step 3 of 5"), named by `label`. Segments up to `current` are
    filled in the tone's colour (3:1 or more on the track and white), the rest
    `surface-muted`. `showText` prints "Step 3 of 5" above (meta). Optional `labels` sit
    under their segments (caption; the current one in ink) and wrap inside their column
    on narrow screens; they are an <ol> outside the progressbar with aria-current="step".

    Props: label ("Progress"), total, current (1-based; 0 for none yet), labels (list,
    same length as total), size (sm 4px | md 8px), tone, showText (true).
--}}
@props(['label' => 'Progress', 'total' => 1, 'current' => 0, 'labels' => [], 'size' => 'sm', 'tone' => 'default', 'showText' => true])

@php
    $total = max(1, (int) $total);
    $current = max(0, min($total, (int) $current));
    // Before the first step the value is 0, so the text says so too.
    $text = $current === 0 ? 'Not started, '.$total.' steps' : 'Step '.$current.' of '.$total;
    $tone = in_array($tone, ['default', 'success', 'warning', 'danger'], true) ? $tone : 'default';
    $labels = array_values($labels);
@endphp

<div {{ $attributes->class(['flex min-w-0 flex-col gap-1.5']) }} data-progress-steps>
    @if ($showText)
        <p class="type-meta" aria-hidden="true">{{ $text }}</p>
    @endif
    <div role="progressbar" aria-label="{{ $label }}" aria-valuemin="0" aria-valuemax="{{ $total }}" aria-valuenow="{{ $current }}" aria-valuetext="{{ $text }}" class="ui-steps flex gap-0.5" data-tone="{{ $tone }}">
        @for ($i = 1; $i <= $total; $i++)
            <span @class(['ui-steps-segment min-w-0 flex-1 rounded-sm', $size === 'md' ? 'h-2' : 'h-1']) @if ($i <= $current) data-on @endif></span>
        @endfor
    </div>
    @if ($labels)
        <ol class="grid gap-0.5" style="grid-template-columns: repeat({{ $total }}, minmax(0, 1fr))">
            @foreach ($labels as $i => $text)
                <li @class(['type-caption min-w-0 wrap-anywhere', 'text-ink! font-medium' => $i + 1 === $current]) @if ($i + 1 === $current) aria-current="step" @endif>{{ $text }}</li>
            @endforeach
        </ol>
    @endif
</div>
