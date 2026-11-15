{{--
    Search box with a leading icon and a clear button that appears once there is
    text. Clearing fires an input event, so wire:model.live and x-model update.
    Props: size (default h-9 | sm h-8), width (full|lg|md|sm|xs, default md), placeholder
    (Search), shortPlaceholder (shown instead when the full placeholder does not fit the
    box, e.g. "Search payouts" at 320px; resources/js/forms.js measures it on resize; if
    even that does not fit, "Search"). The accessible name stays the full placeholder.
    Other attributes go to the <input>.
--}}
@props(['size' => 'default', 'placeholder' => 'Search', 'width' => 'md', 'shortPlaceholder' => null])

@php
    $model = \TallUiKit\Support\FormControl::model($attributes);
@endphp

<div
    x-data="{ filled: false, sync() { this.filled = this.$refs.input.value !== '' }, clear() { this.$refs.input.value = ''; this.$refs.input.dispatchEvent(new Event('input', { bubbles: true })); this.$refs.input.dispatchEvent(new Event('change', { bubbles: true })); this.filled = false; this.$refs.input.focus() } }"
    x-init="$nextTick(() => sync())"
    {{ $attributes->only(['class', 'style'])->class(['relative w-full']) }}
>
    <x-ui.input
        type="search"
        :size="$size"
        :width="$width"
        :placeholder="$placeholder"
        :data-placeholder-full="$shortPlaceholder ? $placeholder : null"
        :data-placeholder-short="$shortPlaceholder"
        autocomplete="off"
        x-ref="input"
        x-on:input="sync()"
        x-on:keydown.escape="if (filled) { $event.stopPropagation(); clear() }"
        :aria-label="$attributes->get('aria-label', $placeholder)"
        {{ $attributes->except(['class', 'style']) }}
    >
        <x-slot:prefix plain><x-ui.icon name="search" class="size-4" /></x-slot:prefix>
        <x-slot:suffix plain>
            <button type="button" x-show="filled" x-cloak x-on:click="clear()" class="pointer-events-auto flex size-6 items-center justify-center rounded text-ink-muted hover:bg-surface-muted hover:text-ink focus-ring" aria-label="Clear search">
                <x-ui.icon name="x" class="size-3.5" />
            </button>
        </x-slot:suffix>
    </x-ui.input>
</div>
