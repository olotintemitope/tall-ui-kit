{{--
    A cell of x-ui.stat-strip: label (label role), value (figure role), optional
    sub line (caption). Pass money through x-ui.money in the slot, or set `money`
    to have it done here (honours privacy mode). A null value shows a muted dash.
    Props: label, sub, value (used when the slot is empty), money, currency.
    Slot: value.
--}}
@props(['label', 'sub' => null, 'value' => null, 'money' => false, 'currency' => 'NGN'])

<div {{ $attributes->class(['min-w-0 border-r border-b border-line px-4 py-3']) }}>
    <dt class="type-label wrap-anywhere">{{ $label }}</dt>
    <dd class="type-figure mt-1">
        @if ($slot->hasActualContent())
            {{ $slot }}
        @elseif ($value === null)
            <span class="text-ink-muted">&ndash;</span>
        @elseif ($money)
            <x-ui.money :value="$value" :currency="$currency" />
        @else
            {{ number_format($value) }}
        @endif
    </dd>
    @if ($sub)
        <dd class="type-caption mt-1">{{ $sub }}</dd>
    @endif
</div>
