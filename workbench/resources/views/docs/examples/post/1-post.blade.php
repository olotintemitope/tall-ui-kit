{{-- A post with a verified author, links, reactions and a More menu --}}
<x-ui.post
    key="docs-p1"
    author="Ada Obi"
    handle="adaobi"
    verified
    time="2026-11-16 11:40:00"
    now="2026-11-16 14:00:00"
    body="Bookings for the December showcase are open. Brief: https://example.com/showcase/brief. Questions to @bola under #showcase2026."
    :likes="1240"
    liked
    :comments="6"
    share-url="https://example.com/posts/1"
    mention-url="#people-:name"
    hashtag-url="#tag-:tag"
>
    <x-ui.reactions label="Reactions to the docs post" subject="docs-p1" :reactions="[['key' => 'like', 'count' => 12, 'mine' => true], ['key' => 'celebrate', 'count' => 4]]" />
    <x-slot:more>
        <x-ui.dropdown.item>Copy link</x-ui.dropdown.item>
        <x-ui.dropdown.item variant="danger">Report post</x-ui.dropdown.item>
    </x-slot:more>
</x-ui.post>
