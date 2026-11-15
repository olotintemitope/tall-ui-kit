<?php

namespace TallUiKit\Livewire\Concerns;

use Livewire\Attributes\Renderless;
use TallUiKit\Support\Checklist\ChecklistStore;

/**
 * Server persistence for x-ui.checklist persist="server" inside this component: the
 * checklist's Collapse and Dismiss buttons call these methods through $wire, and they
 * write to the bound ChecklistStore. Both are renderless (no re-render, a few hundred
 * bytes per round trip).
 *
 * The key arrives from the browser, so it is checked against KEY_PATTERN and, when the
 * component lists them, against checklistKeys(). Override checklistCanDismiss() to
 * refuse a dismissal the server does not agree with (e.g. a required step not done).
 */
trait WithChecklist
{
    #[Renderless]
    public function checklistCollapse(string $key, bool $collapsed): void
    {
        app(ChecklistStore::class)->setCollapsed($this->checklistKey($key), $collapsed);
    }

    #[Renderless]
    public function checklistDismiss(string $key): void
    {
        $key = $this->checklistKey($key);
        abort_unless($this->checklistCanDismiss($key), 403);

        app(ChecklistStore::class)->dismiss($key);
    }

    /** @return list<string>|null The keys this component renders; null accepts any valid key. */
    protected function checklistKeys(): ?array
    {
        return null;
    }

    protected function checklistCanDismiss(string $key): bool
    {
        return true;
    }

    private function checklistKey(string $key): string
    {
        $allowed = $this->checklistKeys();

        abort_unless(preg_match(ChecklistStore::KEY_PATTERN, $key) === 1 && ($allowed === null || in_array($key, $allowed, true)), 422);

        return $key;
    }
}
