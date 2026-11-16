{{--
    x-ui.file with the v0.9 props (limits, reorder, paste, capture, chunked). Included by
    components/ui/file.blade.php with its variables; the same drop area and input, one
    Alpine component (uiFile) and rows built by resources/js/file-core.js from the
    <template> below (names set with textContent). The lists are wire:ignore: they belong
    to the browser, so a re-render of the Livewire component keeps them.
--}}
@php
    $listId = $controlId.'-list';
    $sortable = $reorder && $multiple;
    $fileOptions = [
        'maxSize' => $maxBytes, 'maxFiles' => $maxFiles ? (int) $maxFiles : null, 'maxTotal' => $totalBytes,
        'validateType' => true, 'paste' => (bool) $paste, 'reorderAction' => $reorderAction, 'removeAction' => $removeAction,
        'chunked' => $chunked ? rtrim((string) $chunked, '/') : null, 'chunkSize' => $chunkSize ? (int) $chunkSize : 2097152,
        'images' => $variant === 'images',
        // A `files` slot (e.g. x-ui.upload-progress.list) lists the accepted files instead.
        'slot' => isset($files) && ! $sortable,
    ];
@endphp

<div
    x-data="uiFile(@js($fileOptions))"
    {{ $attributes->only('class')->class(['w-full']) }}
    data-file-field
    @if ($paste) data-file-paste @endif
