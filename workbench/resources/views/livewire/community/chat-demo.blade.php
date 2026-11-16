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
    </x-ui.chat>
    <p class="type-meta" data-chat-accepted>Messages accepted by the server: <span class="type-numeric text-ink-body">{{ count($accepted) }}</span></p>
</div>
