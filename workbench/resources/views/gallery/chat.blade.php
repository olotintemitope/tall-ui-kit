{{--
    Workbench /chat: x-ui.chat (a live Livewire conversation: send, load older, simulate a
    reply, retry, react), the composer's options, message states, typing and day
    separators, on neutral sample data.
--}}
@php
    $now = '2026-11-16 14:00:00';
@endphp
<x-workbench::gallery.layout title="Chat">
    <x-ui.page-header eyebrow="UI kit" title="Chat" description="Conversations, messages, the composer, typing and the inbox. Data in, one Livewire call per action." />

    <div class="space-y-10" data-chat-gallery>
        <section aria-labelledby="conversation" class="min-w-0 space-y-3">
            <x-ui.section-label as="h2" id="conversation">Conversation</x-ui.section-label>
            <x-ui.text role="meta">Scroll up, then use Simulate a reply: a "new messages" button appears. Enter sends, Shift+Enter adds a line.</x-ui.text>
            @livewire(\Workbench\App\Livewire\Community\ChatDemo::class)
        </section>

        <section aria-labelledby="composer" class="min-w-0 space-y-6">
            <x-ui.section-label as="h2" id="composer">Composer</x-ui.section-label>
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="min-w-0 space-y-2">
                    <x-ui.text role="meta">Ctrl or Cmd+Enter sends (enter-sends off), with a 280 character limit:</x-ui.text>
                    <x-ui.chat.composer label="Note to the team" placeholder="Write a note" :enter-sends="false" :maxlength="280" id="composer-note" />
                </div>
                <div class="min-w-0 space-y-2">
                    <x-ui.text role="meta">Disabled, with the reason:</x-ui.text>
                    <x-ui.chat.composer label="Reply" disabled disabled-reason="This conversation is closed. Start a new one to reply." id="composer-closed" />
                </div>
                <div class="min-w-0 space-y-2">
                    <x-ui.text role="meta">Small (32px), for replies:</x-ui.text>
                    <x-ui.chat.composer label="Reply to Ada Obi" placeholder="Write a reply" size="sm" id="composer-small" />
                </div>
                <div class="min-w-0 space-y-2">
                    <x-ui.text role="meta">Large (40px):</x-ui.text>
                    <x-ui.chat.composer label="Message to support" size="lg" submit-label="Send message" id="composer-large" />
                </div>
            </div>
        </section>

        <section aria-labelledby="states" class="min-w-0 space-y-3">
            <x-ui.section-label as="h2" id="states">Message states</x-ui.section-label>
            <x-ui.chat label="Example messages" :now="$now" framed height="h-auto" id="chat-states" data-chat-images>
                <x-ui.chat.day date="2026-11-16" :now="$now" />
                <x-ui.chat.message key="s1" author="Efe Okoro" time="2026-11-16 10:00:00" body="A received message with a very long address that has to wrap: https://example.com/reports/2026/november/creators-and-campaigns-summary-final-version.pdf" />
                <x-ui.chat.message key="s2" author="Efe Okoro" time="2026-11-16 10:01:00" body="Grouped under the one above." grouped />
                <x-ui.chat.message key="s3" mine time="2026-11-16 10:05:00" body="Sending now" status="sending" />
                <x-ui.chat.message key="s4" mine time="2026-11-16 10:06:00" body="This one did not go through." status="failed" />
                <x-ui.chat.message key="s5" mine time="2026-11-16 10:07:00" body="Fixed a typo here." edited />
                <x-ui.chat.message key="s6" author="Efe Okoro" time="2026-11-16 10:10:00" deleted />
                <x-ui.chat.message key="s7" author="Efe Okoro" time="2026-11-16 10:12:00" body="<script>alert('not run')</script> stays text." />
                <x-ui.chat.message key="s8" mine time="2026-11-16 10:15:00" body="Seen by everyone." seen />
            </x-ui.chat>
        </section>

        <section aria-labelledby="typing" class="min-w-0 space-y-3">
            <x-ui.section-label as="h2" id="typing">Typing</x-ui.section-label>
            <div class="space-y-2">
                <x-ui.chat.typing :names="['Ada']" />
                <x-ui.chat.typing :names="['Ada', 'Bola']" />
                <x-ui.chat.typing :names="['Ada', 'Bola', 'Chidi']" />
                <div data-motion-preview="reduced" class="space-y-1">
                    <x-ui.text role="meta">Reduced motion, the dots hold still:</x-ui.text>
                    <x-ui.chat.typing :names="['Dayo']" />
                </div>
            </div>
        </section>
    </div>
</x-workbench::gallery.layout>
