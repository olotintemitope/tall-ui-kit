<?php

namespace TallUiKit\Support;

use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\ComponentAttributeBag;

/**
 * Shared id and error wiring for the x-ui form controls, so x-ui.field and
 * the control inside it derive the same ids without passing them around.
 */
class FormControl
{
    /**
     * The control id: an explicit id wins, otherwise one derived from the
     * field name (dot or bracket notation) or the wire:model / x-model path.
     */
    public static function id(?string $id, ?string $name): ?string
    {
        if (filled($id)) {
            return $id;
        }

        if (blank($name)) {
            return null;
        }

        return 'field-'.Str::slug(str_replace(['.', '[', ']', '_'], '-', $name));
    }

    /**
     * Width classes for a control box. `full|lg|md|sm|xs`; anything else
     * (including a parent component's unrelated `width`) falls back to the
     * control's own default. Inside x-ui.field-row every control fills its column.
     */
    public static function width(mixed $width, string $default = 'md'): string
    {
        $widths = [
            'full' => 'w-full',
            'lg' => 'w-full max-w-lg',
            'md' => 'w-full max-w-md',
            'sm' => 'w-full max-w-xs',
            'xs' => 'w-24',
            'date' => 'w-40',
            'datetime' => 'w-56',
        ];

        return $widths[is_string($width) && ! in_array($width, ['date', 'datetime'], true) ? $width : ''] ?? $widths[$default];
    }

    /**
     * One size vocabulary for every control: `sm` (32px), `default` (36px, `md` is an
     * alias) and `lg` (40px), the same heights as x-ui.button. Unknown values fall
     * back to default, so older call sites keep working.
     */
    public static function size(mixed $size): string
    {
        return match ($size) {
            'sm', 'xs' => 'sm',
            'lg' => 'lg',
            default => 'default',
        };
    }

    /** The control height for a size: `h-8|h-9|h-10`, or `min-h-*` for boxes that grow. */
    public static function height(mixed $size, bool $min = false): string
    {
        $height = ['sm' => 'h-8', 'default' => 'h-9', 'lg' => 'h-10'][self::size($size)];

        return $min ? 'min-'.$height : $height;
    }

    public static function hintId(string $id): string
    {
        return $id.'-hint';
    }

    public static function errorId(string $id): string
    {
        return $id.'-error';
    }

    /**
     * The validation key for a name in either bracket or dot notation.
     */
    public static function errorKey(?string $name): ?string
    {
        if (blank($name)) {
            return null;
        }

        return trim(str_replace(['[', ']'], ['.', ''], $name), '.');
    }

    /**
     * The first error for the field, from an explicit message or the view's error bag.
     */
    public static function error(mixed $errors, ?string $name, ?string $explicit = null): ?string
    {
        if (filled($explicit)) {
            return $explicit;
        }

        $key = self::errorKey($name);

        if ($key === null || ! $errors instanceof ViewErrorBag) {
            return null;
        }

        return $errors->first($key) ?: ($errors->first($key.'.*') ?: null);
    }

    /**
     * The aria-describedby value for a control.
     */
    public static function describedBy(?string $id, bool $hasHint, bool $hasError, ?string $extra = null): ?string
    {
        $ids = array_filter([
            $id && $hasHint ? self::hintId($id) : null,
            $id && $hasError ? self::errorId($id) : null,
            $extra,
        ]);

        return $ids === [] ? null : implode(' ', $ids);
    }

    /**
     * The model path bound through wire:model (any modifier) or x-model.
     *
     * @param  ComponentAttributeBag  $attributes
     */
    public static function model($attributes): ?string
    {
        foreach ($attributes->getAttributes() as $key => $value) {
            if (is_string($value) && (str_starts_with($key, 'wire:model') || $key === 'x-model' || str_starts_with($key, 'x-model.'))) {
                return $value;
            }
        }

        return null;
    }
}
