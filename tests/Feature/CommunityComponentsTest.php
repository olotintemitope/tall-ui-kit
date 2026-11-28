<?php

use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use TallUiKit\Support\KitContrast;
use TallUiKit\Support\RichText;
use Workbench\App\Livewire\Community\ChatDemo;
use Workbench\App\Livewire\Community\FeedDemo;
use Workbench\App\Livewire\Community\QaDemo;
use Workbench\App\Livewire\Community\PeopleDemo;
use Workbench\App\Livewire\Community\PollDemo;

/*
| Chat and community components (docs/components/community.md): markup, accessibility
| attributes, escaping of user text, the demo endpoints (idempotent send, rollback,
| votes) and the Livewire payload budget.
*/

function cm(string $blade, array $data = []): string
{
    return Blade::render($blade, $data);
}

describe('rich text', function () {
    it('escapes everything, then links urls, mentions and hashtags', function () {
        $html = (string) RichText::render('<img src=x onerror=alert(1)> see https://ex.com/a?b=1&c=2. @ada #launch www.ex.org', '/u/:name', '/t/:tag');

        expect($html)->not->toContain('<img')
            ->toContain('&lt;img src=x onerror=alert(1)&gt;')
            ->toContain('<a class="ui-rich-link" href="https://ex.com/a?b=1&amp;c=2" rel="nofollow ugc noopener">https://ex.com/a?b=1&amp;c=2</a>')
            ->toContain('<a class="ui-rich-mention" href="/u/ada" rel="nofollow ugc noopener">@ada</a>')
            ->toContain('<a class="ui-rich-tag" href="/t/launch" rel="nofollow ugc noopener">#launch</a>')
            ->toContain('href="https://www.ex.org"');
    });

    it('renders safe Markdown blocks and inline formatting', function () {
        $html = (string) RichText::render("# Heading\n\n**bold** *italic* ~~removed~~\n\n- first\n- second\n\n> quoted\n\n[unsafe](javascript:alert(1)) <b>raw</b>");

        expect($html)->toContain('<h1>Heading</h1>')->toContain('<strong>bold</strong>')->toContain('<em>italic</em>')
            ->toContain('<del>removed</del>')->toContain('<ul>')
            ->toContain('<blockquote>')->toContain('<span class="ui-rich-link">unsafe</span>')
            ->toContain('&lt;b&gt;raw&lt;/b&gt;')->not->toContain('<b>raw</b>')
            ->not->toContain('href="javascript:');
    });

    it('never links javascript urls, attribute breakouts or e-mail addresses', function () {
        $html = (string) RichText::render('javascript:alert(1) https://e.com/"onmouseover=x ada@example.com');

        expect($html)->not->toContain('href="javascript')
            ->toContain('href="https://e.com/"')
            ->toContain('&quot;onmouseover=x')
            ->not->toContain('ui-rich-mention');
    });
});

