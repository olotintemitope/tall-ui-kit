<?php

namespace TallUiKit\Support;

/**
 * Money display rules from the React MaskedMoney.tsx (formatNaira and the
 * en-US USD formatter).
 */
class Money
{
    /**
     * Placeholder for a missing figure, as MaskedMoney renders it.
     */
    public const EMPTY = "\u{2014}";

    public static function format(int|float|string|null $value, string $currency = 'NGN'): string
    {
        if ($value === null) {
            return self::EMPTY;
        }

        if (is_string($value)) {
            return $value;
        }

        if ($currency === 'USD') {
            return ($value < 0 ? '-' : '').'$'.number_format(abs($value), 2);
        }

        if (! is_finite((float) $value)) {
            return '₦0';
        }

        return '₦'.($value < 0 ? '-' : '').self::groupWithoutTrailingZeros(abs($value));
    }

    /**
     * Mirrors Number.prototype.toLocaleString(): grouped thousands and at
     * most three fraction digits, trailing zeros dropped.
     */
    private static function groupWithoutTrailingZeros(int|float $value): string
    {
        $formatted = number_format($value, 3);

        return str_contains($formatted, '.') ? rtrim(rtrim($formatted, '0'), '.') : $formatted;
    }
}
