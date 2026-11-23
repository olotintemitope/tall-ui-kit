{{-- The composer alone (with @mentions, a reply line and a tool), and the typing line --}}
<div class="space-y-4">
    <x-ui.chat.typing :names="['Ada', 'Bola']" />
    <x-ui.chat.composer label="Note to the team" placeholder="Write a note" :enter-sends="false" :maxlength="280" id="docs-composer-note" />
    <x-ui.chat.composer label="Message the cohort" placeholder="Type @ to mention someone" :mentions="[['key' => 'u1', 'name' => 'Ada Obi'], ['key' => 'u2', 'name' => 'Adaeze Okafor'], ['key' => 'u3', 'name' => 'Bola Ade'], ['key' => 'u4', 'name' => 'Tunde Bakare']]" mentions-label="Cohort members" id="docs-composer-mentions">
        <x-slot:context><span class="min-w-0 wrap-anywhere">Replying to Ada Obi</span><x-ui.button variant="ghost" size="icon-sm" aria-label="Cancel reply"><x-ui.icon name="x" /></x-ui.button></x-slot:context>
        <x-slot:tools><x-ui.button variant="ghost" size="icon" aria-label="Insert code"><x-ui.icon name="code" /></x-ui.button></x-slot:tools>
    </x-ui.chat.composer>
    <x-ui.chat.composer label="Closed conversation" disabled disabled-reason="This conversation is closed." id="docs-composer-closed" />
</div>
