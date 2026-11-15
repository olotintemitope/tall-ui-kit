<?php

namespace TallUiKit\Livewire\Concerns;

use Livewire\Attributes\Renderless;
use TallUiKit\Support\Dismiss\DismissStore;

/**
 * Server persistence for x-ui.banner persist="server" inside this component: the
 * banner's Dismiss button calls dismissNotice(key) through $wire, which writes to the
 * bound DismissStore. Renderless (no re-render; a few hundred bytes per round trip).
 *
 * The key arrives from the browser ("banner:<name>"), so it is checked against
 * KEY_PATTERN and, when the component lists them, against dismissibleKeys() (422
 * otherwise). Override canDismiss() to refuse a dismissal (403), e.g. a notice that
 * must stay until the user acts on it.
 */
trait WithDismissals
{
    #[Renderless]
    public function dismissNotice(string $key): void
    {
        $allowed = $this->dismissibleKeys();

        abort_unless(preg_match(DismissStore::KEY_PATTERN, $key) === 1 && ($allowed === null || in_array($key, $allowed, true)), 422);
        abort_unless($this->canDismiss($key), 403);

        app(DismissStore::class)->dismiss($key);
    }

    /** @return list<string>|null The keys this component renders ("banner:<name>"); null accepts any valid key. */
    protected function dismissibleKeys(): ?array
    {
        return null;
    }

    protected function canDismiss(string $key): bool
    {
        return true;
    }
}