describe('chat', function () {
    $messages = [
        ['key' => 'a', 'author' => 'Ada Obi', 'time' => '2026-11-15 09:00:00', 'body' => "Line one\nLine two <b>bold</b>"],
        ['key' => 'b', 'author' => 'Ada Obi', 'time' => '2026-11-15 09:02:00', 'body' => 'Grouped'],
        ['key' => 'c', 'mine' => true, 'time' => '2026-11-16 10:00:00', 'body' => 'Mine', 'seen' => true],
        ['key' => 'd', 'mine' => true, 'time' => '2026-11-16 10:01:00', 'body' => 'Failed', 'status' => 'failed'],
        ['key' => 'e', 'author' => 'Bola Ade', 'time' => '2026-11-16 10:05:00', 'deleted' => true],
    ];

    it('is a non-live log with a separate polite announcer, days and grouping', function () use ($messages) {
        $html = cm('<x-ui.chat label="Chat with Ada" :messages="$messages" now="2026-11-16 12:00:00" send-action="send" older-action="older" has-older />', compact('messages'));

        expect($html)->toContain('role="log"')->toContain('aria-live="off"')->toContain('aria-label="Chat with Ada"')
            ->toContain('role="status" aria-live="polite" aria-atomic="true"')
            ->toContain('>Yesterday</h3>')->toContain('>Today</h3>')
            ->toContain('Load older messages')
            ->and(substr_count($html, 'data-chat-message '))->toBe(5)
            ->and($html)->toContain('<span class="sr-only">Ada Obi')
            ->toContain('Line one<br />'."\n".'Line two &lt;b&gt;bold&lt;/b&gt;')
            ->not->toContain('<b>bold</b>')
            ->toContain('This message was deleted.')
            ->toContain('Not sent.')->toContain('Retry')
            ->toContain('data-chat-seen>Seen<')
            ->toContain('x-data="uiComposer(');
    });

    it('renders Markdown in each chat message bubble', function () {
        $html = cm('<x-ui.chat.message key="m" body="**bold**" />');

        expect($html)->toContain('ui-chat-bubble')->toContain('<strong>bold</strong>');
    });

    it('passes custom emoji reaction options to the reaction widget', function () {
        $html = cm('<x-ui.chat.message key="m" :reactions="$reactions" :reaction-options="$options" />', ['reactions' => [['key' => '🔥', 'count' => 2]], 'options' => ['🔥' => ['🔥', 'Fire']]]);

        expect($html)->toContain('🔥')->toContain('Fire');
    });


    it('marks the sending, edited and failed states in words', function () {
        $html = cm('<x-ui.chat.message key="x" mine body="Hi" status="sending" /><x-ui.chat.message key="y" mine body="Hi" edited /><x-ui.chat.message key="z" mine body="Hi" status="failed" retry-action="retry" />');

        expect($html)->toContain('data-chat-state="sending">Sending<')
            ->toContain('data-chat-state="edited">Edited<')
            ->toContain('wire:click="retry(\'z\')"');
    });

    it('shows image attachments as buttons for the shared dialog and files as rows', function () {
        $html = cm('<x-ui.chat label="Files" :messages="[[\'key\' => \'i\', \'author\' => \'Ada\', \'time\' => \'2026-11-16 10:00:00\', \'attachments\' => [[\'type\' => \'image\', \'src\' => \'/a.png\', \'alt\' => \'A chart\'], [\'type\' => \'file\', \'name\' => \'a.pdf\', \'href\' => \'/a.pdf\', \'meta\' => \'2 KB\']]]]" />');

        expect($html)->toContain('aria-label="View image: A chart"')->toContain('data-chat-image')
            ->toContain('data-chat-image-dialog')->toContain('href="/a.pdf"')->toContain('2 KB');
    });

    it('says who is typing once, in a status region with hidden dots', function () {
        expect(cm('<x-ui.chat.typing :names="[\'Ada\', \'Bola\']" />'))->toContain('role="status"')->toContain('Ada and Bola are typing')->toContain('ui-typing-dots inline-flex items-center gap-0.5" aria-hidden="true"')
            ->and(cm('<x-ui.chat.typing :names="[\'A\', \'B\', \'C\']" />'))->toContain('Several people are typing')
            ->and(cm('<x-ui.chat.typing />'))->toContain('role="status"')->not->toContain('ui-typing-dots');
    });

    it('builds a composer with a label, hint, counter and the control scale', function () {
        $html = cm('<x-ui.chat.composer label="Reply" action="send" :maxlength="280" attach="files" size="sm" id="c1" />');

        expect($html)->toContain('<label for="c1-input" class="sr-only">Reply</label>')
            ->toContain('maxlength="280"')->toContain('aria-describedby="c1-hint  c1-count "')
            ->toContain('Enter to send, Shift+Enter for a new line.')
            ->toContain('0 / 280')->toContain('min-h-8')
            ->toContain('aria-label="Attach files"')->toContain('wire:model="files"')
            ->toContain('data-upload-list="files"')
            ->and(cm('<x-ui.chat.composer disabled disabled-reason="Closed." />'))->toContain('disabled')->toContain('Closed.');
    });

    it('turns the composer into a mention combobox only when people can be mentioned', function () {
        $html = cm('<x-ui.chat.composer id="c2" mentions-label="Members" :mentions="[[\'key\' => \'u1\', \'name\' => \'Ada Obi\'], [\'key\' => \'u2\', \'name\' => \'\']]" typing-action="typing" />');

        expect($html)->toContain('role="combobox"')->toContain('aria-autocomplete="list"')
            ->toContain('aria-controls="c2-mentions"')->toContain('id="c2-mentions"')
            ->toContain('role="listbox"')->toContain('aria-label="Members"')
            ->toContain('placeMentions($el)')->toContain('data-composer-mention-status')
            ->toContain('Ada Obi')->toContain('typing')
            ->and(cm('<x-ui.chat.composer id="c3" />'))->not->toContain('role="combobox"')->not->toContain('data-composer-mentions');
    });

    it('drops people without a name from the mention list', function () {
        $html = cm('<x-ui.chat.composer id="c4" :mentions="[[\'key\' => \'u2\', \'name\' => \'\']]" />');

        expect($html)->not->toContain('role="combobox"');
    });

    it('passes avatars and turns mentions on for a server search alone', function () {
        $html = cm('<x-ui.chat.composer id="c6" :mentions="[[\'key\' => \'u1\', \'name\' => \'Ada Obi\', \'avatar\' => \'/a.png\']]" />');

        expect($html)->toContain('/a.png')->toContain('x-bind:src="person.avatar"')->toContain('person.name.slice(0, 1)');

        $search = cm('<x-ui.chat.composer id="c7" mention-search="findPeople" />');

        expect($search)->toContain('role="combobox"')->toContain('findPeople');
    });

    it('sends the picked mentions after the key and gives tools an insert hook', function () {
        $js = file_get_contents(kit_path('resources/js/chat.js'));

        expect($js)->toContain('[...params, body, key, mentions]')
            ->toContain('this.body.includes(`@${person.name}`)')
            ->toContain('options.mentionSearch')->toContain('this.searched.has(query)')
            ->toContain('insert(text)')->toContain('startMention()')
            ->toContain('this.$dispatch(\'ui-chat-send\', { body, key, params, mentions })');
    });

    it('opens the mention list below, and above only when docked in a clipped chat', function () {
        $html = cm('<x-ui.chat.composer id="c8" :mentions="[[\'key\' => \'u1\', \'name\' => \'Ada Obi\']]" />');
        $js = file_get_contents(kit_path('resources/js/chat.js'));

        expect($html)->toContain('placeMentions($el)')->toContain("mentionAbove ? 'bottom-full mb-1.5' : 'top-full mt-1.5'")
            ->not->toMatch('/class="[^"]*\\babsolute inset-x-0 top-full/');
        // Below with the page scrolled to show it, unless a clipping container has no room.
        expect($js)->toContain('const above = clip !== null && room < list.offsetHeight + 8;')
            ->toContain('if (!above) this.$nextTick(() => this.$revealBelow(list, this.$refs.input));');
        expect(file_get_contents(kit_path('docs/rules.md')))->toContain('`x-ui.chat.composer` mention list opens above the box');
    });

    it('gives the text box its own row when tools would squeeze it on a narrow composer', function () {
        $with = cm('<x-ui.chat.composer id="c9"><x-slot:tools><x-ui.button variant="ghost" size="icon" aria-label="Mention someone"><x-ui.icon name="at-sign" /></x-ui.button></x-slot:tools></x-ui.chat.composer>');
        $without = cm('<x-ui.chat.composer id="c10" />');

        expect($with)->toContain('ui-composer @container')->toContain('flex items-end gap-2 flex-wrap')
            ->toContain('@max-md:order-first @max-md:basis-full')->toContain('@max-md:ms-auto');
        expect($without)->not->toContain('@max-md:order-first')->not->toContain('flex-wrap" data-composer-row');
    });

    it('starts from a kept draft, forwards removeAction to the upload list and clears it after a send', function () {
        $html = cm('<x-ui.chat.composer id="c11" attach="files" multiple remove-action="dropFile" draft="Half-written note" />');
        $js = file_get_contents(kit_path('resources/js/chat.js'));

        expect($html)->toContain('Half-written note')->toContain('dropFile');
        expect($js)->toContain("body: typeof options.draft === 'string' ? options.draft : ''")
            ->toContain('this.$root.querySelectorAll(\'[data-upload-list]\')');
    });

    it('draws flat answers with likes and an accepted answer for questions and answers', function () {
        $comments = [
            ['key' => 'a1', 'author' => 'Bola Ade', 'body' => 'Cash book first.', 'likes' => 4, 'liked' => true, 'tag' => 'Instructor'],
            ['key' => 'a2', 'author' => 'Chidi Eze', 'body' => 'See lesson 3.', 'likes' => 1, 'accepted' => true, 'replies' => [['key' => 'a2-1', 'author' => 'Ada', 'body' => 'Thanks']]],
        ];
        $html = Blade::render('<x-ui.comment-thread id="qa" :comments="$c" :nested="false" reply-action="answer" like-action="vote" like-label="Helpful" like-icon="thumbs-up" accept-action="accept" add-label="Your answer" add-placeholder="Write an answer" add-submit-label="Post answer" />', ['c' => $comments]);

        expect($html)->not->toContain('data-reply-template')->not->toMatch('/>Reply</')
            ->toContain('data-like-action="vote"')->toContain('aria-pressed="true" data-post-like data-count="4"')->toContain('Helpful')
            ->toContain('Accepted answer')->toMatch('/wire:click="accept\\([^)]*a1[^)]*\\)"/')->not->toMatch('/accept\\([^)]*a2(?!-)[^)]*\\)/')
            ->toContain('Your answer')->toContain('Write an answer')->toContain('Post answer')->toContain('Instructor');

        $plain = Blade::render('<x-ui.comment-thread id="plain" :comments="$c" reply-action="reply" />', ['c' => $comments]);
        expect($plain)->toContain('data-reply-template')->not->toContain('data-post-like')->not->toContain('Mark as answer')->toContain('Add a comment');
    });

    it('titles a post for a thread or question, with a status, a role and renamed actions', function () {
        $html = Blade::render('<x-ui.post key="q1" author="Ada Obi" author-badge="Instructor" title="When do I post to the ledger?" status="Solved" title-href="/q/1" body="Which lesson?" like-label="Upvote" like-icon="arrow-big-up" comment-label="Answers" :likes="3" :comments="2" />');

        expect($html)->toContain('aria-labelledby="post-q1-title"')->toMatch('/<h3 id="post-q1-title"[^>]*><a href="\/q\/1"/')
            ->toContain('When do I post to the ledger?')->toContain('Solved')->toContain('Instructor')
            ->toContain('<p id="post-q1-author"')->toContain('Upvote')->toContain('Answers')->not->toMatch('/<span>Like<\/span>/');

        $plain = Blade::render('<x-ui.post key="p9" author="Bola" body="Hello" />');
        expect($plain)->toContain('aria-labelledby="post-p9-author"')->toContain('<h3 id="post-p9-author"')->toContain('<span>Like</span>')->not->toContain('post-p9-title');
    });

    it('likes from a comment through the post like handler', function () {
        expect(file_get_contents(kit_path('resources/js/community-core.js')))->toContain("button.closest('[data-comment], [data-post]')");
    });

    it('quotes the message a reply answers and offers Reply on each message', function () {
        $html = cm('<x-ui.chat id="r1" reply-action="reply" reply-label="Answer" :messages="[[\'key\' => \'a\', \'author\' => \'Ada\', \'body\' => \'Hi\'], [\'key\' => \'b\', \'mine\' => true, \'body\' => \'Hello\', \'replyTo\' => [\'author\' => \'Ada\', \'body\' => \'Hi <b>there</b>\']]]" />');

        expect($html)->toContain('data-chat-reply-to')->toContain('Hi &lt;b&gt;there&lt;/b&gt;')
            ->toContain('data-chat-reply')->toContain('>Answer<')->toContain('\u0022replyAction\u0022:\u0022reply\u0022');
        expect(file_get_contents(kit_path('resources/js/chat-core.js')))->toContain("component.\$wire.call(options.replyAction, key)")->toContain("'ui-chat-reply'");
        expect(cm('<x-ui.chat id="r2" :messages="[[\'key\' => \'a\', \'body\' => \'Hi\']]" />'))->not->toContain('data-chat-reply');
    });

    it('keeps a row per file for one-at-a-time uploads with a removeAction', function () {
        $one = cm('<x-ui.chat.composer id="c12" attach="upload" :multiple="false" remove-action="dropFile" />');

        expect($one)->not->toMatch('/<input[^>]*data-composer-files[^>]*\\smultiple/')
            ->toContain('\u0022multiple\u0022:true')->toContain('dropFile');
        expect(cm('<x-ui.chat.composer id="c13" attach="upload" :multiple="false" />'))->toContain('\u0022multiple\u0022:false');
    });

    it('takes tools beside Attach and a context line above the box', function () {
        $html = cm('<x-ui.chat.composer id="c5" attach="files"><x-slot:context>Replying to Ada</x-slot:context><x-slot:tools><x-ui.button variant="ghost" size="icon" aria-label="Create a poll"><x-ui.icon name="bar-chart-3" /></x-ui.button></x-slot:tools></x-ui.chat.composer>');

        expect($html)->toContain('data-composer-context')->toContain('Replying to Ada')
            ->toContain('data-composer-tools')->toContain('aria-label="Create a poll"');
        expect(strpos($html, 'data-composer-attach'))->toBeLessThan(strpos($html, 'data-composer-tools'));
        expect(strpos($html, 'data-composer-tools'))->toBeLessThan(strpos($html, 'data-composer-input'));
    });

    it('runs the mention script tests: finding, matching and inserting', function () {
        if (! Illuminate\Support\Facades\Process::run('node --version')->successful()) {
            $this->markTestSkipped('Node is not installed.');
        }

        $result = Illuminate\Support\Facades\Process::path(kit_path())->run('node --test tests/js/mention-core.test.mjs');

        expect($result->exitCode())->toBe(0, $result->output().$result->errorOutput())
            ->and($result->output())->toContain('fail 0');
    });

    it('lists conversations with one tab stop, aria-current and spoken unread counts', function () {
        $html = cm('<x-ui.chat.inbox :conversations="[[\'key\' => \'a\', \'name\' => \'Ada\', \'preview\' => \'Hi\', \'active\' => true, \'href\' => \'/a\'], [\'key\' => \'b\', \'name\' => \'Crew\', \'people\' => [\'A\', \'B\', \'C\'], \'unread\' => 3, \'href\' => \'/b\']]" />');

        expect($html)->toContain('<nav')->toContain('aria-current="true"')
            ->and(substr_count($html, 'tabindex="0"'))->toBe(1)
            ->and($html)->toContain('<span class="sr-only"> unread</span>')
            ->toContain('data-avatar-group')
            ->not->toContain('truncate')->not->toContain('line-clamp');
    });
});

