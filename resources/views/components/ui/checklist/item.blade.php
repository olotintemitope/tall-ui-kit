{{--
    One step of x-ui.checklist. A row on the list's 1px `line` rules: the state mark,
    the label (with a hint below it) and, while the step is not done, a link-style
    action on the right (below the text on phones).

    Look, by state (ratios in the Contrast table, KitContrast "Checklist" rows):
      not done  an empty 16px circle in `control` (3.65:1), label in body (ink-body)
      next      the first step not done: a `surface-subtle` tint and the label in
                body-strong; the circle keeps 3.38:1 on the tint, the action 4.82:1
      done      a plain 16px check in ink (15.81:1), label in ink-muted (5.85:1), never
                crossed out; no action
    No icon tile, no fill behind the mark. Each row says its state to screen readers
    ("Done" / "Not done", before the label) and the next one has aria-current="step".
    The action is named by its own label and described by the step label.
    When a Livewire update turns the step done, the check scales in (kit motion
    tokens, instant under reduced motion), a polite status names the step and focus
    stays in the row (resources/js/checklist.js).

    Props: label, hint, done (false), optional (false: shows "Optional"), next (false),
    href or action (a Livewire call such as "verifyEmail" or "complete('profile')") with
    actionLabel, id. Other attributes go on the <li> (wire:key, data-*).
--}}
@props([
    'label',
    'hint' => null,
    'done' => false,
    'optional' => false,
    'next' => false,
    'href' => null,
    'action' => null,
    'actionLabel' => null,
    'id' => null,
])

@php
    $id ??= 'checklist-step-'.substr(md5($label), 0, 8);
    $done = (bool) $done;
    $next = $next && ! $done;
    $showAction = ! $done && $actionLabel && ($href || $action);
    // wire:target for the button's loading state: the method name without arguments.
    $target = $action ? strtok($action, '(') : null;
@endphp

{{-- Compact markup: every whitespace run between tags is a text node each morph walks. --}}
<li {{ $attributes->class(['flex gap-3 px-4 py-3 outline-none last:rounded-b-lg', 'bg-surface-subtle' => $next]) }} id="{{ $id }}" tabindex="-1" data-checklist-item="{{ $id }}" data-done="{{ $done ? '1' : '0' }}" data-label="{{ $label }}" @if ($next) aria-current="step" @endif><span class="flex h-5 w-4 shrink-0 items-center justify-center">@if ($done)<x-ui.icon name="check" class="size-4 text-ink" data-checklist-check />@else<x-ui.icon name="circle" class="size-4 text-control" />@endif</span><div class="flex min-w-0 flex-1 flex-col gap-1 sm:flex-row sm:items-start sm:justify-between sm:gap-4"><div class="min-w-0"><p id="{{ $id }}-label" @class(['wrap-anywhere', 'type-body text-ink-muted' => $done, 'type-body-strong' => $next, 'type-body' => ! $done && ! $next])><span class="sr-only">{{ $done ? 'Done' : 'Not done' }}: </span>{{ $label }}@if ($optional)<span class="type-meta"> (Optional)</span>@endif</p>@if ($hint)<p class="type-meta wrap-anywhere">{{ $hint }}</p>@endif</div>@if ($showAction)@if ($href)<x-ui.button variant="link" size="sm" :href="$href" class="min-h-6 shrink-0 self-start" aria-describedby="{{ $id }}-label">{{ $actionLabel }}</x-ui.button>@else<x-ui.button variant="link" size="sm" wire:click="{{ $action }}" :loading="$target" class="min-h-6 shrink-0 self-start" aria-describedby="{{ $id }}-label">{{ $actionLabel }}</x-ui.button>@endif @endif</div></li>
