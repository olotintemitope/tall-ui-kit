{{-- Workbench /community: PollDemo. --}}
<div class="min-w-0" data-poll-demo>
    <x-ui.poll
            key="launch-time"
            question="Launch call start time"
            :options="[['key' => 'a', 'label' => '9:00, before stand-up', 'votes' => $votes['a']], ['key' => 'b', 'label' => '13:00, after lunch', 'votes' => $votes['b']], ['key' => 'c', 'label' => '17:00, end of day, for the teams in other time zones', 'votes' => $votes['c']]]"
            :voted="$voted"
            ends-at="2026-11-18 12:00:00"
            :now="$now"
            action="vote"
            :heading-level="4"
        />
</div>
