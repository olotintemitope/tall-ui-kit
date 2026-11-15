{{--
    One accordion row: x-ui.disclosure with 1px rules. Props: summary, open, meta.
    Exclusivity comes from the parent x-ui.accordion (`exclusive`, `name`).
--}}
@props(['summary' => null, 'open' => false, 'meta' => null])
@aware(['exclusive' => false, 'name' => 'accordion'])

<x-ui.disclosure :summary="$summary" :open="$open" :meta="$meta" :name="$exclusive ? $name : null" bordered {{ $attributes }}>{{ $slot }}</x-ui.disclosure>
