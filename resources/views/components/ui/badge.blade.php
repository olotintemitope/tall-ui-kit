{{--
    Status pill: soft fill, darker text, Inter 12/16 500, sentence case. The only
    rounded-full element in the kit. No dot, no icon, no border.
    Props: variant (neutral|success|warning|danger|info|accent; `outline` is a
    non-status tag with a hairline border), size (default | sm 11/14 for dense rows).
    AA ratios (text on fill): success 7.29, warning 6.84, danger 5.91, info 6.16,
    neutral 13.63, accent 4.67, outline 5.85.
    lean: the same look from one class (.ui-badge in app.css, tone and size as data
    attributes), about 60 bytes instead of 180, for table cells and other dense rows.
    One line, always: the pill never breaks onto a second line and never shrinks;
    it takes the width its label needs, so nothing is cut either.
--}}
@props(['variant' => 'neutral', 'size' => 'default', 'lean' => false])

@if ($lean)
<span class="ui-badge" @if ($variant !== 'neutral') data-tone="{{ $variant }}" @endif @if ($size === 'sm') data-size="sm" @endif {{ $attributes }}>{{ $slot }}</span>@php return; @endphp
@endif

@php
    $variants = [
        'neutral' => 'bg-surface-muted text-ink',
        'success' => 'bg-success-tint text-success-strong',
        'warning' => 'bg-warning-tint text-warning-text',
        'danger' => 'bg-danger-tint text-danger-text',
        'info' => 'bg-info-tint text-info-text',
        'accent' => 'bg-primary/10 text-primary-text',
        'outline' => 'bg-surface text-ink-muted ring-1 ring-inset ring-line-strong',
    ];
@endphp

<span {{ $attributes->class([
    'inline-flex shrink-0 items-center whitespace-nowrap rounded-full px-2 py-0.5 font-body font-medium',
    $size === 'sm' ? 'text-[11px] leading-[14px]' : 'text-[12px] leading-4',
    $variants[$variant] ?? $variants['neutral'],
]) }}>{{ $slot }}</span>
