{{--
    Inline page-level notice, flat (no shadow, no icon): a 1px border all round in a
    light tone of the status colour, a very light tinted fill and the message in an
    AA tone (danger red-800 on red-50, warning amber-900 on amber-50, success
    emerald-800 on emerald-50, neutral ink on panel-footer). No coloured edge; the
    same convention as toasts.
    Props: tone (danger | warning | success | neutral). Slot: message, may include
    an x-ui.button variant="link" action. Danger is role="alert", the rest "status".
--}}
@props(['tone' => 'neutral'])

@php
    $tones = [
        'danger' => 'border-red-200 bg-red-50 text-red-800',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-900',
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'neutral' => 'border-line bg-panel-footer text-ink',
    ];
@endphp

<div role="{{ $tone === 'danger' ? 'alert' : 'status' }}" {{ $attributes->class(['type-body flex flex-wrap items-baseline gap-x-2 gap-y-1 rounded-md border px-3 py-2.5', $tones[$tone] ?? $tones['neutral']]) }} data-alert="{{ $tone }}">
    {{ $slot }}
</div>
