{{--
    One strip of metrics divided by 1px `line` rules, no cards. Cells wrap two per
    row on phones, three from sm, and all on one row from xl.
    Props: columns (cells per row at xl, 2 to 6). Slot: x-ui.stat or
    x-ui.skeleton.stat cells.
--}}
@props(['columns' => 4])

@php
    $xl = [2 => 'xl:grid-cols-2', 3 => 'xl:grid-cols-3', 4 => 'xl:grid-cols-4', 5 => 'xl:grid-cols-5', 6 => 'xl:grid-cols-6'][$columns] ?? 'xl:grid-cols-4';
@endphp

{{-- Each cell draws its right and bottom rule; the negative margin clips the outer ones. --}}
<div {{ $attributes->class(['overflow-hidden border-y border-l border-line']) }} data-stat-strip>
    <dl class="-mr-px -mb-px grid grid-cols-2 sm:grid-cols-3 {{ $xl }}">
        {{ $slot }}
    </dl>
</div>
