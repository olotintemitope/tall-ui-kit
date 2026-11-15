<?php

namespace TallUiKit\Support\Checklist;

use TallUiKit\Support\Dismiss\DismissStore;

/**
 * Where x-ui.checklist keeps its collapsed and dismissed state when it is rendered
 * with persist="server" (docs/components/feedback.md, "Checklist"). The default is
 * SessionChecklistStore (this browser session only), bound with bindIf by the kit's
 * service provider. An app binds its own store to keep the state per user, e.g. in a
 * preferences table:
 *
 *     $this->app->bind(ChecklistStore::class, UserPreferenceChecklistStore::class);
 *
 * Keys come from the browser through WithChecklist, which checks them against
 * KEY_PATTERN first; an implementation still scopes them to the current user.
 *
 * It extends DismissStore (dismissed, dismiss and KEY_PATTERN), so the same store keeps
 * x-ui.banner dismissals too unless the app binds DismissStore on its own.
 */
interface ChecklistStore extends DismissStore
{
    public function collapsed(string $key): bool;

    public function setCollapsed(string $key, bool $collapsed): void;
}
