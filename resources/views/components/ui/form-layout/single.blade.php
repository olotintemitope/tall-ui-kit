{{--
    Single-column form: a heading and description, then one raised x-ui.panel about
    640px wide on the tinted page (use the layout's surface="muted" or x-ui.form-page).
    Fields stack 16px apart; the footer slot is the panel's action bar (secondary then
    primary, full width side by side on phones). Wrap the panel in x-ui.form yourself
    or pass `form` attributes through: put x-ui.form around this component.
    Props: title, description, headingLevel (2). Slots: default (fields), footer.
--}}
@props(['title' => null, 'description' => null, 'headingLevel' => 2])

<div {{ $attributes->class(['mx-auto flex w-full max-w-[640px] flex-col gap-4']) }} data-form-layout="single">
    @if ($title)
        <div>
            <x-ui.heading :level="$headingLevel" size="heading">{{ $title }}</x-ui.heading>
            @if ($description)
                <p class="type-meta mt-1">{{ $description }}</p>
            @endif
        </div>
    @endif
    <x-ui.panel>
        {{ $slot }}
        @isset($footer)
            <x-slot:footer>{{ $footer }}</x-slot:footer>
        @endisset
    </x-ui.panel>
</div>
