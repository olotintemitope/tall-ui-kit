{{--
    Pagination bar: "1–20 of 312" on the left, previous / pages / next on the right.
    Used three ways:
      - Livewire default: resources/views/vendor/livewire/{tailwind,simple-tailwind}.blade.php
        render it with livewire=true, so $rows->links() in a WithPagination component uses it.
      - Plain Blade default via Paginator::defaultView (AppServiceProvider).
      - Directly: <x-ui.pagination :paginator="$rows" /> (pass livewire inside Livewire views).
    Props: paginator, elements (Laravel's page window; computed when absent), livewire,
    scrollTo (selector to scroll to after a Livewire page change, false to skip).
    aria-label (default "Pagination"): name it when a page has more than one.
--}}
@props(['paginator', 'elements' => null, 'livewire' => false, 'scrollTo' => 'body'])

@php
    use Illuminate\Contracts\Pagination\LengthAwarePaginator;
    use Illuminate\Pagination\UrlWindow;

    $lengthAware = $paginator instanceof LengthAwarePaginator;

    if ($elements === null && $lengthAware) {
        $window = UrlWindow::make($paginator);
        $elements = array_filter([
            $window['first'],
            is_array($window['slider']) ? '...' : null,
            $window['slider'],
            is_array($window['last']) ? '...' : null,
            $window['last'],
        ]);
    }

    $pageName = $paginator->getPageName();
    $scroll = $scrollTo !== false
        ? "(\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}'))?.scrollIntoView()"
        : '';

    $cell = 'inline-flex h-8 min-w-8 items-center justify-center rounded-md px-2 font-mono text-numeric tabular-nums transition-colors duration-100 focus-ring';
    $nav = 'inline-flex h-8 items-center gap-1 rounded-md px-2 font-body text-meta font-medium transition-colors duration-100 focus-ring';
@endphp

@if ($paginator->hasPages() || $lengthAware)
    <nav role="navigation" aria-label="{{ $attributes->get('aria-label', 'Pagination') }}" {{ $attributes->except('aria-label')->class(['flex min-w-0 flex-wrap items-center justify-between gap-x-4 gap-y-2']) }}>
        <p class="type-meta tabular-nums">
            @if ($lengthAware)
                @if ($paginator->total() > 0)
                    <span class="text-ink font-medium">{{ number_format($paginator->firstItem()) }}&ndash;{{ number_format($paginator->lastItem()) }}</span>
                    of {{ number_format($paginator->total()) }}
                @else
                    No results
                @endif
            @else
                Page {{ $paginator->currentPage() }}
            @endif
        </p>

        @if ($paginator->hasPages())
            <div class="flex min-w-0 flex-wrap items-center gap-0.5">
                @if ($paginator->onFirstPage())
                    <span class="{{ $nav }} text-ink-muted cursor-not-allowed" role="link" aria-disabled="true" aria-label="Previous page">
                        <x-ui.icon name="chevron-left" class="size-4" /><span class="hidden sm:inline">Previous</span>
                    </span>
                @elseif ($livewire)
                    <button type="button" wire:click="previousPage('{{ $pageName }}')" x-on:click="{{ $scroll }}" wire:loading.attr="aria-busy" wire:loading.class="pointer-events-none" class="{{ $nav }} text-ink-body hover:bg-surface-subtle hover:text-ink" aria-label="Previous page">
                        <x-ui.icon name="chevron-left" class="size-4" /><span class="hidden sm:inline">Previous</span>
                    </button>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $nav }} text-ink-body hover:bg-surface-subtle hover:text-ink" aria-label="Previous page">
                        <x-ui.icon name="chevron-left" class="size-4" /><span class="hidden sm:inline">Previous</span>
                    </a>
                @endif

                @if ($lengthAware)
                    <span class="hidden sm:flex items-center gap-0.5">
                        @foreach ($elements as $element)
                            @if (is_string($element))
                                <span class="{{ $cell }} text-ink-muted" aria-hidden="true">&hellip;</span>
                            @elseif (is_array($element))
                                @foreach ($element as $page => $url)
                                    @if ($page == $paginator->currentPage())
                                        <span class="{{ $cell }} bg-surface-muted text-ink font-medium" aria-current="page">{{ $page }}</span>
                                    @elseif ($livewire)
                                        <button type="button" wire:click="gotoPage({{ $page }}, '{{ $pageName }}')" x-on:click="{{ $scroll }}" class="{{ $cell }} text-ink-body hover:bg-surface-subtle hover:text-ink" aria-label="Page {{ $page }}">{{ $page }}</button>
                                    @else
                                        <a href="{{ $url }}" class="{{ $cell }} text-ink-body hover:bg-surface-subtle hover:text-ink" aria-label="Page {{ $page }}">{{ $page }}</a>
                                    @endif
                                @endforeach
                            @endif
                        @endforeach
                    </span>
                @endif

                @if (! $paginator->hasMorePages())
                    <span class="{{ $nav }} text-ink-muted cursor-not-allowed" role="link" aria-disabled="true" aria-label="Next page">
                        <span class="hidden sm:inline">Next</span><x-ui.icon name="chevron-right" class="size-4" />
                    </span>
                @elseif ($livewire)
                    <button type="button" wire:click="nextPage('{{ $pageName }}')" x-on:click="{{ $scroll }}" wire:loading.attr="aria-busy" wire:loading.class="pointer-events-none" class="{{ $nav }} text-ink-body hover:bg-surface-subtle hover:text-ink" aria-label="Next page">
                        <span class="hidden sm:inline">Next</span><x-ui.icon name="chevron-right" class="size-4" />
                    </button>
                @else
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $nav }} text-ink-body hover:bg-surface-subtle hover:text-ink" aria-label="Next page">
                        <span class="hidden sm:inline">Next</span><x-ui.icon name="chevron-right" class="size-4" />
                    </a>
                @endif
            </div>
        @endif
    </nav>
@endif
