{{--
    Header cell. Props: numeric (right-aligned), scope (col by default; colgroup or
    rowgroup for grouped headers), row (a row header inside the body: th scope="row" in
    the body-strong role at the body cell's 40px height, e.g. the first cell of a row
    or a totals label in the table foot).
--}}
@props(['numeric' => false, 'row' => false, 'scope' => null])
@if ($row)
<th {{ $attributes->merge(['scope' => $scope ?? 'row'])->class(['h-10 py-2 wrap-break-word px-4 text-left type-body-strong', 'text-right' => $numeric]) }}>{{ $slot }}</th>
@else
<th {{ $attributes->merge(['scope' => $scope ?? 'col'])->class(['h-9 whitespace-nowrap px-4 type-label', 'text-right' => $numeric]) }}>{{ $slot }}</th>
@endif
