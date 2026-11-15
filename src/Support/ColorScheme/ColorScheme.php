<?php

namespace TallUiKit\Support\ColorScheme;

/**
 * The colour scheme settings x-ui.head and x-ui.theme-toggle share, read from
 * config('tall-ui-kit.color_scheme') with every value checked, so a typo in config
 * falls back to the defaults instead of printing an unknown attribute.
 */
class ColorScheme
{
    /** @return list<string> the schemes people may pick, in toggle order */
    public static function schemes(): array
    {
        $schemes = array_values(array_intersect(ColorSchemeStore::SCHEMES, (array) config('tall-ui-kit.color_scheme.schemes', ColorSchemeStore::SCHEMES)));

        return $schemes === [] ? ['light'] : $schemes;
    }

    /** What a visitor with no stored choice gets. */
    public static function default(): string
    {
        $default = (string) config('tall-ui-kit.color_scheme.default', 'system');
        $schemes = self::schemes();

        return in_array($default, $schemes, true) ? $default : $schemes[0];
    }

    public static function persist(): string
    {
        return config('tall-ui-kit.color_scheme.persist') === 'server' ? 'server' : 'local';
    }

    /** Whether people get a choice at all (more than one scheme). */
    public static function switchable(): bool
    {
        return count(self::schemes()) > 1;
    }

    /** The server's stored choice when persist is 'server' and it is allowed, else null. */
    public static function stored(): ?string
    {
        if (self::persist() !== 'server' || ! self::switchable()) {
            return null;
        }

        $scheme = app(ColorSchemeStore::class)->get();

        return in_array($scheme, self::schemes(), true) ? $scheme : null;
    }

    public static function allows(string $scheme): bool
    {
        return in_array($scheme, self::schemes(), true);
    }
}
