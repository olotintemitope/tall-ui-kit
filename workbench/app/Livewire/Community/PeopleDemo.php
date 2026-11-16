<?php

namespace Workbench\App\Livewire\Community;

use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Workbench /community: x-ui.profile-header and x-ui.member-card with the optimistic
 * Follow toggle, and x-ui.chat.inbox opening a conversation.
 */
class PeopleDemo extends Component
{
    /** @var array<string, bool> */
    public array $following = ['u-ada' => true, 'u-bola' => false, 'u-chidi' => false, 'u-efe' => false];

    public string $open = 'k1';

    /** @var array<string, int> */
    public array $unread = ['k1' => 0, 'k2' => 3, 'k3' => 0, 'k4' => 12];

    public function follow(string $key, bool $on): bool
    {
        abort_unless(array_key_exists($key, $this->following), 422);
        $this->following[$key] = $on;

        return true;
    }

    public function openConversation(string $key): void
    {
        abort_unless(array_key_exists($key, $this->unread), 422);
        $this->open = $key;
        $this->unread[$key] = 0;
    }

    public function render(): View
    {
        return view('livewire.community.people-demo', ['now' => '2026-11-16 14:00:00']);
    }
}
