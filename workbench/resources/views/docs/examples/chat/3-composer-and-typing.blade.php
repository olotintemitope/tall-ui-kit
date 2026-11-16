{{-- The composer alone, and the typing line --}}
<div class="space-y-4">
    <x-ui.chat.typing :names="['Ada', 'Bola']" />
    <x-ui.chat.composer label="Note to the team" placeholder="Write a note" :enter-sends="false" :maxlength="280" id="docs-composer-note" />
    <x-ui.chat.composer label="Closed conversation" disabled disabled-reason="This conversation is closed." id="docs-composer-closed" />
</div>
