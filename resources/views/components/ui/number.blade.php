{{--
    A number in the numeric role (mono, tabular), formatted by TallUiKit\Support\Format.
    Props: value, format (plain 1,204 | compact 1.2k, with the full value as a
    title | percent: value is a ratio, 0.124 is 12.4%), precision (decimals).
    Money is x-ui.money.
--}}
@props(['value', 'format' => 'plain', 'precision' => null])

@php
    use TallUiKit\Support\Format;

    $text = match ($format) {
        'compact' => Format::compact($value, $precision ?? 1),
        'percent' => Format::percent($value, $precision ?? 1),
        default => Format::number($value, $precision ?? 0),
    };
    $full = $format === 'compact' && is_numeric($value) ? Format::number($value) : null;
@endphp

<span @if ($full && $full !== $text) title="{{ $full }}" @endif {{ $attributes->class(['type-numeric']) }}>{{ $text }}</span>