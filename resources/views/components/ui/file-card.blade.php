{{--
    One file as a row, a card or a compact link (docs/components/files.md, "File card").
    The type label ("PDF", "JPG", "MP3": mono caption, ink-muted, no tile behind it) or a
    thumbnail, the full name (body, wraps anywhere: never cut off), the size or `meta` line,
    an optional status pill (one line), and the actions the file's state allows.

    Variants:
      row      (default) a 1px-ruled row: type label, name and size, status, actions
      card     a bordered card for grids: a 4:3 thumbnail or the type label, then the name,
               size and status, actions at the foot
      compact  an inline 1px-bordered chip for chat, timelines and tickets: the type label,
               name and meta; with `href` and no actions the whole chip is the link

    Status words, never colour alone: uploading "Uploading" (info) with a bar (`progress`),
    done "Uploaded" (success), failed "Failed" (danger, `error` below the name), infected
    "Infected" (danger) and blocked "Blocked" (warning): neither offers Download.

    Actions: Download (a link to `href` with the download attribute, or `downloadAction`),
    Cancel while uploading, Retry when failed, Remove. `downloadAction`, `cancelAction`,
    `retryAction` and `removeAction` are Livewire calls (e.g. "removeFile(3)"). For a
    button without a Livewire call, name it in `actions` (download, cancel, retry,
    remove): it dispatches a bubbling `ui-file` event, detail { action, key, name }. Buttons
    are named with the file ("Remove report.pdf").

    The name is user input: it is always escaped (never {!! !!}).
    Props: name (required), key (for events, default the name), size (bytes), meta (the
    line under the name instead of the size, e.g. "240 KB · PDF"), type (mime type, for
    the label when the name has no extension), href, thumb (image URL), alt, status,
    progress, error, variant (row | card | compact), as (div | li), actions,
    downloadAction, cancelAction, retryAction, removeAction, newTab (open `href` in a new
    tab instead of downloading).
--}}
@props([
    'name', 'key' => null, 'size' => null, 'meta' => null, 'type' => null, 'href' => null, 'thumb' => null, 'alt' => null,
    'status' => null, 'progress' => 0, 'error' => null, 'variant' => 'row', 'as' => 'div', 'actions' => [],
    'downloadAction' => null, 'cancelAction' => null, 'retryAction' => null, 'removeAction' => null, 'newTab' => false,
])

@php
    use Illuminate\Support\Js;
    use TallUiKit\Support\Files;

    $name = (string) $name;
    $key = (string) ($key ?? $name);
    $variant = in_array($variant, ['row', 'card', 'compact'], true) ? $variant : 'row';
    $status = in_array($status, ['uploading', 'done', 'failed', 'infected', 'blocked'], true) ? $status : null;
    $progress = (int) max(0, min(100, (int) $progress));
    $label = Files::typeLabel($name, $type);
    $metaText = $meta ?? Files::bytes($size);
    $unsafe = in_array($status, ['infected', 'blocked'], true);
    $actions = (array) $actions;
    $pill = $status ? [
        'uploading' => ['Uploading', 'info'],
        'done' => ['Uploaded', 'success'],
        'failed' => ['Failed', 'danger'],
        'infected' => ['Infected', 'danger'],
        'blocked' => ['Blocked', 'warning'],
    ][$status] : null;
    $event = fn (string $action): string => '$dispatch(\'ui-file\', { action: \''.$action.'\', key: '.Js::from($key).', name: '.Js::from($name).' })';
    $buttons = array_values(array_filter([
        ! $unsafe && $status !== 'uploading' && $status !== 'failed' && (($href && $variant !== 'compact') || $downloadAction || in_array('download', $actions, true))
            ? ['download', $newTab ? 'Open' : 'Download', ($newTab ? 'Open ' : 'Download ').$name, $downloadAction] : null,
        $status === 'uploading' && ($cancelAction || in_array('cancel', $actions, true)) ? ['cancel', 'Cancel', 'Cancel upload of '.$name, $cancelAction] : null,
        $status === 'failed' && ($retryAction || in_array('retry', $actions, true)) ? ['retry', 'Retry', 'Retry upload of '.$name, $retryAction] : null,
        $removeAction || in_array('remove', $actions, true) ? ['remove', 'Remove', 'Remove '.$name, $removeAction] : null,
    ]));
    $needsAlpine = collect($buttons)->contains(fn ($b) => ! $b[3] && ! ($b[0] === 'download' && $href));
    $tag = $as === 'li' ? 'li' : 'div';
    $wholeLink = $variant === 'compact' && $href && ! $buttons && ! $unsafe;
