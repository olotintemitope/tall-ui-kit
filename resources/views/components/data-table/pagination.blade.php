{{--
    x-data-table.pagination: previous, page numbers (from 640px) and next, in the
    x-ui.pagination look. Wrapped rather than reused because the table needs two things
    the kit bar does not do yet (proposed for the kit): unavailable buttons stay buttons
    with aria-disabled (never `disabled`, which would drop focus while a page loads), and
    a simple paginator without page numbers. After a page change focus moves to the table
    caption (docs/ui-kit-tables.md, Focus). Props: paginator, name (table name).
    Classes: inline-flex h-8 min-w-8 items-center justify-center gap-1 rounded-md px-2
    font-body font-mono text-meta text-numeric font-medium tabular-nums transition-colors
    duration-100 focus-ring text-ink-body hover:bg-surface-subtle hover:text-ink
    cursor-not-allowed text-ink-muted bg-surface-muted text-ink hidden sm:inline sm:flex.
--}}
@props(['paginator', 'name'])
@php
    use Illuminate\Contracts\Pagination\LengthAwarePaginator;
    use Illuminate\Pagination\UrlWindow;

    $current = $paginator->currentPage();
    $pages = [];
    if ($paginator instanceof LengthAwarePaginator && $paginator->lastPage() > 1) {
        $window = UrlWindow::make($paginator);
        foreach ([$window['first'], is_array($window['slider']) ? '...' : null, $window['slider'], is_array($window['last']) ? '...' : null, $window['last']] as $part) {
            if (is_array($part)) { foreach (array_keys($part) as $p) { $pages[] = $p; } } elseif ($part === '...') { $pages[] = '...'; }
        }
    }
    $hasPrev = $current > 1;
    $hasNext = $paginator->hasMorePages();
    $nav = 'inline-flex h-8 items-center gap-1 rounded-md px-2 font-body text-meta font-medium transition-colors duration-100 focus-ring';
    $cell = 'inline-flex h-8 min-w-8 items-center justify-center rounded-md px-2 font-mono text-numeric tabular-nums transition-colors duration-100 focus-ring';
    $on = 'text-ink-body hover:bg-surface-subtle hover:text-ink';
    $off = 'cursor-not-allowed text-ink-muted';
    $html = '';
    foreach ($pages as $p) {
        $html .= match (true) {
            $p === '...' => '<span class="'.$cell.' text-ink-muted" aria-hidden="true">&hellip;</span>',
            $p == $current => '<span class="'.$cell.' bg-surface-muted font-medium text-ink" aria-current="page">'.$p.'</span>',
            default => '<button type="button" wire:click="gotoPage('.$p.')" data-page="'.$p.'" class="'.$cell.' '.$on.'" aria-label="Page '.$p.'" data-fk="page-'.$p.'">'.$p.'</button>',
        };
    }
@endphp
@if ($hasPrev || $hasNext)
<nav aria-label="Pagination" class="flex min-w-0 flex-wrap items-center gap-0.5" data-dt-pager>
<button type="button" @if ($hasPrev) wire:click="gotoPage({{ $current - 1 }})" data-page="{{ $current - 1 }}" @else aria-disabled="true" @endif class="{{ $nav }} {{ $hasPrev ? $on : $off }}" aria-label="Previous page" data-fk="page-prev" wire:key="{{ $name }}-page-prev"><svg class="size-4" aria-hidden="true"><use href="#dt-i-left"/></svg><span class="hidden sm:inline">Previous</span></button>
@if ($pages !== [])<span class="hidden flex-wrap items-center gap-0.5 sm:flex">{!! $html !!}</span>@endif
<button type="button" @if ($hasNext) wire:click="gotoPage({{ $current + 1 }})" data-page="{{ $current + 1 }}" @else aria-disabled="true" @endif class="{{ $nav }} {{ $hasNext ? $on : $off }}" aria-label="Next page" data-fk="page-next" wire:key="{{ $name }}-page-next"><span class="hidden sm:inline">Next</span><svg class="size-4" aria-hidden="true"><use href="#dt-i-right"/></svg></button>
</nav>
@endif
