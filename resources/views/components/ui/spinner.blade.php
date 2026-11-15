{{--
    Spinner: a 1.5px ring, an `ink-muted` track at 25% and an `ink` arc (15.8:1, so the
    arc clears 3:1 as meaningful non-text), turning once every 0.7s. Under
    prefers-reduced-motion it pulses slowly instead of turning.
    Sizes xs 12, sm 14, default 16, lg 20. Tone `inverse` draws a white arc on a white
    track for dark fills (secondary and destructive buttons).
    On its own it is role="status" with a visually hidden label ("Loading"). Inside
    something that already says it is busy (a button with aria-busy, a region with
    aria-busy, x-ui.loading-inline's text) pass :announce="false" so it is not read twice.
    Props: size, label, tone (default | inverse), announce.
--}}
@props(['size' => 'default', 'label' => 'Loading', 'tone' => 'default', 'announce' => true])

@php
    $px = ['xs' => 12, 'sm' => 14, 'default' => 16, 'lg' => 20][$size] ?? 16;
    $track = $tone === 'inverse' ? 'stroke-white/30' : 'stroke-ink-muted/25';
    $arc = $tone === 'inverse' ? 'stroke-white' : 'stroke-ink';
@endphp

<span
    @if ($announce) role="status" @endif
    {{ $attributes->class(['inline-flex shrink-0 items-center justify-center align-middle']) }}
    data-spinner="{{ $size }}"
>
    <svg width="{{ $px }}" height="{{ $px }}" viewBox="0 0 16 16" fill="none" aria-hidden="true" class="animate-spinner motion-reduce:animate-spinner-reduced" style="width: {{ $px }}px; height: {{ $px }}px">
        <circle cx="8" cy="8" r="6.5" class="{{ $track }}" stroke-width="1.5" />
        <path d="M8 1.5a6.5 6.5 0 0 1 6.5 6.5" class="{{ $arc }}" stroke-width="1.5" stroke-linecap="round" />
    </svg>
    @if ($announce)
        <span class="sr-only">{{ $label }}</span>
    @endif
</span>
