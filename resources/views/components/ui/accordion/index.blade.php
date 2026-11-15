{{--
    A stack of disclosures divided by 1px `line` rules (x-ui.accordion.item, which is
    x-ui.disclosure with `bordered`). Items open independently unless `exclusive`: then
    opening one closes the others in the same accordion (give each exclusive accordion
    on a page its own `name`). Put it under a heading; summaries are buttons, not
    headings. Props: exclusive, name ("accordion"), label (aria-label for a role=group).
    Slot: x-ui.accordion.item elements.
--}}
@props(['exclusive' => false, 'name' => 'accordion', 'label' => null])

<div {{ $attributes->class(['flex flex-col']) }} @if ($label) role="group" aria-label="{{ $label }}" @endif data-accordion @if ($exclusive) data-accordion-exclusive="{{ $name }}" @endif>
    {{ $slot }}
</div>
