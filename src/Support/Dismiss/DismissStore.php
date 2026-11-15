<?php

namespace TallUiKit\Support\Dismiss;

/**
 * Where the kit keeps "dismissed" flags for anything the user can put away for good:
 * x-ui.banner with persist="server" (docs/components/workflow.md, "Banner") and, through
 * ChecklistStore (which extends this contract), x-ui.checklist.
 *
 * By default the kit resolves DismissStore to the bound ChecklistStore, so an app that
 * already binds its own per-user ChecklistStore keeps banners per user too, with no new
 * binding. Bind DismissStore directly to store them somewhere else:
 *
 *     $this->app->bind(DismissStore::class, UserPreferenceDismissStore::class);
 *
 * Keys come from the browser through WithDismissals (or WithChecklist), which check them
 * against KEY_PATTERN first; an implementation still scopes them to the current user.
 */
interface DismissStore
{
    /** Letters, digits, underscore, colon and hyphen, 1 to 64 characters. */
    public const KEY_PATTERN = '/^[A-Za-z0-9_:-]{1,64}$/';

    public function dismissed(string $key): bool;

    public function dismiss(string $key): void;
}
