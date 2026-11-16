{{-- Workbench /community: FeedDemo. Posts are divided by 1px rules, not cards. --}}
<div class="min-w-0 divide-y divide-line border-y border-line" data-feed-demo>
    <x-ui.post
        key="p1"
        author="Ada Obi"
        handle="adaobi"
        verified
        time="2026-11-16 11:40:00"
        :now="$now"
        href="#post-p1"
        :body="implode(PHP_EOL.PHP_EOL, ['Bookings for the December showcase are open. Slots run from 10am to 6pm, 30 minutes each.', 'Read the brief first: https://example.com/showcase/brief. Questions to @bola or under #showcase2026.'])"
        :likes="$likes['p1']['likes']"
        :liked="$likes['p1']['liked']"
        :comments="6"
        like-action="like"
        share-url="https://example.com/posts/p1"
        mention-url="#people-:name"
        hashtag-url="#tag-:tag"
    >
        <x-ui.reactions :reactions="$reactions" action="react" subject="p1" label="Reactions to the post by Ada Obi" />
        <x-slot:media>
            <img src="{{ \Workbench\App\Livewire\Community\ChatDemo::image('Showcase floor plan') }}" alt="Floor plan of the showcase hall with the stage and two booths" width="480" height="320" class="block h-auto w-full max-w-lg rounded-lg border border-line">
        </x-slot:media>
        <x-slot:more>
            <x-ui.dropdown.item>Copy link</x-ui.dropdown.item>
            <x-ui.dropdown.item>Mute Ada Obi</x-ui.dropdown.item>
            <x-ui.dropdown.separator />
            <x-ui.dropdown.item variant="danger">Report post</x-ui.dropdown.item>
        </x-slot:more>
        <x-slot:footer>
            <x-ui.comment-thread
                :comments="$comments"
                label="Comments on the post by Ada Obi"
                :depth="3"
                reply-action="reply"
                more-action="moreReplies"
                continue-href="#thread-:key"
                :maxlength="1000"
                :now="$now"
                mention-url="#people-:name"
                id="demo-thread"
            />
        </x-slot:footer>
    </x-ui.post>

    <x-ui.post
        key="p2"
        author="Chidi Eze"
        handle="chidi.eze"
        time="2026-11-16 09:15:00"
        :now="$now"
        body="Liking this post fails on purpose: the heart turns on, the server says no, and it rolls back with a message."
        :likes="$likes['p2']['likes']"
        :liked="$likes['p2']['liked']"
        :comments="0"
        like-action="like"
    />

    <x-ui.post key="p3" author="Bola Ade" time="2026-11-15 16:00:00" :now="$now" body="Which start time works for the launch call?" :likes="32" :comments="2">
        @livewire(\Workbench\App\Livewire\Community\PollDemo::class)
    </x-ui.post>
</div>
