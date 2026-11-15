{{--
    Segmented control for switching a view or period (role="radiogroup"). Three forms,
    one look: text segments, icon with label, and icon only.

    Look: a `surface-subtle` track (#F6F6F6) with a 1px `line` border, 6px radius and 2px
    padding; the selected segment is white with a 1px `control` border (3.38:1 on the
    track, 3.65:1 on white), 4px radius, ink text and icon; unselected segments have no
    fill or border and are `ink-muted` (5.42:1 on the track); hover turns the text ink,
    never a box. The selected segment carries the 3:1 boundary; the track's `line` edge
    is decorative (a `control` track border was measured too and read as a box in a box).
    Disabled: `ink-muted` (5.42:1, no opacity) with a dashed `control` underline (a dashed
    `control` border on icon-only segments),
    aria-disabled, no hover, cursor-not-allowed. Labels 13px/500, centred with icons.
    Heights match buttons and inputs: default 36px overall (30px segments), sm 32px
    (26px); icon-only segments are square. Never wraps: `responsive` swaps in an
    x-ui.select with the same binding below `sm`. `data-segmented-indicator` marks the
    selected segment as the hook for a later sliding indicator (not animated).

    Props: options (value => label for text; or value => ['label' => required, also the
    accessible name, 'icon' => Lucide name, 'iconOnly' => bool, 'disabled' => bool,
    'force' => hover|focus: draws that state statically for the /admin/ui states matrix]),
    value (initial value without a binding), size (default | sm), label (accessible name
    of the group), responsive. Bind with wire:model / x-model (x-modelable).
    Either every segment has an icon or none does; mixing throws in local and testing.
    Arrow keys move and select (disabled segments are skipped), Space and Enter select,
    only the selected segment is in the tab order.
--}}
@props(['options' => [], 'value' => null, 'size' => 'default', 'label' => null, 'responsive' => false])

@php
    $normalised = collect($options)->map(function ($option, $key): array {
        $option = is_array($option) ? $option : ['label' => (string) $option];

        return [
            'value' => (string) $key,
            'label' => (string) ($option['label'] ?? $key),
            'icon' => $option['icon'] ?? null,
            'iconOnly' => (bool) ($option['iconOnly'] ?? false) && filled($option['icon'] ?? null),
            'disabled' => (bool) ($option['disabled'] ?? false),
            'force' => in_array($option['force'] ?? null, ['hover', 'focus'], true) ? $option['force'] : null,
        ];
    })->values();

    $withIcons = $normalised->filter(fn (array $option): bool => filled($option['icon']))->count();
    if ($withIcons > 0 && $withIcons < $normalised->count() && app()->environment('local', 'testing')) {
        throw new InvalidArgumentException('x-ui.segmented: give every option an icon or none. Mixed options: '.$normalised->pluck('value')->implode(', '));
    }

    $enabled = $normalised->reject(fn (array $option): bool => $option['disabled']);
    $initial = (string) ($value ?? $enabled->first()['value'] ?? $normalised->first()['value'] ?? '');
    $small = $size === 'sm';
    $binding = $attributes->filter(fn ($v, $key) => str_starts_with($key, 'wire:model') || str_starts_with($key, 'x-model'));
    $selectOptions = $normalised->map(fn (array $o): array => ['value' => $o['value'], 'label' => $o['label'], 'disabled' => $o['disabled']])->all();
    $selectSize = $small ? 'sm' : 'default';
@endphp

@if ($responsive)
    <x-ui.select
        :size="$selectSize"
        :aria-label="$label"
        :options="$selectOptions"
        :selected="$initial"
        class="sm:hidden"
        :attributes="$binding"
        data-segmented-select
    />
@endif

<div
    x-data="{
        value: @js($initial),
        move(step) {
            const items = [...$el.querySelectorAll('[role=radio]:not(:disabled)')];
            if (! items.length) return;
            const index = items.findIndex((item) => item.dataset.value === String(this.value));
            const next = items[(index + step + items.length) % items.length];
            this.value = next.dataset.value;
            next.focus();
        },
    }"
    x-modelable="value"
    role="radiogroup"
    @if ($label) aria-label="{{ $label }}" @endif
    x-on:keydown.right.prevent="move(1)"
    x-on:keydown.down.prevent="move(1)"
    x-on:keydown.left.prevent="move(-1)"
    x-on:keydown.up.prevent="move(-1)"
    data-segmented="{{ $withIcons ? 'icon' : 'text' }}"
    {{ $attributes->class(['inline-flex shrink-0 items-center gap-0.5 rounded-md border border-line bg-surface-subtle p-0.5', $small ? 'h-8' : 'h-9', 'max-sm:hidden' => $responsive]) }}
>
    @foreach ($normalised as $option)
        @if ($option['iconOnly'])
            <x-ui.tooltip :text="$option['label']" position="bottom">
                <button
                    type="button"
                    role="radio"
                    data-value="{{ $option['value'] }}"
                    aria-label="{{ $option['label'] }}"
                    x-bind:aria-checked="String(value) === @js($option['value']) ? 'true' : 'false'"
                    x-bind:tabindex="String(value) === @js($option['value']) ? 0 : -1"
                    x-bind:data-segmented-indicator="String(value) === @js($option['value']) ? '' : null"
                    x-on:click="value = @js($option['value'])"
                    aria-checked="{{ $initial === $option['value'] ? 'true' : 'false' }}"
                    tabindex="{{ $initial === $option['value'] ? 0 : -1 }}"
                    @if ($option['disabled']) disabled aria-disabled="true" @endif
                    @if ($option['force']) data-force="{{ $option['force'] }}" @endif
                    @class([
                        'inline-flex shrink-0 items-center justify-center rounded-sm border border-transparent text-ink-muted transition-colors duration-100 focus-ring',
                        'state-hover:text-ink aria-checked:border-control aria-checked:bg-white aria-checked:text-ink',
                        'disabled:cursor-not-allowed disabled:border-dashed disabled:border-control disabled:text-ink-muted',
                        $small ? 'size-6.5' : 'size-7.5',
                    ])
                ><x-ui.icon :name="$option['icon']" class="size-4" /></button>
            </x-ui.tooltip>
        @else
            <button
                type="button"
                role="radio"
                data-value="{{ $option['value'] }}"
                x-bind:aria-checked="String(value) === @js($option['value']) ? 'true' : 'false'"
                x-bind:tabindex="String(value) === @js($option['value']) ? 0 : -1"
                x-bind:data-segmented-indicator="String(value) === @js($option['value']) ? '' : null"
                x-on:click="value = @js($option['value'])"
                aria-checked="{{ $initial === $option['value'] ? 'true' : 'false' }}"
                tabindex="{{ $initial === $option['value'] ? 0 : -1 }}"
                @if ($option['disabled']) disabled aria-disabled="true" @endif
                @if ($option['force']) data-force="{{ $option['force'] }}" @endif
                @class([
                    'inline-flex items-center justify-center gap-1.5 rounded-sm border border-transparent px-2.5 font-body text-meta font-medium leading-none text-ink-muted transition-colors duration-100 focus-ring',
                    'state-hover:text-ink aria-checked:border-control aria-checked:bg-white aria-checked:text-ink',
                    'disabled:cursor-not-allowed disabled:text-ink-muted disabled:underline disabled:decoration-control disabled:decoration-dashed disabled:underline-offset-4',
                    $small ? 'h-6.5' : 'h-7.5',
                ])
            >
                @if ($option['icon'])
                    <x-ui.icon :name="$option['icon']" class="size-4" />
                @endif
                {{ $option['label'] }}
            </button>
        @endif
    @endforeach
</div>
