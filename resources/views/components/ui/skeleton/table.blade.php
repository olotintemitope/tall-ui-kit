{{--
    Loading state for an x-ui.table: the 36px header and 40px rows on 1px rules, in the
    table's border and radius. Props: rows (5), columns (4), label, flush (no outer
    border, for a table inside a card or section).
--}}
@props(['rows' => 5, 'columns' => 4, 'label' => 'Loading', 'flush' => false])

<div {{ $attributes->class(['overflow-hidden bg-white', 'rounded-lg border border-line' => ! $flush]) }} role="status" data-skeleton="table">
    <span class="sr-only">{{ $label }}</span>
    <div aria-hidden="true">
        <div class="flex h-9 items-center gap-4 border-b border-line bg-panel-footer px-4">
            @for ($c = 0; $c < $columns; $c++)
                <x-ui.skeleton @class(['h-2.5', 'flex-1' => $c === 1, 'w-16' => $c !== 1]) />
            @endfor
        </div>
        @for ($i = 0; $i < $rows; $i++)
            <div class="flex h-10 items-center gap-4 border-b border-line px-4 last:border-b-0">
                @for ($c = 0; $c < $columns; $c++)
                    <x-ui.skeleton @class(['h-3.5', 'flex-1' => $c === 1, 'w-16' => $c !== 1]) />
                @endfor
            </div>
        @endfor
    </div>
</div>