describe('community', function () {
    it('renders a post as an article with a safe body, compact counts and a named like toggle', function () {
        $body = "<script>x</script>\n\n@bola #tag https://ex.com";
        $html = cm('<x-ui.post key="p1" author="Ada Obi" verified :body="$body" :likes="1240" liked :comments="3" like-action="like" share-url="https://ex.com/p1"><x-slot:more><x-ui.dropdown.item>Report</x-ui.dropdown.item></x-slot:more></x-ui.post>', compact('body'));

        expect($html)->toContain('<article')->toContain('aria-labelledby="post-p1-author"')
            ->toContain('Verified')->not->toContain('<script>x')->toContain('&lt;script&gt;x&lt;/script&gt;')
            ->toContain('class="ui-rich-mention">@bola')->toContain('href="https://ex.com"')
            ->toContain('aria-pressed="true" data-post-like data-count="1240"')
            ->toContain('>1.2k<')->toContain('1,240 likes')
            ->toContain('data-like-action="like"')->toContain('data-post-share')
            ->toContain('aria-label="More actions for the post by Ada Obi"');
    });

    it('renders Markdown in posts and community comments', function () {
        $post = cm('<x-ui.post key="p1" author="Ada" body="**bold**" />');
        $comments = [[
            'key' => 'c1', 'author' => 'Ada', 'body' => '**bold**',
        ]];
        $thread = cm('<x-ui.comment-thread :comments="$comments" />', compact('comments'));

        expect($post)->toContain('data-post-body')->toContain('<strong>bold</strong>')
            ->and($thread)->toContain('data-comment-body')->toContain('<strong>bold</strong>');
    });

    it('renders reactions as toggles with labels and a picker below its button', function () {
        $html = cm('<x-ui.reactions subject="p1" action="react" :reactions="[[\'key\' => \'like\', \'count\' => 3, \'mine\' => true], [\'key\' => \'sad\', \'count\' => 0]]" />');

        expect($html)->toContain('role="group"')->toContain('aria-label="Reactions"')
            ->toContain('aria-pressed="true" data-reaction="like"')
            ->toContain('<span class="sr-only">Like</span>')
            ->not->toContain('data-reaction="sad"')
            ->toContain('popovertarget="')->toContain('aria-label="Add reaction"')
            ->toContain('aria-label="Choose a reaction"')
            ->and(substr_count($html, 'data-reaction-pick='))->toBe(6);
    });

    it('nests comments to the depth limit with reply, collapse and continue links', function () {
        $comments = [['key' => 'c1', 'author' => 'Ada', 'body' => '<i>x</i>', 'more' => 2, 'replies' => [
            ['key' => 'c2', 'author' => 'Bola', 'body' => 'Two', 'replies' => [['key' => 'c3', 'author' => 'Chidi', 'body' => 'Three']]],
        ]]];
        $html = cm('<x-ui.comment-thread :comments="$comments" :depth="2" reply-action="reply" more-action="more" continue-href="/t/:key" />', compact('comments'));

        expect($html)->toContain('x-data="uiThread"')->toContain('aria-label="Comments"')
            ->toContain('&lt;i&gt;x&lt;/i&gt;')->toContain('Reply<span class="sr-only"> to Ada</span>')
            ->toContain('aria-controls="')->toContain('Hide replies')
            ->toContain('Continue thread (1 reply)')->toContain('href="/t/c2"')
            ->not->toContain('Three')
            ->toContain('Show 2 more replies')->toContain('wire:click="more(\'c1\')"')
            ->toContain('Add a comment');
    });

    it('shows a poll form before voting and progress results after, with the viewer\'s vote in words', function () {
        $options = [['key' => 'a', 'label' => 'Nine', 'votes' => 3], ['key' => 'b', 'label' => 'One', 'votes' => 1]];
        $open = cm('<x-ui.poll key="p" question="When?" :options="$options" action="vote" />', compact('options'));
        $voted = cm('<x-ui.poll key="p" question="When?" :options="$options" :voted="[\'a\']" />', compact('options'));
        $ended = cm('<x-ui.poll key="p" question="When?" :options="$options" closed />', compact('options'));

        expect($open)->toContain('type="radio"')->toContain('<legend class="sr-only">When?</legend>')->toContain('data-poll-submit')->toContain('role="alert"')->not->toContain('<progress')
            ->and($voted)->toContain('<progress')->toContain('Nine (your vote)')->toContain('aria-valuetext="75%, 3 votes"')->toContain('4 votes')
            ->and($ended)->toContain('Poll ended')
            ->and(cm('<x-ui.poll key="m" question="Q" multiple :options="$options" />', compact('options')))->toContain('type="checkbox"')->toContain('Choose any');
    });

    it('renders people with stats and a follow toggle whose text gives the state', function () {
        $card = cm('<x-ui.member-card key="u1" name="Ada Obi" role="Lead" following follow-action="follow" message-href="/m" :stats="[[\'label\' => \'Followers\', \'value\' => 12480]]" />');
        $header = cm('<x-ui.profile-header key="u1" name="Ada Obi" verified :stats="[[\'label\' => \'Followers\', \'value\' => 12480]]" />');

        expect($card)->toContain('<article')->toContain('data-follow-action="follow"')->toContain('aria-pressed="true"')
            ->toContain('>Following<')->toContain('<span class="sr-only"> Ada Obi</span>')->toContain('12.5k')->toContain('data-stat-strip')
            ->and($header)->toContain('<h1 class="type-title')->toContain('size-12')->toContain('Verified')->toContain('12.5k');
    });

    it('lists channels with collapsible sections, unread and mentions in words', function () {
        $html = cm('<x-ui.channel-list :sections="[[\'key\' => \'s\', \'label\' => \'Channels\', \'channels\' => [[\'key\' => \'g\', \'name\' => \'general\', \'href\' => \'/g\', \'unread\' => true, \'active\' => true], [\'key\' => \'f\', \'name\' => \'finance\', \'href\' => \'/f\', \'mentions\' => 2, \'private\' => true]]]]" />');

        expect($html)->toContain('<nav')->toContain('aria-label="Channels"')->toContain('aria-expanded="true"')
            ->toContain('aria-current="page"')->toContain('<span class="sr-only">, unread</span>')
            ->toContain('<span class="sr-only"> mentions</span>')->toContain('<span class="sr-only">Private: </span>');
    });

    it('ranks rows with rank changes in words and highlights the viewer', function () {
        $html = cm('<x-ui.leaderboard value-label="streams" :rows="[[\'label\' => \'A\', \'value\' => 1200, \'change\' => 2], [\'label\' => \'B\', \'value\' => 900, \'change\' => -1, \'current\' => true], [\'label\' => \'C\', \'value\' => 5, \'new\' => true], [\'label\' => \'D\', \'value\' => 1, \'change\' => 0]]" />');

        expect($html)->toContain('<ol')->toContain('Up 2')->toContain('Down 1')->toContain('New')->toContain('Same')
            ->toContain('aria-current="true"')->toContain('You')->toContain('1,200<span class="sr-only"> streams</span>')
            ->not->toContain('medal')->not->toContain('data-avatar');
    });
});

