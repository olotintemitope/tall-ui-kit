{{-- Workbench /chat: ChatDemo. --}}
<div class="min-w-0 space-y-3" data-chat-demo>
    <x-ui.chat
        label="Conversation with Bola Ade"
        :messages="$messages"
        older-action="loadOlder"
        :has-older="$hasOlder"
        :typing="['Bola']"
        send-action="send"
        :maxlength="2000"
        retry-action="retry"
        react-action="react"
        framed
        height="h-[34rem] max-h-[80vh]"
        :now="$now"
        id="demo-chat"
    >
        <x-slot:header>
            <div class="flex min-w-0 flex-wrap items-center justify-between gap-2">
                <div class="flex min-w-0 items-center gap-2">
                    <x-ui.avatar name="Bola Ade" alt="" />
                    <p class="type-body-strong wrap-anywhere">Bola Ade</p>
                </div>
                <x-ui.button variant="outline" size="sm" wire:click="simulateReply" loading="simulateReply" data-simulate-reply>Simulate a reply</x-ui.button>
            </div>
        </x-slot:header>
        <x-slot:composer>
            {{-- @mentions: the loaded people, plus findPeople() for anyone else in the team. --}}
            <x-ui.chat.composer action="send" :maxlength="2000" :mentions="$people" mention-search="findPeople" typing-action="typing" mentions-label="Team" id="demo-chat-composer">
                <x-slot:tools>
                    <x-ui.button type="button" variant="ghost" size="icon" aria-label="Mention someone" x-on:click="startMention()" data-composer-mention-tool><x-ui.icon name="at-sign" /></x-ui.button>
                </x-slot:tools>
            </x-ui.chat.composer>
        </x-slot:composer>
    </x-ui.chat>
    <p class="type-meta" data-chat-accepted>Messages accepted by the server: <span class="type-numeric text-ink-body">{{ count($accepted) }}</span></p>
    <p class="type-meta" data-chat-mentioned>Mentioned in your last message: <span class="text-ink-body">{{ $lastMentioned === [] ? 'nobody' : implode(', ', $lastMentioned) }}</span></p>
</div>
