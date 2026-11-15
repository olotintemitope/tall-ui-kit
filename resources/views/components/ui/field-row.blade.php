{{--
    Fields side by side from md up (stacked below), 12px column gap, 16px row gap.
    Labels share a top line even when only one field has a hint. Controls inside fill
    their column. Props: cols (2|3).
--}}
@props(['cols' => 2])

<div {{ $attributes->class([
    'grid grid-cols-1 items-start gap-x-3 gap-y-4',
    'md:grid-cols-3' => (int) $cols === 3,
    'md:grid-cols-2' => (int) $cols !== 3,
    '[&_[data-control-box]]:w-full [&_[data-control-box]]:max-w-none',
]) }}>{{ $slot }}</div>
