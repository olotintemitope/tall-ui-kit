{{--
    Compact list of errors at the top of a form, linking to each field. Shows only when
    more than `min` (3) of the given fields have errors. Props: fields (name => label, in
    form order), min (3). It is focusable (tabindex -1): after a submit with more than 3
    errors x-ui.form moves focus here instead of the first field.
--}}
@props(['fields' => [], 'min' => 3])

@php
    use TallUiKit\Support\FormControl;

    $messages = collect($fields)
        ->map(fn (string $label, string $name) => ['label' => $label, 'message' => FormControl::error($errors ?? null, $name), 'href' => '#'.FormControl::id(null, $name)])
        ->filter(fn (array $item) => $item['message'] !== null);
@endphp

@if ($messages->count() > $min)
    <div {{ $attributes->class(['rounded-md border border-danger-edge bg-danger-tint px-3 py-2.5 focus-ring']) }} role="alert" aria-labelledby="error-summary-title" tabindex="-1" data-error-summary>
        <p id="error-summary-title" class="type-body-strong">{{ $messages->count() }} fields need attention</p>
        <ul class="mt-1 flex flex-col gap-0.5">
            @foreach ($messages as $item)
                <li class="type-meta"><a href="{{ $item['href'] }}" class="text-danger-text underline underline-offset-2 hover:no-underline rounded-sm focus-ring">{{ $item['label'] }}</a>: {{ $item['message'] }}</li>
            @endforeach
        </ul>
    </div>
@endif
