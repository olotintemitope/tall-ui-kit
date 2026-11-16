{{-- Slot mode: message states --}}
<x-ui.chat label="Message states example" height="h-auto" now="2026-11-16 14:00:00" id="docs-chat-states">
    <x-ui.chat.day date="2026-11-16" now="2026-11-16 14:00:00" />
    <x-ui.chat.message key="e1" mine time="2026-11-16 10:05:00" body="Sending now" status="sending" />
    <x-ui.chat.message key="e2" mine time="2026-11-16 10:06:00" body="This one did not go through." status="failed" />
    <x-ui.chat.message key="e3" mine time="2026-11-16 10:07:00" body="Fixed a typo here." edited />
    <x-ui.chat.message key="e4" author="Efe Okoro" time="2026-11-16 10:10:00" deleted />
    <x-ui.chat.message key="e5" author="Efe Okoro" time="2026-11-16 10:12:00" body="<b>Markup</b> stays text." :attachments="[['type' => 'file', 'name' => 'rates-2026.pdf', 'href' => '#rates', 'meta' => '120 KB']]" />
</x-ui.chat>
