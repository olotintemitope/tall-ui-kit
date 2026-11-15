<?php

namespace TallUiKit\Support\ColorScheme;

/**
 * Where the kit keeps a person's colour scheme (light, dark or system) when
 * config('tall-ui-kit.color_scheme.persist') is 'server' (docs/theming.md "Dark mode").
 * The same store pattern as DismissStore: the kit binds SessionColorSchemeStore by
 * default; bind your own to keep the choice per user, e.g. on a preferences column:
 *
 *     $this->app->bind(ColorSchemeStore::class, UserColorSchemeStore::class);
 *
 * x-ui.head reads get() before first paint; x-ui.theme-toggle writes through
 * WithColorScheme::setColorScheme(), which checks the value against SCHEMES and the
 * configured schemes first. An implementation still scopes it to the current user.
 */
interface ColorSchemeStore
{
    public const SCHEMES = ['light', 'dark', 'system'];

    /** The stored scheme, or null when the person has not chosen one. */
    public function get(): ?string;

    public function put(string $scheme): void;
}
