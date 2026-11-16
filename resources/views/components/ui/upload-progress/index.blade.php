{{--
    One file's upload (docs/components/workflow.md, "Upload progress"): the type label
    (the x-ui.file-card one: "PDF", "ZIP"), the file name
    (body, never cut: long names wrap anywhere), its size and status (meta), a linear
    x-ui.progress (label hidden but kept for assistive tech), and the actions for its
    state: Cancel while uploading, Retry and Remove when it failed, Remove when done.
    Server-driven: the page passes the state (for uploads it tracks itself, e.g. a
    direct-to-storage upload). For Livewire's own wire:model uploads, use
    x-ui.upload-progress.list, which follows Livewire's upload events in the browser.

    Status words, not colour alone: "Waiting", "Uploading, 45%", "Uploaded", "Failed"
    (red-700, with `error` below). The bar takes the danger tone on failure and the
    success tone when done. Action buttons are named with the file ("Cancel upload of
    report.pdf"); `cancelAction`, `retryAction` and `removeAction` are Livewire calls
    (e.g. "removeFile(3)"); without them the buttons dispatch a bubbling `ui-upload`
    event, detail { action: 'cancel' | 'retry' | 'remove', name }.

    Props: name, size (bytes), progress (0 to 100), status (queued | uploading | done |
    failed), error, cancelAction, retryAction, removeAction, as (div | li).
--}}
@props([
    'name', 'size' => null, 'progress' => 0, 'status' => 'uploading', 'error' => null,
    'cancelAction' => null, 'retryAction' => null, 'removeAction' => null, 'as' => 'div',
])

@php
    $status = in_array($status, ['queued', 'uploading', 'done', 'failed'], true) ? $status : 'uploading';
    $progress = (int) max(0, min(100, (int) $progress));
    $bytes = function (?float $n): ?string {
        if ($n === null) {
            return null;
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($n >= 1000 && $i < count($units) - 1) {
            $n /= 1000;
            $i++;
        }

        return ($i === 0 ? number_format($n) : rtrim(rtrim(number_format($n, 1), '0'), '.')).' '.$units[$i];
    };
    $word = ['queued' => 'Waiting', 'uploading' => 'Uploading, '.$progress.'%', 'done' => 'Uploaded', 'failed' => 'Failed'][$status];
    $tag = $as === 'li' ? 'li' : 'div';
    $event = fn (string $action): string => "\$dispatch('ui-upload', { action: '{$action}', name: ".\Illuminate\Support\Js::from($name).' })';
    $buttons = array_filter([
        in_array($status, ['queued', 'uploading'], true) ? ['cancel', 'Cancel', 'Cancel upload of '.$name, $cancelAction] : null,
        $status === 'failed' ? ['retry', 'Retry', 'Retry upload of '.$name, $retryAction] : null,
        in_array($status, ['done', 'failed'], true) ? ['remove', 'Remove', 'Remove '.$name, $removeAction] : null,
    ]);
@endphp

<{{ $tag }} {{ $attributes->class(['flex min-w-0 items-start gap-3 py-3']) }} data-upload-row data-status="{{ $status }}">
    <span class="ui-file-type mt-0.5" aria-hidden="true" data-upload-type>{{ \TallUiKit\Support\Files::typeLabel($name) }}</span>
    <div class="flex min-w-0 flex-1 flex-col gap-1.5">
        <div class="flex flex-wrap items-baseline justify-between gap-x-3">
            <p class="type-body min-w-0 wrap-anywhere text-ink">{{ $name }}</p>
            <p @class(['type-meta', 'text-danger-text!' => $status === 'failed'])>@if ($bytes($size)){{ $bytes($size) }} · @endif{{ $word }}</p>
        </div>
        @if ($status !== 'done')
            <x-ui.progress :label="'Upload of '.$name" :value="$status === 'queued' ? 0 : $progress" label-position="hidden" :tone="$status === 'failed' ? 'danger' : 'default'" :value-text="$word" />
        @endif
        @if ($status === 'failed' && $error)
            <p class="type-caption text-danger-text! wrap-anywhere">{{ $error }}</p>
        @endif
    </div>
    <div class="flex shrink-0 items-center gap-1" @if (collect($buttons)->contains(fn ($b) => ! $b[3])) x-data @endif>
        @foreach ($buttons as [$action, $text, $aria, $call])
            @if ($call)
                <x-ui.button variant="ghost" size="sm" type="button" :aria-label="$aria" wire:click="{{ $call }}" data-upload-action="{{ $action }}">{{ $text }}</x-ui.button>
            @else
                <x-ui.button variant="ghost" size="sm" type="button" :aria-label="$aria" x-on:click="{{ $event($action) }}" data-upload-action="{{ $action }}">{{ $text }}</x-ui.button>
            @endif
        @endforeach
    </div>
</{{ $tag }}>
