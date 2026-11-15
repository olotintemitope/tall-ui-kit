{{--
    Phone number input: type="tel", inputmode tel, autocomplete tel, or tel-national
    when a country prefix is shown. Props: country (a dialling code inside the border,
    e.g. "+234"; read with the number because the prefix is part of the field's
    accessible description), size, width, invalid. Wiring and passthrough as x-ui.input.
--}}
@props(['country' => null, 'size' => 'default', 'width' => null, 'invalid' => null])

@if ($country)
    @php($countryId = 'tel-country-'.substr(md5($country.($attributes->get('id') ?? \TallUiKit\Support\FormControl::model($attributes) ?? '')), 0, 8))
    <x-ui.input type="tel" :size="$size" :width="$width" :invalid="$invalid" autocomplete="tel-national" :aria-describedby="$countryId" {{ $attributes }}>
        <x-slot:prefix><span class="type-numeric text-ink-body">{{ $country }}</span></x-slot:prefix>
    </x-ui.input>
    <span id="{{ $countryId }}" class="sr-only">Country code {{ $country }}</span>
@else
    <x-ui.input type="tel" :size="$size" :width="$width" :invalid="$invalid" {{ $attributes }} />
@endif
