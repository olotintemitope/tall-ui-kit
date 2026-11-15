{{--
    A day group of notification rows: Today, Yesterday or Earlier (see
    App\Services\Notifications\NotificationService::group). Props: label, level (3:
    the heading level; 2 on the full page). Slot: x-ui.notification-item rows.
--}}
@props(['label', 'level' => 3])

@php $headingId = 'notification-group-'.\Illuminate\Support\Str::slug($label).'-'.substr(md5(uniqid('', true)), 0, 6); @endphp

<div role="group" aria-labelledby="{{ $headingId }}" {{ $attributes->merge(['data-notification-group' => $label]) }}>
    <h{{ $level }} id="{{ $headingId }}" class="type-label px-4 pb-1 pt-3">{{ $label }}</h{{ $level }}>
    <ul role="list" class="divide-y divide-line">{{ $slot }}</ul>
</div>
