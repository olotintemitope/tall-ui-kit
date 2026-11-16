<?php

namespace Workbench\App\Livewire\Community;

use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Workbench /community: a feed of x-ui.post items with likes (the second post refuses
 * the like, to show the rollback), reactions, a comment thread with replies and
 * "Show more replies". The poll is its own child (PollDemo), so each round trip
 * carries one demo only.
 */
class FeedDemo extends Component
{
    public const NOW = '2026-11-16 14:00:00';

    /** @var array<string, array{likes: int, liked: bool}> */
    public array $likes = ['p1' => ['likes' => 1240, 'liked' => false], 'p2' => ['likes' => 18, 'liked' => false]];

    /** @var list<array{key: string, count: int, mine: bool}> */
    public array $reactions = [['key' => 'like', 'count' => 12, 'mine' => false], ['key' => 'celebrate', 'count' => 4, 'mine' => true]];

    /** @var list<array<string, mixed>> */
    public array $comments = [];

    /** @var list<string> */
    public array $accepted = [];

    public function mount(): void
    {
        $this->comments = [
            ['key' => 'c1', 'author' => 'Funmi Lawal', 'time' => '2026-11-16 12:10:00', 'body' => 'Is the 3pm slot still open? Asking for the Lagos team.', 'more' => 3, 'replies' => [
                ['key' => 'c1-1', 'author' => 'Ada Obi', 'time' => '2026-11-16 12:20:00', 'body' => 'Yes, until Friday. Book it here: https://example.com/book', 'replies' => [
                    ['key' => 'c1-1-1', 'author' => 'Funmi Lawal', 'time' => '2026-11-16 12:25:00', 'body' => 'Booked, thanks @ada.', 'replies' => [
                        ['key' => 'c1-1-1-1', 'author' => 'Gbenga Musa', 'time' => '2026-11-16 12:40:00', 'body' => 'Same for us.'],
                    ]],
                ]],
            ]],
            ['key' => 'c2', 'author' => 'Gbenga Musa', 'time' => '2026-11-16 13:02:00', 'body' => "Great news.\nWill the recordings be shared after?", 'edited' => true],
        ];
    }

    public function like(string $key, bool $on): bool
    {
        abort_unless(isset($this->likes[$key]), 422);

        // The second post refuses every like, so the gallery can show the rollback.
        if ($key === 'p2') {
            return false;
        }

        if ($this->likes[$key]['liked'] !== $on) {
            $this->likes[$key] = ['likes' => $this->likes[$key]['likes'] + ($on ? 1 : -1), 'liked' => $on];
        }

        return true;
    }

    public function react(?string $subject, string $reaction): void
    {
        abort_unless(in_array($reaction, ['like', 'love', 'funny', 'wow', 'sad', 'celebrate'], true), 422);
        $list = collect($this->reactions)->keyBy('key');
        $current = $list->get($reaction, ['key' => $reaction, 'count' => 0, 'mine' => false]);
        $current['count'] += $current['mine'] ? -1 : 1;
        $current['mine'] = ! $current['mine'];
        $list->put($reaction, $current);
        $this->reactions = $list->values()->all();
    }

    public function reply(?string $parent, string $body, string $key): bool
    {
        $body = trim($body);
        abort_if($body === '' || mb_strlen($body) > 1000 || strlen($key) > 64, 422);
        if (in_array($key, $this->accepted, true)) {
            return true;
        }
        $this->accepted[] = $key;
        $comment = ['key' => 'n'.substr(md5($key), 0, 8), 'author' => 'You', 'time' => now()->toIso8601String(), 'body' => $body];

        if ($parent === null) {
            $this->comments[] = $comment;

            return true;
        }

        $this->comments = $this->insert($this->comments, $parent, $comment);

        return true;
    }

    public function moreReplies(string $key): void
    {
        $this->comments = array_map(function (array $c) use ($key) {
            if ($c['key'] === $key && ($c['more'] ?? 0) > 0) {
                foreach (range(1, $c['more']) as $i) {
                    $c['replies'][] = ['key' => $key.'-m'.$i, 'author' => ['Bola Ade', 'Chidi Eze', 'Dayo Bello'][$i % 3], 'time' => '2026-11-16 12:5'.$i.':00', 'body' => 'Reply '.$i.' that was not loaded at first.'];
                }
                $c['more'] = 0;
            }

            return $c;
        }, $this->comments);
    }

    /** @param  list<array<string, mixed>>  $list */
    private function insert(array $list, string $parent, array $comment): array
    {
        foreach ($list as $i => $c) {
            if ($c['key'] === $parent) {
                $list[$i]['replies'][] = $comment;
            } elseif (! empty($c['replies'])) {
                $list[$i]['replies'] = $this->insert($c['replies'], $parent, $comment);
            }
        }

        return $list;
    }

    public function render(): View
    {
        return view('livewire.community.feed-demo', ['now' => self::NOW]);
    }
}
