<?php

namespace TallUiKit\Support\Checklist;

use Illuminate\Contracts\Session\Session;

/**
 * The default ChecklistStore: collapsed and dismissed flags in the session, so they
 * last for the browser session and need no table. Bind your own store to keep them
 * per user across devices.
 */
class SessionChecklistStore implements ChecklistStore
{
    private const PREFIX = 'tall_ui_kit_checklist';

    public function __construct(private readonly Session $session) {}

    public function collapsed(string $key): bool
    {
        return (bool) $this->session->get($this->path($key, 'collapsed'), false);
    }

    public function dismissed(string $key): bool
    {
        return (bool) $this->session->get($this->path($key, 'dismissed'), false);
    }

    public function setCollapsed(string $key, bool $collapsed): void
    {
        $this->session->put($this->path($key, 'collapsed'), $collapsed);
    }

    public function dismiss(string $key): void
    {
        $this->session->put($this->path($key, 'dismissed'), true);
    }

    /** KEY_PATTERN has no dots, so a key never reaches into another session entry. */
    private function path(string $key, string $flag): string
    {
        return self::PREFIX.'.'.$key.'.'.$flag;
    }
}
