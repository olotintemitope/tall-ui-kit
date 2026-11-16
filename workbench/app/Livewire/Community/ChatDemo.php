<?php

namespace Workbench\App\Livewire\Community;

use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Workbench /chat: x-ui.chat on neutral sample data. send() is idempotent (a key it has
 * seen is accepted again without a second message), loadOlder() puts earlier messages
 * above, simulateReply() adds a message from someone else (to see the "new messages"
 * button), retry() resends the failed message and react() toggles a reaction.
 */
class ChatDemo extends Component
{
    public const NOW = '2026-11-16 14:00:00';

    /** @var list<array<string, mixed>> */
    public array $messages = [];

    /** @var list<string> idempotency keys already accepted */
    public array $accepted = [];

    public bool $hasOlder = true;

    public int $replies = 0;

    public function mount(): void
    {
        $this->messages = [
            ['key' => 'm1', 'author' => 'Ada Obi', 'time' => '2026-11-15 09:12:00', 'body' => "Morning. The October statement is ready, I've put it in the shared folder."],
            ['key' => 'm2', 'author' => 'Ada Obi', 'time' => '2026-11-15 09:13:00', 'body' => 'Totals match the bank this time.', 'reactions' => [['key' => 'like', 'count' => 2, 'mine' => true]]],
            ['key' => 'm3', 'mine' => true, 'time' => '2026-11-15 09:40:00', 'body' => 'Thanks. I will check the payouts section before lunch.'],
            ['key' => 'm4', 'author' => 'Bola Ade', 'time' => '2026-11-16 08:05:00', 'body' => "Two things for today:\n1. The vendor list for the December campaign\n2. Sign-off on the new rates\nDetails: https://example.com/notes/rates-2026?team=ops&v=2", 'attachments' => [
                ['type' => 'file', 'name' => 'vendor-list-december-2026-final.xlsx', 'href' => '#vendor-list', 'meta' => '48 KB'],
            ]],
            ['key' => 'm5', 'author' => 'Bola Ade', 'time' => '2026-11-16 08:06:00', 'body' => 'Here is the stage plan from the venue.', 'attachments' => [
                ['type' => 'image', 'name' => 'stage-plan.svg', 'src' => self::image('Stage plan'), 'alt' => 'Stage plan with two exits marked', 'width' => 240, 'height' => 160],
            ]],
            ['key' => 'm6', 'mine' => true, 'time' => '2026-11-16 09:30:00', 'body' => 'Looks good. @bola can you confirm the load-in time? #december', 'edited' => true],
            ['key' => 'm7', 'author' => 'Chidi Eze', 'time' => '2026-11-16 10:02:00', 'deleted' => true],
            ['key' => 'm8', 'mine' => true, 'time' => '2026-11-16 13:10:00', 'body' => 'Sending the rates sheet now.', 'status' => 'failed'],
            ['key' => 'm9', 'mine' => true, 'time' => '2026-11-16 13:12:00', 'body' => 'Rates are in the folder too.', 'seen' => 'Seen by Bola'],
        ];
    }

    /** A small inline picture so the gallery needs no image files. */
    public static function image(string $label): string
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="480" height="320" viewBox="0 0 480 320"><rect width="480" height="320" fill="#EDEDEE"/><rect x="40" y="40" width="400" height="160" fill="none" stroke="#1F2021" stroke-width="2"/><rect x="40" y="230" width="120" height="50" fill="none" stroke="#1F2021" stroke-width="2"/><rect x="320" y="230" width="120" height="50" fill="none" stroke="#1F2021" stroke-width="2"/><text x="240" y="128" font-family="sans-serif" font-size="22" text-anchor="middle" fill="#1F2021">'.e($label).'</text></svg>';

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    public function send(string $body, string $key): bool
    {
        $body = trim($body);
        abort_if($body === '' || mb_strlen($body) > 2000 || strlen($key) > 64, 422);

        // The same draft sent twice (a double click, a retry after a timeout): accept it once.
        if (in_array($key, $this->accepted, true)) {
            return true;
        }

        $this->accepted[] = $key;
        $this->messages[] = ['key' => 'k'.substr(md5($key), 0, 10), 'mine' => true, 'time' => now()->toIso8601String(), 'body' => $body];

        return true;
    }

    public function loadOlder(): void
    {
        if (! $this->hasOlder) {
            return;
        }

        $older = [];
        foreach (range(1, 8) as $i) {
            $older[] = ['key' => 'o'.$i, 'author' => $i % 3 === 0 ? null : 'Ada Obi', 'mine' => $i % 3 === 0, 'time' => '2026-11-12 1'.min(9, $i).':0'.$i.':00', 'body' => 'Earlier message '.$i.': notes from the planning call.'];
        }
        $this->messages = array_merge($older, $this->messages);
        $this->hasOlder = false;
    }

    public function simulateReply(): void
    {
        $this->replies++;
        $lines = ['Load-in is at 3pm.', 'The crew arrives an hour before.', 'Parking is behind the hall.'];
        $this->messages[] = ['key' => 'r'.$this->replies, 'author' => 'Bola Ade', 'time' => now()->toIso8601String(), 'body' => $lines[($this->replies - 1) % 3]];
    }

    public function retry(string $key): void
    {
        foreach ($this->messages as $i => $message) {
            if (($message['key'] ?? null) === $key) {
                $this->messages[$i]['status'] = 'sent';
            }
        }
    }

    public function react(string $subject, string $reaction): void
    {
        abort_unless(in_array($reaction, ['like', 'love', 'funny', 'wow', 'sad', 'celebrate'], true), 422);

        foreach ($this->messages as $i => $message) {
            if (($message['key'] ?? null) !== $subject) {
                continue;
            }
            $list = collect($message['reactions'] ?? [])->keyBy('key');
            $current = $list->get($reaction, ['key' => $reaction, 'count' => 0, 'mine' => false]);
            $current['count'] += $current['mine'] ? -1 : 1;
            $current['mine'] = ! $current['mine'];
            $list->put($reaction, $current);
            $this->messages[$i]['reactions'] = $list->values()->all();
        }
    }

    public function render(): View
    {
        return view('livewire.community.chat-demo', ['now' => self::NOW]);
    }
}
