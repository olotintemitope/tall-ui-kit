{{--
    Loading state for list rows: x-ui.card.row's box (10px padding, a body-strong title
    of 20px, a meta line of 18px, a value) on 1px `line` rules, 59px per row.
    Props: rows (5), label, flush (no outer border, for rows inside a card or section).
--}}
@props(['rows' => 5, 'label' => 'Loading', 'flush' => false])

<div {{ $attributes->class(['rounded-lg border border-line px-4' => ! $flush]) }} role="status" data-skeleton="list">
    <span class="sr-only">{{ $label }}</span>
    <div aria-hidden="true">
        @for ($i = 0; $i < $rows; $i++)
            <div class="flex items-start justify-between gap-3 border-b border-line py-2.5 last:border-b-0">
                <div class="min-w-0 flex-1">
                    <x-ui.skeleton.line role="body" width="w-2/5" />
                    <x-ui.skeleton.line role="meta" width="w-1/4" />
                </div>
                <x-ui.skeleton.line role="meta" width="w-14" />
            </div>
        @endfor
    </div>
</div>
