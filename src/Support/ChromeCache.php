<?php

namespace TallUiKit\Support;

/**
 * Parts of a component the browser already holds (x-ui.file-manager's toolbar, bulk bar
 * and dialogs). resources/js/files.js lists their keys in the X-Tall-Ui-Chrome header of
 * each Livewire request; a listed part is sent as an empty wire:ignore placeholder, and
 * Livewire's morph keeps the original. fake() sets the list in tests.
 */
final class ChromeCache
{
    public const HEADER = 'X-Tall-Ui-Chrome';

    /** @var list<string>|null */
    private static ?array $fake = null;

    /** Does the browser already hold this part? */
    public static function has(string $key): bool
    {
        $sent = self::$fake ?? preg_split('/\s+/', (string) request()->header(self::HEADER, ''), -1, PREG_SPLIT_NO_EMPTY);

        return in_array($key, $sent, true);
    }

    /** @param list<string>|null $keys null goes back to the request header */
    public static function fake(?array $keys): void
    {
        self::$fake = $keys;
    }
}
