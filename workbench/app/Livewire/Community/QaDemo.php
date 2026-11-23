<?php

namespace Workbench\App\Livewire\Community;

use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Workbench /community: a question with flat answers (x-ui.comment-thread with
 * nested false), likes on answers and an accepted answer. answer() is idempotent by
 * key, like() records the vote, accept() marks one answer.
 */
class QaDemo extends Component
{
    /** @var list<array<string, mixed>> */
    public array $answers = [
        ['key' => 'a1', 'author' => 'Bola Ade', 'time' => '2026-11-16 11:05:00', 'body' => 'Use the cash book first, then post to the ledger at month end.', 'likes' => 4, 'liked' => false, 'tag' => 'Instructor'],
        ['key' => 'a2', 'author' => 'Chidi Eze', 'time' => '2026-11-16 12:40:00', 'body' => 'The worked example in lesson 3 shows both columns side by side.', 'likes' => 1, 'liked' => false],
    ];

    /** @var list<string> */
    public array $sentKeys = [];

    public function answer(?string $parent, string $body, string $key): bool
    {
        $body = trim($body);
        abort_if($body === '' || mb_strlen($body) > 2000 || strlen($key) > 64, 422);
        if (in_array($key, $this->sentKeys, true)) {
            return true;
        }
        $this->sentKeys[] = $key;
        $this->answers[] = ['key' => 'n'.substr(md5($key), 0, 8), 'author' => 'You', 'time' => now()->toIso8601String(), 'body' => $body, 'likes' => 0, 'liked' => false];

        return true;
    }

    public function like(string $key, bool $on): bool
    {
        foreach ($this->answers as &$answer) {
            if ($answer['key'] === $key) {
                $answer['liked'] = $on;
                $answer['likes'] = max(0, $answer['likes'] + ($on ? 1 : -1));

                return true;
            }
        }

        return false;
    }

    public function accept(string $key): void
    {
        foreach ($this->answers as &$answer) {
            $answer['accepted'] = $answer['key'] === $key;
        }
    }

    public function render(): View
    {
        return view('livewire.community.qa-demo', ['now' => FeedDemo::NOW]);
    }
}