@endphp

@if ($wholeLink)
{{-- Compact link: the chip is the link (chat and timeline attachments). --}}
@if ($tag === 'li')<li class="min-w-0" {{ $attributes->only('wire:key') }}>@endif<a href="{{ $href }}" @if ($newTab) target="_blank" rel="noopener" @endif {{ ($tag === 'li' ? $attributes->except('wire:key') : $attributes)->class(['ui-file-card inline-flex max-w-full items-center gap-2 rounded-md border border-line bg-surface px-3 py-2 hover:bg-surface-subtle focus-ring']) }} data-file-card="compact" data-type="{{ $label }}"><span class="ui-file-type" aria-hidden="true">{{ $label }}</span><span class="type-body min-w-0 wrap-anywhere text-ink">{{ $name }}</span>@if ($metaText)<span class="type-meta shrink-0">{{ $metaText }}</span>@endif<span class="sr-only">, {{ $label }} file</span></a>@if ($tag === 'li')</li>@endif
@else
<{{ $tag }} {{ $attributes->class([
    'ui-file-card min-w-0',
    'flex items-start gap-3 py-3' => $variant === 'row',
    'flex flex-col rounded-lg border border-line bg-surface' => $variant === 'card',
    'inline-flex max-w-full flex-wrap items-center gap-x-2 gap-y-1 rounded-md border border-line bg-surface px-3 py-2' => $variant === 'compact',
]) }} data-file-card="{{ $variant }}" data-type="{{ $label }}" @if ($status) data-status="{{ $status }}" @endif @if ($needsAlpine) x-data @endif>
    @if ($variant === 'card')
        <div class="flex aspect-[4/3] items-center justify-center overflow-hidden rounded-t-lg border-b border-line bg-surface-subtle">
            @if ($thumb && ! $unsafe)
                <img src="{{ $thumb }}" alt="{{ $alt ?? '' }}" loading="lazy" decoding="async" class="size-full object-cover">
            @else
                <span class="ui-file-type" data-size="lg" aria-hidden="true">{{ $label }}</span>
            @endif
        </div>
        <div class="flex min-w-0 flex-1 flex-col gap-1 p-3">
    @else
        <span class="ui-file-type {{ $variant === 'row' ? 'mt-0.5' : '' }}" aria-hidden="true">{{ $label }}</span>
        <div class="flex min-w-0 flex-1 flex-col gap-1">
    @endif
            <div class="flex min-w-0 flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
                <p class="type-body min-w-0 wrap-anywhere text-ink" data-file-name>{{ $name }}<span class="sr-only">, {{ $label }} file</span></p>
                @if ($pill)
                    <span class="ui-badge" data-tone="{{ $pill[1] }}" data-size="sm" data-file-status>{{ $pill[0] }}@if ($status === 'uploading') {{ $progress }}%@endif</span>
                @endif
            </div>
            @if ($metaText)
                <p class="type-meta">{{ $metaText }}</p>
            @endif
            @if ($status === 'uploading')
                <x-ui.progress :label="'Upload of '.$name" :value="$progress" label-position="hidden" :value-text="'Uploading, '.$progress.'%'" />
            @endif
            @if (($status === 'failed' || $unsafe) && $error)
                <p class="type-caption text-danger-text! wrap-anywhere" data-file-error>{{ $error }}</p>
            @endif
    @if ($variant === 'card')
        </div>
        @if ($buttons)
            <div class="flex flex-wrap items-center gap-1 border-t border-line px-2 py-1.5">
        @endif
    @else
        </div>
        @if ($buttons)
            <div class="flex shrink-0 flex-wrap items-center gap-1">
        @endif
    @endif
    @foreach ($buttons as [$action, $text, $aria, $call])
        @if ($action === 'download' && $href && ! $call)
            <x-ui.button variant="ghost" size="sm" :href="$href" :aria-label="$aria" :download="$newTab ? null : ''" :target="$newTab ? '_blank' : null" :rel="$newTab ? 'noopener' : null" data-file-action="download">{{ $text }}</x-ui.button>
        @elseif ($call)
            <x-ui.button variant="ghost" size="sm" type="button" :aria-label="$aria" wire:click="{{ $call }}" data-file-action="{{ $action }}">{{ $text }}</x-ui.button>
        @else
            <x-ui.button variant="ghost" size="sm" type="button" :aria-label="$aria" x-on:click="{{ $event($action) }}" data-file-action="{{ $action }}">{{ $text }}</x-ui.button>
        @endif
    @endforeach
    @if ($buttons)
        </div>
    @endif
</{{ $tag }}>
@endif
