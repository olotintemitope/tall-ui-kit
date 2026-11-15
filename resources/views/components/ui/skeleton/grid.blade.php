{{--
    Loading state for a grid of summary cards: x-ui.skeleton.card blocks in the grid the
    cards use (1 column on phones, 2 from sm, `columns` from lg). One status for the grid.
    Props: count (6), columns (2 to 4), label.
--}}
@props(['count' => 6, 'columns' => 3, 'label' => 'Loading'])

@php
    $lg = [2 => 'lg:grid-cols-2', 3 => 'lg:grid-cols-3', 4 => 'lg:grid-cols-4'][$columns] ?? 'lg:grid-cols-3';
@endphp

<div {{ $attributes }} role="status" data-skeleton="grid">
    <span class="sr-only">{{ $label }}</span>
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 {{ $lg }}" aria-hidden="true">
        @for ($i = 0; $i < $count; $i++)
            <div class="rounded-lg border border-line bg-white p-4">
                <x-ui.skeleton.line role="label" width="w-20" />
                <x-ui.skeleton.line role="figure" width="w-28" class="mt-1" />
                <x-ui.skeleton.line role="meta" width="w-24" class="mt-1" />
            </div>
        @endfor
    </div>
</div>
