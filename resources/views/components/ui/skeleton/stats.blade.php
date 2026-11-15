{{-- Loading state for a metrics strip: x-ui.stat-strip with skeleton cells. Props: count (4). --}}
@props(['count' => 4])

<x-ui.stat-strip :columns="$count" {{ $attributes }}>
    @for ($i = 0; $i < $count; $i++)
        <x-ui.skeleton.stat />
    @endfor
</x-ui.stat-strip>
