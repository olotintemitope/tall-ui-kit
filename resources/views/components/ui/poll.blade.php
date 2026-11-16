{{--
    A poll (docs/components/community.md, "Poll"): the question, its options, and the
    results as bars from the progress family (x-ui.progress: "42%, 120 votes" beside
    each). Before voting it is a form: radios for one choice, check boxes for `multiple`
    (the kit's custom controls, never native look), and Vote. Results show after the
    viewer votes, once the poll has ended, or always with `results="always"`. The
    viewer's choices are marked "Your vote" in text, not colour alone. Ended polls say
    "Poll ended" with the total; open ones can say when they end.

    Voting calls `action` once with the poll's `key` and the chosen option keys, e.g.
    vote('poll-3', ['b']), then focus moves to the results (the form is gone). Nothing
    chosen: "Choose an option." under the options. Without `action` a bubbling `ui-vote`
    event, detail { key, choices }. One delegated listener for the page
    (resources/js/community-core.js), no Alpine per poll.

    Props: key (required), question, options (list of [key, label, votes]), multiple
    (false), voted (the viewer's option keys; empty: not voted), closed (false), endsAt
    (a time), results (after-vote | always), action, label ("Poll"), headingLevel (3),
    tz (Africa/Lagos), now.
--}}
@props([
    'key', 'question' => '', 'options' => [], 'multiple' => false, 'voted' => [], 'closed' => false, 'endsAt' => null,
    'results' => 'after-vote', 'action' => null, 'label' => 'Poll', 'headingLevel' => 3, 'tz' => 'Africa/Lagos', 'now' => null,
])

@php
    use Illuminate\Support\Carbon;
    use TallUiKit\Support\Format;

    $tag = 'h'.max(2, min(6, (int) $headingLevel));
    $id = 'poll-'.\Illuminate\Support\Str::slug((string) $key);
    $voted = array_map('strval', (array) $voted);
    $list = collect($options)->values()->map(fn ($o, $i) => ['key' => (string) ($o['key'] ?? $i), 'label' => (string) ($o['label'] ?? ''), 'votes' => (int) ($o['votes'] ?? 0)]);
    $total = $list->sum('votes');
    $showResults = $closed || $voted !== [] || $results === 'always';
    $nowAt = ($now ? Carbon::parse($now) : Carbon::now())->setTimezone($tz);
    $ended = $closed || ($endsAt && Carbon::parse($endsAt)->lte($nowAt));
    $showResults = $showResults || $ended;
    $ends = ! $ended && $endsAt ? 'Ends '.\TallUiKit\Support\Format::relative(Carbon::parse($endsAt)->setTimezone($tz), $nowAt) : null;
    $votes = number_format($total).' '.($total === 1 ? 'vote' : 'votes');
@endphp

<section {{ $attributes->class(['ui-poll min-w-0']) }} aria-labelledby="{{ $id }}-question" id="{{ $id }}" data-poll data-key="{{ $key }}" @if ($action) data-action="{{ $action }}" @endif>
    <{{ $tag }} id="{{ $id }}-question" class="type-subheading wrap-anywhere">{{ $question }}</{{ $tag }}>
    <p class="type-meta mt-0.5"><span class="sr-only">{{ $label }}, </span>{{ $multiple ? 'Choose any' : 'Choose one' }}</p>

    @if ($showResults)
        <div class="mt-3 flex min-w-0 flex-col gap-3 outline-none" tabindex="-1" data-poll-results>
            @foreach ($list as $o)
                @php
                    $share = $total > 0 ? $o['votes'] / $total : 0;
                    $mine = in_array($o['key'], $voted, true);
                @endphp

{{-- Markup kept on few lines: whitespace runs are nodes that every morph walks and bytes in every response. --}}
<x-ui.progress :label="$o['label'].($mine ? ' (your vote)' : '')" :value="$o['votes']" :max="max(1, $total)" :value-text="Format::percent($share, 0).', '.number_format($o['votes']).' '.($o['votes'] === 1 ? 'vote' : 'votes')" size="md" :id="$id.'-r-'.$o['key']" wire:key="{{ $id }}-r-{{ $o['key'] }}" data-poll-option="{{ $o['key'] }}" :class="$mine ? 'ui-poll-mine' : null" /> @endforeach </div> <p class="type-meta mt-3 flex flex-wrap items-center gap-x-2 gap-y-1" data-poll-total> @if ($ended)<x-ui.badge size="sm">Poll ended</x-ui.badge>@endif <span class="type-numeric text-ink-body">{{ $votes }}</span> @if ($ends)<span aria-hidden="true">·</span><span>{{ $ends }}</span>@endif </p> @else <form class="mt-3 min-w-0" novalidate data-poll-form> <fieldset class="min-w-0" aria-describedby="{{ $id }}-error"> <legend class="sr-only">{{ $question }}</legend> <div class="flex flex-col gap-1"> @foreach ($list as $o) <label class="flex min-h-9 cursor-pointer items-center gap-3 rounded-md border border-line px-3 py-2 hover:border-control-hover hover:bg-surface-subtle has-checked:border-control-hover has-checked:bg-surface-muted has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-focus" wire:key="{{ $id }}-o-{{ $o['key'] }}"> <span class="relative flex size-4 shrink-0 items-center justify-center"> <input type="{{ $multiple ? 'checkbox' : 'radio' }}" name="{{ $id }}-choice" value="{{ $o['key'] }}" @class(['peer size-4 cursor-pointer appearance-none forced-colors:appearance-auto border border-control bg-surface transition-colors duration-100 checked:border-secondary checked:bg-secondary focus-visible:outline-none', 'rounded-sm' => $multiple, 'rounded-full' => ! $multiple]) data-poll-choice> @if ($multiple)<x-ui.icon name="check" :stroke-width="3" class="pointer-events-none absolute size-3 text-on-secondary opacity-0 peer-checked:opacity-100 forced-colors:hidden" />@else<span class="pointer-events-none absolute size-1.5 rounded-full bg-on-secondary opacity-0 peer-checked:opacity-100 forced-colors:hidden" aria-hidden="true"></span>@endif </span> <span class="type-body min-w-0 wrap-anywhere text-ink">{{ $o['label'] }}</span> </label> @endforeach </div> </fieldset> <p id="{{ $id }}-error" class="type-caption mt-2 text-danger-text" role="alert" data-poll-error></p> <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-2"> <x-ui.button size="sm" data-poll-submit>Vote</x-ui.button> <span class="type-meta"><span class="type-numeric text-ink-body">{{ $votes }}</span>@if ($ends) · {{ $ends }}@endif</span> </div> </form> @endif </section>
