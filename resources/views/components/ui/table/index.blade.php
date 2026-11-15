{{--
    Dense data table, flat and bordered. Scrolls sideways inside its border on narrow
    screens; cells never wrap unless a td opts in with `wrap`.
    Props: flush (drop the outer border and radius, for tables inside a section that
    already has rules), caption (visually hidden by default; also names the scroll
    region), captionVisible (show the caption above the table in the label role),
    sticky (the header row sticks to the top of the table's own scroll box, which is
    `maxHeight` tall, default max-h-[32rem]; a scroll box is needed because a sideways
    scroller cannot also stick to the page), maxHeight.
    Slots: head (x-ui.table.th cells; attributes go on the <thead>, e.g.
    class="hidden md:table-header-group"), colgroup (<col> elements, e.g. widths or a
    span for grouped columns), default (x-ui.table.row rows), foot (rows for a <tfoot>:
    totals, with x-ui.table.th row for the label; drawn in body-strong over a 1px rule).
    When the table scrolls the wrapper becomes a focusable region named by the
    caption, so it can be scrolled by keyboard.
--}}
@props(['flush' => false, 'caption' => null, 'captionVisible' => false, 'sticky' => false, 'maxHeight' => null])

@php
    $maxHeight = $sticky ? ($maxHeight ?? 'max-h-[32rem]') : null;
@endphp

{{-- Focusable (and named by the caption) only while it actually scrolls, so keyboard users can scroll it.
     Measured only by the ResizeObserver (its first callback runs after layout), never during
     Alpine's init, where a read per table forced a layout per table. --}}
<div
    {{ $attributes->class([
        'focus-ring',
        'overflow-x-auto' => ! ($sticky && $maxHeight),
        'overflow-auto' => $sticky && $maxHeight,
        $maxHeight,
        'rounded-lg border border-line' => ! $flush,
    ]) }}
    x-data="{ scrolls: false }"
    x-init="new ResizeObserver(() => { scrolls = $el.scrollWidth > $el.clientWidth + 1 || $el.scrollHeight > $el.clientHeight + 1 }).observe($el)"
    x-bind:tabindex="scrolls ? 0 : null"
    x-bind:role="scrolls ? 'region' : null"
    x-bind:aria-label="scrolls ? @js($caption ?? 'Table') : null"
    data-table
    @if ($sticky) data-table-sticky @endif
>
    <table @class(['w-full text-left', 'border-collapse' => ! $sticky, 'border-separate border-spacing-0' => $sticky])>
        @if ($caption)
            <caption @class(['sr-only' => ! $captionVisible, 'type-label px-4 py-2 text-left' => $captionVisible])>{{ $caption }}</caption>
        @endif
        @isset($colgroup)
            <colgroup {{ $colgroup->attributes }}>{{ $colgroup }}</colgroup>
        @endisset
        @isset($head)
            <thead {{ $head->attributes->class([
                'sticky z-10 [&_th]:border-b [&_th]:border-line [&_th]:bg-panel-footer' => $sticky,
                'top-0' => $sticky,
            ]) }}>
                <tr @class(['bg-panel-footer', 'border-b border-line' => ! $sticky])>{{ $head }}</tr>
            </thead>
        @endisset
        <tbody @class(['divide-y divide-line' => ! $sticky, '[&>tr:not(:last-child)>*]:border-b [&>tr>*]:border-line' => $sticky])>{{ $slot }}</tbody>
        @isset($foot)
            <tfoot {{ $foot->attributes->class(['[&_td]:type-body-strong [&_td.type-numeric]:font-medium [&>tr>*]:border-t [&>tr>*]:border-line bg-panel-footer']) }}>{{ $foot }}</tfoot>
        @endisset
    </table>
</div>
