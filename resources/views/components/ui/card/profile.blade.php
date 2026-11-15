{{--
    Profile card: a neutral initials avatar, name (body-strong), role or meta line, an
    optional "Verified" badge, an inline strip of 2 or 3 small stats (label in meta,
    value in numeric) and actions.
    Props: name, meta, verified, stats (label => value, or a list of {label, value}).
    Slot: actions.
--}}
@props(['name', 'meta' => null, 'verified' => false, 'stats' => []])

@php
    $pairs = collect($stats)->map(fn ($value, $key): array => is_array($value) ? $value : ['label' => $key, 'value' => $value])->values()->take(3);
@endphp

<x-ui.card {{ $attributes->class(['p-4']) }} data-card-profile>
    <div class="flex items-start gap-3">
        <x-ui.avatar :name="$name" size="lg" />
        <div class="min-w-0 flex-1">
            <p class="flex flex-wrap items-center gap-x-2 gap-y-1">
                <span class="type-body-strong wrap-anywhere">{{ $name }}</span>
                @if ($verified)
                    <x-ui.badge variant="info" size="sm">Verified</x-ui.badge>
                @endif
            </p>
            @if ($meta)
                <p class="type-meta wrap-anywhere">{{ $meta }}</p>
            @endif
        </div>
    </div>

    @if ($pairs->isNotEmpty())
        <dl class="mt-3 flex flex-wrap gap-x-6 gap-y-2 border-t border-line pt-3">
            @foreach ($pairs as $pair)
                <div>
                    <dt class="type-meta">{{ $pair['label'] }}</dt>
                    <dd class="type-numeric text-ink">{{ $pair['value'] }}</dd>
                </div>
            @endforeach
        </dl>
    @endif

    @isset($actions)
        <div class="mt-3 flex flex-wrap gap-2">{{ $actions }}</div>
    @endisset
</x-ui.card>
