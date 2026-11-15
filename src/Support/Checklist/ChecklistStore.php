<?php

namespace TallUiKit\Support\Checklist;

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
 */
interface ChecklistStore
{
    /** Letters, digits, underscore, colon and hyphen, 1 to 64 characters. */
    public const KEY_PATTERN = '/^[A-Za-z0-9_:-]{1,64}$/';

    public function collapsed(string $key): bool;

    public function dismissed(string $key): bool;

    public function setCollapsed(string $key, bool $collapsed): void;

    public function dismiss(string $key): void;
}
