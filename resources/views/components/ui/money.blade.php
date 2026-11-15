{{--
    Port of MaskedMoney.tsx: blurs under privacy mode (see the `privacy` store in app.js).
    lean: one span and no Alpine, for table cells and dense rows; privacy mode still
    blurs it (the CSS on [data-money]), without the "hidden value" name and hint.
--}}
@props(['value', 'currency' => 'NGN', 'sign' => null, 'lean' => false])

@if ($lean)
<span data-money {{ $attributes }}>{{ $sign }}{{ \TallUiKit\Support\Money::format($value, $currency) }}</span>@php return; @endphp
@endif

<span {{ $attributes->class(['inline-flex items-baseline']) }}>{{ $sign }}<span
        data-money
        x-data
        x-bind:aria-label="$store.privacy.hidden ? 'hidden value' : null"
        x-bind:title="$store.privacy.hidden ? 'Privacy mode is on. Click the eye in the top bar or press H to reveal' : null"
    >{{ \TallUiKit\Support\Money::format($value, $currency) }}</span></span>
