<?php

namespace TallUiKit\Support\ColorScheme;

/**
 * Default ColorSchemeStore: the choice lives in the session, so it lasts for the
 * visit on any page and is gone after logout. Bind a per-user store for good.
 */
class SessionColorSchemeStore implements ColorSchemeStore
{
    private const KEY = 'tall-ui-kit.color_scheme';

    public function get(): ?string
    {
        $scheme = session(self::KEY);

        return in_array($scheme, self::SCHEMES, true) ? $scheme : null;
    }

    public function put(string $scheme): void
    {
        session([self::KEY => $scheme]);
    }
}
