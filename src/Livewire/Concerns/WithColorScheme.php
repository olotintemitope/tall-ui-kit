<?php

namespace TallUiKit\Livewire\Concerns;

use Livewire\Attributes\Renderless;
use TallUiKit\Support\ColorScheme\ColorScheme;
use TallUiKit\Support\ColorScheme\ColorSchemeStore;

/**
 * Server persistence for x-ui.theme-toggle when config('tall-ui-kit.color_scheme.persist')
 * is 'server': the toggle calls setColorScheme(scheme) through $wire, which writes to
 * the bound ColorSchemeStore, and x-ui.head prints the stored value on the next page.
 * Renderless (no re-render). The value arrives from the browser, so it must be one of
 * the configured schemes (422 otherwise). Outside a component with this trait the
 * toggle keeps the choice in localStorage only.
 */
trait WithColorScheme
{
    #[Renderless]
    public function setColorScheme(string $scheme): void
    {
        abort_unless(ColorScheme::allows($scheme), 422);

        app(ColorSchemeStore::class)->put($scheme);
    }
}
