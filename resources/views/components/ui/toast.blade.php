{{--
    One toast, styled like x-ui.alert: a 1px tinted border, a light fill and tone text.
    No icon tile, no edge bar. Normally drawn by x-ui.toaster from the `toasts` store
    (resources/js/toasts.js); render it directly only for previews.
    Props: variant (success | error | warning | info | neutral | loading; `danger` is read
    as error; `tone` is an alias of `variant`), title,
    description, action (['label' => 'Undo', 'method' => 'undo'] or ['label' => 'View',
    'url' => '/admin/payouts']), dismissible (true), duration (ms, 0 sticky; previews
    ignore it). `bind` (a JS expression, e.g. "toast") renders the bound version the
    toaster uses, so both share this markup.
    Ratios on their fills: emerald-800 7.23, red-800 7.64, amber-900 8.73, blue-800
    8.43, ink on panel-footer 15.15.
--}}
@props(['variant' => 'neutral', 'tone' => null, 'title' => '', 'description' => null, 'action' => null, 'dismissible' => true, 'duration' => 4000, 'bind' => null])

@php
    $variant = $tone ?? $variant;
    $variant = $variant === 'danger' ? 'error' : $variant;
    $tones = [
        'success' => 'border-success-edge bg-success-tint text-success-strong',
        'error' => 'border-danger-edge bg-danger-tint text-danger-strong',
        'warning' => 'border-warning-edge bg-warning-tint text-warning-strong',
        'info' => 'border-info-edge bg-info-tint text-info-strong',
        'neutral' => 'border-line bg-panel-footer text-ink',
        'loading' => 'border-line bg-panel-footer text-ink',
    ];
    $base = 'pointer-events-auto flex w-full items-start gap-2.5 rounded-lg border py-2.5 pl-3 pr-2 shadow-popover';
    $b = $bind;
@endphp

@if ($b)
    <div
        data-toast
        x-bind:data-toast="{{ $b }}.variant"
        x-bind:class="@js($tones)[{{ $b }}.variant] ?? @js($tones['neutral'])"
        x-on:mouseenter="$store.toasts.pause({{ $b }}.id)"
        x-on:mouseleave="if (! $el.contains(document.activeElement)) $store.toasts.resume({{ $b }}.id)"
        x-on:focusin="$store.toasts.pause({{ $b }}.id)"
        x-on:focusout="if (! $el.contains($event.relatedTarget) && ! $el.matches(':hover')) $store.toasts.resume({{ $b }}.id)"
        {{ $attributes->class([$base]) }}
    >
        <span x-show="{{ $b }}.variant === 'loading'" class="flex h-5 items-center"><x-ui.spinner size="sm" :announce="false" /></span>
        <div class="min-w-0 flex-1 pt-px">
            <p class="font-body text-body-strong text-current" x-text="{{ $b }}.title"></p>
            <p x-show="{{ $b }}.description" class="font-body text-meta text-current" x-text="{{ $b }}.description"></p>
            <template x-if="{{ $b }}.action">
                <button type="button" x-on:click="$store.toasts.run({{ $b }})" class="-ml-1 mt-1 inline-flex min-h-6 items-center rounded-sm px-1 font-body text-body-strong text-current underline underline-offset-3 hover:no-underline focus-ring" x-text="{{ $b }}.action.label" data-toast-action></button>
            </template>
        </div>
        <button type="button" x-show="{{ $b }}.dismissible" x-on:click="$store.toasts.dismiss({{ $b }}.id)" x-bind:aria-label="'Dismiss: ' + {{ $b }}.title" class="flex size-6 shrink-0 items-center justify-center rounded-sm text-current hover:bg-ink/[0.06] focus-ring">
            <x-ui.icon name="x" class="size-3.5" />
        </button>
    </div>
@else
    <div data-toast="{{ $variant }}" data-toast-preview {{ $attributes->class([$base, $tones[$variant] ?? $tones['neutral']]) }}>
        @if ($variant === 'loading')
            <span class="flex h-5 items-center"><x-ui.spinner size="sm" :announce="false" /></span>
        @endif
        <div class="min-w-0 flex-1 pt-px">
            <p class="font-body text-body-strong text-current">{{ $title }}</p>
            @if ($description)
                <p class="font-body text-meta text-current">{{ $description }}</p>
            @endif
            @if ($action)
                @if (! empty($action['url']))
                    <a href="{{ $action['url'] }}" class="-ml-1 mt-1 inline-flex min-h-6 items-center rounded-sm px-1 font-body text-body-strong text-current underline underline-offset-3 hover:no-underline focus-ring" data-toast-action>{{ $action['label'] }}</a>
                @else
                    <button type="button" @if (! empty($action['method'])) wire:click="{{ $action['method'] }}" @endif class="-ml-1 mt-1 inline-flex min-h-6 items-center rounded-sm px-1 font-body text-body-strong text-current underline underline-offset-3 hover:no-underline focus-ring" data-toast-action>{{ $action['label'] }}</button>
                @endif
            @endif
        </div>
        @if ($dismissible && $variant !== 'loading')
            <button type="button" aria-label="Dismiss: {{ $title }}" class="flex size-6 shrink-0 items-center justify-center rounded-sm text-current hover:bg-ink/[0.06] focus-ring" x-data x-on:click="$el.closest('[data-toast]').remove()">
                <x-ui.icon name="x" class="size-3.5" />
            </button>
        @endif
    </div>
@endif
