{{--
    "Load more" for long lists: "Showing 20 of 134" in meta and an outline button that
    shows its loading state while the action runs. `auto` also loads the next page when
    the button scrolls into view (Alpine Intersect, bundled with Livewire); the button
    stays as the manual fallback and for keyboard users. Hidden once everything shows.
    Props: shown, total, action (the Livewire method, default "loadMore"), label, auto,
    noun (for the count line, e.g. "transactions").
--}}
@props(['shown', 'total', 'action' => 'loadMore', 'label' => 'Load more', 'auto' => false, 'noun' => null])

@php
    $count = 'Showing '.number_format($shown).' of '.number_format($total).($noun ? ' '.$noun : '');
@endphp

<div {{ $attributes->class(['flex flex-wrap items-center justify-between gap-3 py-3']) }} data-load-more>
    <p class="type-meta" role="status">{{ $count }}</p>
    @if ($shown < $total)
        @if ($auto)
            <x-ui.button variant="outline" size="sm" wire:click="{{ $action }}" :loading="$action" x-data x-intersect.margin.200px="$wire.{{ $action }}()" data-load-more-auto>{{ $label }}</x-ui.button>
        @else
            <x-ui.button variant="outline" size="sm" wire:click="{{ $action }}" :loading="$action">{{ $label }}</x-ui.button>
        @endif
    @endif
</div>