>
    <div
        @class([
            'relative flex flex-col items-start gap-1 rounded-md border border-dashed bg-surface px-4 py-4 transition-colors duration-100',
            'has-focus-visible:border-focus has-focus-visible:ring-2 has-focus-visible:ring-primary/20',
            'has-disabled:bg-surface-subtle has-disabled:cursor-not-allowed',
            'border-danger' => $isInvalid,
            'border-control hover:border-control-hover' => ! $isInvalid,
        ])
        x-bind:class="dragging && 'border-secondary! bg-surface-subtle'"
    >
        <input
            type="file"
            @if ($variant === 'images' && ! $attributes->has('accept')) accept="image/*" @endif
            id="{{ $controlId }}"
            data-ui="file"
            @if ($multiple) multiple @endif
            @if ($captureValue) capture="{{ $captureValue }}" @endif
            @if ($chunked) data-chunked @endif
            @if ($isInvalid) aria-invalid="true" @endif
            @if ($required) aria-required="true" @endif
            aria-describedby="{{ trim($areaHintId.' '.\TallUiKit\Support\FormControl::describedBy($controlId, false, (bool) $message)) }}"
            x-on:dragenter="dragging = true"
            x-on:dragleave="dragging = false"
            x-on:drop="dragging = false"
            {{ $attributes->except(['class', 'id'])->class(['absolute inset-0 size-full cursor-pointer opacity-0 disabled:cursor-not-allowed']) }}
        />
        <p class="type-body text-ink! pointer-events-none">
            <span class="font-medium underline underline-offset-2 decoration-line-strong">Choose {{ $multiple ? 'files' : 'a file' }}</span> or drag {{ $multiple ? 'them' : 'it' }} here
        </p>
        <p id="{{ $areaHintId }}" class="type-caption pointer-events-none">{{ $hint ?? ($multiple ? 'Any number of files.' : 'One file.') }}</p>
        <div x-show="progress !== null" x-cloak class="mt-2 h-1 w-full overflow-hidden rounded-full bg-surface-muted" role="progressbar" aria-label="Upload progress" x-bind:aria-valuenow="progress" aria-valuemin="0" aria-valuemax="100">
            <div class="h-full origin-left bg-secondary transition-transform duration-150 motion-reduce:transition-none" x-bind:style="`transform: scaleX(${(progress ?? 0) / 100})`"></div>
        </div>
    </div>

    @isset($files)
        {{ $files }}
    @endisset

    <div class="min-w-0" data-file-lists>
        @if ($sortable)
            <div class="mt-2 min-w-0" data-sortable-root wire:ignore>
                <p id="{{ $listId }}-help" class="sr-only">Press Space or Enter on a grip to pick up a file, the arrow keys to move it, Space or Enter to drop it and Escape to cancel.</p>
                <ul id="{{ $listId }}" hidden aria-label="Chosen files, in order" class="ui-sortable min-w-0 divide-y divide-line rounded-md border border-line bg-surface" data-sortable data-file-list></ul>
                <p class="sr-only" aria-live="polite" aria-atomic="true" data-sortable-live></p>
            </div>
        @elseif ($variant === 'images')
            <ul id="{{ $listId }}" hidden class="mt-3 grid grid-cols-3 gap-3 sm:grid-cols-4" aria-label="Chosen images" data-file-previews data-file-list wire:ignore></ul>
        @else
            <ul id="{{ $listId }}" hidden class="mt-2 divide-y divide-line rounded-md border border-line" aria-label="Chosen files" data-file-list wire:ignore></ul>
        @endif
        <ul hidden class="mt-2 divide-y divide-line rounded-md border border-danger-edge" aria-label="Files not added" data-file-rejected wire:ignore></ul>
    </div>

    <template data-file-row>
        @if ($variant === 'images' && ! $sortable)
            <li class="ui-file-row flex min-w-0 flex-col gap-1">
                <div class="relative flex aspect-square items-center justify-center overflow-hidden rounded-lg border border-line bg-surface-subtle">
                    <img data-thumb alt="" class="size-full object-cover" decoding="async">
                    <span class="ui-file-type absolute left-2 top-2" data-type aria-hidden="true"></span>
                    <button type="button" class="absolute right-1 top-1 flex size-6 items-center justify-center rounded-md border border-control bg-surface text-ink focus-ring hover:bg-surface-subtle" data-file-remove><x-ui.icon name="x" class="size-3.5" /></button>
                </div>
                <span class="type-caption wrap-anywhere" data-name></span>
                <span class="sr-only" data-meta></span>
                <span class="type-caption text-danger-text! wrap-anywhere" data-error hidden></span>
            </li>
        @else
            <li class="ui-file-row ui-sortable-item flex min-w-0 items-start gap-2 bg-surface px-2 py-2" data-sortable-item>
                @if ($sortable)
                    <button type="button" class="ui-sortable-handle focus-ring" aria-describedby="{{ $listId }}-help" data-sortable-handle><x-ui.icon name="grip-vertical" class="size-4" /></button>
                @endif
                <span class="ui-file-type mt-2" data-type aria-hidden="true"></span>
                <div class="min-w-0 flex-1 py-1.5">
                    <p class="type-body wrap-anywhere text-ink" data-name></p>
                    <p class="type-meta" data-meta></p>
                    <p class="type-caption text-danger-text! wrap-anywhere" data-error hidden></p>
                </div>
                @if ($sortable)
                    <span class="flex shrink-0 items-center gap-0.5 self-center"><button type="button" tabindex="-1" class="flex size-6 items-center justify-center rounded-sm text-ink-muted hover:bg-surface-subtle hover:text-ink focus-ring" data-sortable-move="up"><x-ui.icon name="chevron-up" class="size-4" /></button><button type="button" tabindex="-1" class="flex size-6 items-center justify-center rounded-sm text-ink-muted hover:bg-surface-subtle hover:text-ink focus-ring" data-sortable-move="down"><x-ui.icon name="chevron-down" class="size-4" /></button></span>
                @endif
                <x-ui.button variant="ghost" size="sm" type="button" class="self-center" data-file-remove>Remove</x-ui.button>
            </li>
        @endif
    </template>

    <p class="sr-only" role="status" aria-live="polite" x-text="status" data-file-live></p>

    {{-- Inside x-ui.field the field prints the message (with this id); printing it here too would repeat it. --}}
    @if ($message && $__env->getConsumableComponentData('label') === null)
        <p id="{{ \TallUiKit\Support\FormControl::errorId($controlId) }}" class="type-caption text-danger-text! mt-1">{{ $message }}</p>
    @endif
</div>
