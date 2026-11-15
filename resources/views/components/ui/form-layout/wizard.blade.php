{{--
    A multi-step form, one step per screen (x-ui.form inside a raised panel).
    Header: a text step list, "1 Song · 2 Targets · 3 Details · 4 Review · 5 Pay", as an
    <ol>: the current step in ink (500, aria-current="step"), completed steps followed by
    "Done" in text and linked back (a button that calls `goTo`), later steps ink-muted.
    Below sm it collapses to "Step 2 of 5" and the step's heading. No progress bar, no
    circles, no colour fills.
    Body: an h2 with the step's name (focus moves here after a step change, tabindex -1),
    then the slot. Footer: Back (outline; absent on step 1) and Continue (primary; the
    last step's label is `finishLabel`). Continue submits the form, so the server
    validates only that step and the kit's submit focus rule applies (the error summary
    with more than 3 errors, else the first invalid field). Back keeps the values (they
    stay in Livewire state; nothing is cleared).
    Announced politely: "Step 2 of 5: Targets". With `flow`, the draft (the `draftModel`
    array) is kept in sessionStorage as newbanger_draft_<flow> and offered back through
    the component's restoreDraft(array) method on load. With `guard`, leaving with
    unsaved values asks first (x-ui.form guard).

    Props: steps (list of labels), current (1-based), model (the Livewire step property,
    for the announcement), flow (draft key, null for none), draftModel ("wizard"),
    title (the flow's name, h1-level context shown above the panel), backAction (back),
    nextAction (next), gotoAction (goTo), finishLabel ("Continue"), guard (true),
    errors (x-ui.error-summary fields for the current step), dirty (the flow holds values
    that only live in this page's state, so the guard stays on after a step is accepted). Slots: default (the step),
    aside (optional, e.g. a summary rail; below the panel on phones).
--}}
@props([
    'steps' => [], 'current' => 1, 'model' => 'step', 'flow' => null, 'draftModel' => 'wizard', 'title' => null,
    'backAction' => 'back', 'nextAction' => 'next', 'gotoAction' => 'goTo', 'finishLabel' => 'Continue', 'guard' => true, 'errors' => [], 'dirty' => false,
])

@php
    $steps = array_values($steps);
    $total = count($steps);
    $current = max(1, min($total, (int) $current));
    $label = $steps[$current - 1] ?? '';
    $last = $current === $total;
@endphp

<div
    x-data="uiWizard({ flow: @js($flow), step: {{ $current }}, total: {{ $total }}, label: @js($label) })"
    x-effect="changed($wire.{{ $model }}, {{ $total }}, @js($steps)[$wire.{{ $model }} - 1] ?? '', @js((bool) $flow) ? $wire.{{ $draftModel }} : null)"
    {{ $attributes->class(['flex w-full flex-col gap-6']) }}
    data-form-layout="wizard"
    data-step="{{ $current }}"
>
    <p class="sr-only" aria-live="polite" x-text="message" data-wizard-status></p>

    <div @class(['grid grid-cols-1 items-start gap-6', 'lg:grid-cols-[minmax(0,1fr)_360px]' => isset($aside)])>
        <x-ui.form :guard="$guard" :dirty="$dirty" wire:submit="{{ $nextAction }}" class="min-w-0" data-wizard-form>
            <x-ui.panel>
                <nav aria-label="{{ $title ? $title.' steps' : 'Steps' }}" class="-mt-1">
                    <p class="type-meta sm:hidden" data-wizard-compact>Step {{ $current }} of {{ $total }}</p>
                    <ol class="hidden flex-wrap items-center gap-x-2 gap-y-1 sm:flex" data-wizard-steps>
                        @foreach ($steps as $i => $step)
                            @php($n = $i + 1)
                            <li class="flex items-center gap-2">
                                @if ($n > 1)
                                    <span class="text-ink-muted" aria-hidden="true">·</span>
                                @endif
                                @if ($n < $current)
                                    <button type="button" wire:click="{{ $gotoAction }}({{ $n }})" class="rounded-sm type-meta text-ink-body hover:text-ink focus-ring" data-wizard-step="done">
                                        <span class="type-numeric">{{ $n }}</span> {{ $step }} <span class="text-emerald-700">Done</span>
                                    </button>
                                @elseif ($n === $current)
                                    <span class="type-meta font-medium text-ink!" aria-current="step" data-wizard-step="current"><span class="type-numeric">{{ $n }}</span> {{ $step }}</span>
                                @else
                                    <span class="type-meta" data-wizard-step="upcoming"><span class="type-numeric">{{ $n }}</span> {{ $step }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </nav>

                <h2 class="type-heading rounded-sm focus-ring" tabindex="-1" wire:key="wizard-heading-{{ $current }}" x-init="arrive({{ $current }}, $el)" data-wizard-heading>{{ $label }}</h2>

                @if ($errors)
                    <x-ui.error-summary :fields="$errors" />
                @endif

                {{ $slot }}

                <x-slot:footer>
                    <div class="flex w-full items-center gap-2 max-sm:[&>*]:flex-1">
                        @if ($current > 1)
                            <x-ui.button variant="outline" wire:click="{{ $backAction }}" data-wizard-back>Back</x-ui.button>
                        @endif
                        <x-ui.button type="submit" class="sm:ml-auto" :loading="$nextAction" data-wizard-next>{{ $last ? $finishLabel : 'Continue' }}</x-ui.button>
                    </div>
                </x-slot:footer>
            </x-ui.panel>
        </x-ui.form>

        @isset($aside)
            <div class="min-w-0">{{ $aside }}</div>
        @endisset
    </div>
</div>
