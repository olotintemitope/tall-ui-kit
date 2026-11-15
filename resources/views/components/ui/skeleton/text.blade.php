{{--
    Loading state for running text: `lines` body lines (20px each), the last one shorter.
    Props: lines (3), label.
--}}
@props(['lines' => 3, 'label' => 'Loading'])

<div {{ $attributes }} role="status" data-skeleton="text">
    <span class="sr-only">{{ $label }}</span>
    @for ($i = 0; $i < $lines; $i++)
        <x-ui.skeleton.line role="body" :width="$i === $lines - 1 ? 'w-3/5' : 'w-full'" />
    @endfor
</div>
