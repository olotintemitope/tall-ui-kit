{{--
    Who is typing, as text ("Ada is typing", "Ada and Bola are typing", "Several people
    are typing") with three small dots after it. The text sits in a polite status region
    that is always present, so each change is announced once; the dots are hidden from
    assistive tech. The dots fade in turn (opacity only, --motion-lg steps); under reduced
    motion they hold still. With nobody typing the line keeps its height (no jump).
    Props: names (list of first names), label (overrides the sentence).
--}}
@props(['names' => [], 'label' => null])

@php
    $names = collect($names)->filter()->values();
    $label ??= match (true) {
        $names->isEmpty() => '',
        $names->count() === 1 => $names[0].' is typing',
        $names->count() === 2 => $names[0].' and '.$names[1].' are typing',
        default => 'Several people are typing',
    };
@endphp

<div {{ $attributes->class(['ui-chat-typing flex min-h-5 items-center gap-1.5 type-meta']) }} data-chat-typing><p role="status" class="min-w-0 wrap-anywhere">{{ $label }}</p>@if ($label !== '')<span class="ui-typing-dots inline-flex items-center gap-0.5" aria-hidden="true"><span></span><span></span><span></span></span>@endif</div>
