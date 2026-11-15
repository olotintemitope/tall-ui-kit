{{--
    Shared native <dialog> frame for every modal in the kit (x-ui.dialog, .form, .steps,
    .fullscreen, x-ui.sheet, x-ui.confirm*, x-ui.alert-dialog). Not used directly.
    Behaviour: Alpine.data('uiDialog') in resources/js/dialogs.js. Motion: .ui-dialog /
    .ui-sheet in app.css (fade plus scale from 0.98; sheets slide), instant under
    reduced motion. `wire:ignore.self` keeps the open state through Livewire morphs.
    Props: name, title, description, size (sm 400 | md 560 | lg 720 | xl 960),
    kind (dialog | sheet | fullscreen), sheetSize (md 480 | lg 640), role (dialog |
    alertdialog), dismissible (the backdrop closes it: simple dialogs only), guard
    (null | dirty), initialFocus (first | cancel | title), steps, aliases (other event
    names it answers), closeButton. Slots: default (body), footer, header (after the title).
--}}
@props([
    'name',
    'title',
    'description' => null,
    'size' => 'md',
    'kind' => 'dialog',
    'sheetSize' => 'md',
    'role' => 'dialog',
    'dismissible' => true,
    'guard' => null,
    'initialFocus' => 'first',
    'steps' => 1,
    'aliases' => [],
    'closeButton' => true,
    'idPrefix' => 'dialog',
])

@php
    $sizes = ['sm' => 'sm:max-w-[400px]', 'md' => 'sm:max-w-[560px]', 'lg' => 'sm:max-w-[720px]', 'xl' => 'sm:max-w-[960px]'];
    $sheetSizes = ['md' => 'sm:max-w-[480px]', 'lg' => 'sm:max-w-[640px]'];
    $titleId = $idPrefix.'-'.$name.'-title';
    $descriptionId = $description ? $idPrefix.'-'.$name.'-description' : null;
    $frame = match ($kind) {
        'sheet' => ['ui-sheet m-0 ml-auto h-dvh max-h-dvh w-full max-w-full rounded-none border-0 sm:border-l sm:border-panel-border', $sheetSizes[$sheetSize] ?? $sheetSizes['md']],
        'fullscreen' => ['ui-dialog ui-dialog-fullscreen m-0 h-dvh max-h-dvh w-full max-w-full rounded-none border-0 sm:mx-auto sm:mt-[10vh] sm:h-fit sm:max-h-[80dvh] sm:w-[calc(100%-2rem)] sm:rounded-lg sm:border sm:border-panel-border', $sizes[$size] ?? $sizes['md']],
        default => ['ui-dialog mx-auto mt-[10vh] mb-auto h-fit max-h-[80dvh] w-[calc(100%-2rem)] rounded-lg border border-panel-border', $sizes[$size] ?? $sizes['md']],
    };
    $config = [
        'dismissible' => (bool) $dismissible,
        'guard' => $guard,
        'initialFocus' => $initialFocus,
        'steps' => (int) $steps,
        'aliases' => array_values((array) $aliases),
    ];
@endphp

<div
    x-data="uiDialog(@js($name), @js($config))"
    x-on:open-dialog.window="if (matches($event.detail)) show()"
    x-on:close-dialog.window="if (matches($event.detail)) close(true)"
    @if ($aliases)
        x-on:open-confirm.window="if (matches($event.detail)) show()"
        x-on:close-confirm.window="if (matches($event.detail)) close(true)"
    @endif
    class="contents"
    data-dialog-host="{{ $name }}"
    {{ $attributes->only(['wire:key']) }}
>
    <dialog
        x-ref="dialog"
        wire:ignore.self
        role="{{ $role }}"
        aria-labelledby="{{ $titleId }}"
        @if ($descriptionId) aria-describedby="{{ $descriptionId }}" @endif
        x-bind:aria-busy="busy ? 'true' : null"
        data-dialog="{{ $kind }}"
        data-dialog-size="{{ $kind === 'sheet' ? $sheetSize : $size }}"
        {{ $attributes->except(['wire:key'])->class(['bg-popover p-0 text-ink shadow-dialog', ...$frame]) }}
    >
        <div class="flex max-h-[inherit] flex-col {{ $kind === 'dialog' ? '' : 'h-full' }}">
            <div class="flex shrink-0 items-start gap-3 border-b border-line px-5 py-4">
                <div class="min-w-0 flex-1">
                    <h2 id="{{ $titleId }}" tabindex="-1" class="type-heading rounded-sm focus-ring" data-dialog-title>{{ $title }}</h2>
                    @if ($description)
                        <p id="{{ $descriptionId }}" class="type-meta mt-0.5">{{ $description }}</p>
                    @endif
                    {{ $header ?? '' }}
                </div>
                @if ($closeButton)
                    <button type="button" x-on:click="close()" x-bind:aria-disabled="busy ? 'true' : null" data-dialog-close class="-mr-2 -mt-1 flex size-8 shrink-0 items-center justify-center rounded-md text-ink-muted hover:bg-surface-subtle hover:text-ink aria-disabled:cursor-not-allowed focus-ring" aria-label="Close">
                        <x-ui.icon name="x" class="size-4" />
                    </button>
                @endif
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto px-5 py-4 type-body" data-dialog-body>
                <div x-show="error" x-cloak class="mb-4">
                    <x-ui.alert tone="danger" tabindex="-1" data-dialog-error><span x-text="error"></span></x-ui.alert>
                </div>
                {{ $slot }}
            </div>

            @isset($footer)
                <div class="flex shrink-0 flex-wrap items-center justify-end gap-2 border-t border-line bg-popover px-5 py-3" data-dialog-footer>{{ $footer }}</div>
            @endisset
        </div>
    </dialog>

    {{ $after ?? '' }}
</div>
