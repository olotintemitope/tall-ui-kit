{{--
    Selectable card with a price column: a real radio (one of a set) or checkbox (many)
    in a card whose whole area is the target, the title, meta lines and badges in the
    middle and the price on the right. Same states as radio-group variant="cards":
    1px `control` border (3.65:1), checked 1px `primary-text` border (5.21:1) with a
    primary/5 tint, the checked control filled ink; 8px radius. Disabled cards get a
    dashed `control` border (3.38:1) on a `surface-subtle` fill, explain why in `reason` (linked to the input) and cannot be chosen.
    Generalised from x-marketplace.target-option, which can migrate to it.

    Props: type (radio | checkbox), name, value, title, meta (string or list of lines),
    price (null hides the column; plain text, catalogue prices are not private), currency,
    unit ("per video"), priceNote (a caption under the price), badges (list of
    [label, variant]), checked, disabled, reason. wire:model / x-model go on the input.
    Slot: default (replaces the price column).
--}}
@props(['type' => 'radio', 'name' => null, 'value', 'title', 'meta' => [], 'price' => null, 'currency' => 'NGN', 'unit' => null, 'priceNote' => null, 'badges' => [], 'checked' => false, 'disabled' => false, 'reason' => null])

@php
    $binding = $attributes->filter(fn ($v, $key) => str_starts_with($key, 'wire:model') || str_starts_with($key, 'x-model'));
    $id = $attributes->get('id') ?? 'select-'.\Illuminate\Support\Str::slug(($name ?? 'option').'-'.$value);
    $lines = is_array($meta) ? $meta : array_filter([$meta]);
    $metaId = $lines ? $id.'-meta' : null;
    $reasonId = $reason ? $id.'-reason' : null;
    $isRadio = $type === 'radio';
@endphp

<label for="{{ $id }}" {{ $attributes->except(array_merge(array_keys($binding->getAttributes()), ['id']))->class([
    'group flex items-start gap-3 rounded-lg border bg-white p-3 transition-colors duration-100',
    'has-checked:border-primary-text has-checked:bg-primary/5 has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-focus',
    'cursor-pointer border-control hover:border-control-hover' => ! $disabled,
    'cursor-not-allowed border-dashed border-control bg-surface-subtle' => $disabled,
]) }} data-card-selectable>
    <span class="relative flex h-5 w-4 shrink-0 items-center justify-center">
        <input
            type="{{ $isRadio ? 'radio' : 'checkbox' }}"
            id="{{ $id }}"
            @if ($name) name="{{ $name }}" @endif
            value="{{ $value }}"
            data-ui="{{ $isRadio ? 'radio' : 'checkbox' }}"
            @checked($checked)
            @disabled($disabled)
            @if ($metaId || $reasonId) aria-describedby="{{ trim($metaId.' '.$reasonId) }}" @endif
            {{ $binding }}
            @class([
                'peer size-4 cursor-pointer appearance-none forced-colors:appearance-auto border border-control bg-white transition-colors duration-100 group-hover:border-control-hover checked:border-secondary checked:bg-secondary focus-visible:outline-none disabled:cursor-not-allowed disabled:border-dashed disabled:bg-surface-subtle disabled:checked:border-solid disabled:checked:border-ink-muted disabled:checked:bg-ink-muted',
                'rounded-full' => $isRadio,
                'rounded-sm' => ! $isRadio,
            ])
        />
        @if ($isRadio)
            <span class="pointer-events-none absolute size-1.5 rounded-full bg-white opacity-0 peer-checked:opacity-100 forced-colors:hidden" aria-hidden="true"></span>
        @else
            <x-ui.icon name="check" :stroke-width="3" class="pointer-events-none absolute size-3 text-white opacity-0 peer-checked:opacity-100 forced-colors:hidden" />
        @endif
    </span>
    <span class="flex min-w-0 flex-1 flex-col">
        <span class="flex flex-wrap items-center gap-2">
            <span class="type-body-strong">{{ $title }}</span>
            @foreach ($badges as [$badgeLabel, $badgeVariant])
                <x-ui.badge :variant="$badgeVariant" size="sm">{{ $badgeLabel }}</x-ui.badge>
            @endforeach
        </span>
        @if ($lines)
            <span id="{{ $metaId }}" class="flex flex-col">
                @foreach ($lines as $line)
                    <span class="type-meta">{{ $line }}</span>
                @endforeach
            </span>
        @endif
        @if ($reason)
            <span id="{{ $reasonId }}" class="type-caption mt-1">{{ $reason }}</span>
        @endif
    </span>
    @if ($slot->hasActualContent() || $price !== null)
        <span class="flex shrink-0 flex-col items-end text-right">
            @if ($slot->hasActualContent())
                {{ $slot }}
            @else
                <span class="type-numeric text-ink">{{ \TallUiKit\Support\Format::money($price, $currency) }}</span>
                @if ($unit)
                    <span class="type-caption">{{ $unit }}</span>
                @endif
                @if ($priceNote)
                    <span class="type-caption">{{ $priceNote }}</span>
                @endif
            @endif
        </span>
    @endif
</label>