describe('demo endpoints', function () {
    it('accepts a draft once however often it is sent', function () {
        $component = Livewire::test(ChatDemo::class);
        $before = count($component->get('messages'));
        $component->call('send', 'Hello', 'key-1')->call('send', 'Hello', 'key-1');

        expect(count($component->get('messages')))->toBe($before + 1)
            ->and($component->get('accepted'))->toBe(['key-1']);

        $component->call('send', '', 'key-2')->assertStatus(422);
    });

    it('loads older messages above and reacts to messages', function () {
        $component = Livewire::test(ChatDemo::class)->call('loadOlder')->assertSet('hasOlder', false)->call('react', 'm2', 'like');

        expect($component->get('messages.0.key'))->toBe('o1')
            ->and(collect($component->get('messages'))->firstWhere('key', 'm2')['reactions'][0]['count'])->toBe(1);
    });

    it('refuses a like to show the rollback, and records votes and replies once', function () {
        $feed = Livewire::test(FeedDemo::class);
        expect($feed->instance()->like('p2', true))->toBeFalse()
            ->and($feed->instance()->like('p1', true))->toBeTrue();

        Livewire::test(PollDemo::class)->call('vote', 'launch-time', ['b'])->assertSet('voted', ['b'])->assertSet('votes.b', 32)
            ->call('vote', 'launch-time', ['a'])->assertStatus(422);

        $thread = Livewire::test(FeedDemo::class)->call('reply', 'c2', 'Thanks', 'r1')->call('reply', 'c2', 'Thanks', 'r1');
        expect(collect($thread->get('comments'))->firstWhere('key', 'c2')['replies'])->toHaveCount(1);

        Livewire::test(PeopleDemo::class)->call('follow', 'u-bola', true)->assertSet('following.u-bola', true)
            ->call('openConversation', 'k2')->assertSet('unread.k2', 0)->call('follow', 'nobody', true)->assertStatus(422);
    });

    it('records answers once, votes and one accepted answer in the Q&A demo', function () {
        $qa = Livewire::test(QaDemo::class)
            ->call('answer', null, 'Reconcile at month end.', 'k1')
            ->call('answer', null, 'Reconcile at month end.', 'k1')
            ->call('like', 'a1', true)
            ->call('accept', 'a2');

        expect($qa->get('answers'))->toHaveCount(3)
            ->and($qa->get('answers')[0]['likes'])->toBe(5)
            ->and(collect($qa->get('answers'))->where('accepted', true)->pluck('key')->all())->toBe(['a2']);
        expect(Livewire::test(QaDemo::class)->call('like', 'missing', true)->get('answers')[0]['likes'])->toBe(4);
    });

    it('keeps each chat and feed response under the 40 KB payload budget', function () {
        $sizes = [
            'send' => Livewire::test(ChatDemo::class)->call('send', 'Payload check', 'k-payload')->effects,
            'older' => Livewire::test(ChatDemo::class)->call('loadOlder')->effects,
            'feed' => Livewire::test(FeedDemo::class)->call('react', 'p1', 'love')->effects,
            'reply' => Livewire::test(FeedDemo::class)->call('reply', 'c2', 'Thanks', 'k')->effects,
            'poll' => Livewire::test(PollDemo::class)->call('vote', 'launch-time', ['a'])->effects,
            'people' => Livewire::test(PeopleDemo::class)->call('follow', 'u-bola', true)->effects,
            'answer' => Livewire::test(QaDemo::class)->call('answer', null, 'Payload check', 'k-qa')->effects,
            'vote' => Livewire::test(QaDemo::class)->call('like', 'a1', true)->effects,
            'chat-reply' => Livewire::test(ChatDemo::class)->call('reply', 'm4')->call('send', 'Replying', 'k-reply')->effects,
        ];

        foreach ($sizes as $name => $effects) {
            expect(strlen(json_encode($effects)))->toBeLessThan(40_000, $name);
        }
    });
});

it('measures the chat and community colour pairs', function () {
    expect(collect(KitContrast::pairs())->filter(fn ($p) => preg_match('/^(Chat|Composer|Inbox|Like|Post|Reaction|Follow|Poll|Channel|Leaderboard)/', $p[0]))->count())->toBeGreaterThanOrEqual(30);
});
