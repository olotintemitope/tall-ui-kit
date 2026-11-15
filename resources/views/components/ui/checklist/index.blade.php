{{--
    A checklist of steps for any onboarding, setup, verification or launch flow. Generic:
    steps, their state and their actions are data the page passes in; the kit knows
    nothing about the app's models or routes.

    Look: a flat card (1px `line` border, 8px radius, no shadow). The header holds the
    title (heading role), an optional description, "2 of 5 done" (meta) and the kit's
    x-ui.progress bar; the steps are an ordered list on 1px rules (x-ui.checklist.item).
    The first step not done is "next": a `surface-subtle` tint and a body-strong label.
    When every required step is done, a footer line says so ("You're all set.") with the
    Dismiss button when `dismissible`. No icon tiles, gradients, glow or celebration.

    Progress counts the required steps; `optional` steps are labelled and never block
    "all done". `dismissed` is honoured only while every required step is done, so a
    new required step brings the checklist back.

    Accessibility: a <section> named by its title; the list is aria-describedby the
    progress line; each step says "Done" or "Not done"; the collapse button has
    aria-expanded and aria-controls and keeps focus. After a Livewire update that turns
    a step done, a polite status names it ("Verify your email: done.") and focus stays
    in that row. After Dismiss, focus moves to the next control on the page, else the
    page heading, never <body>.

    Motion: the check scales in and the bar fills (kit motion tokens: --motion-sm,
    --motion-md), only when a step turns done; instant under reduced motion.

    Persistence of collapsed and dismissed, by `name` (letters, digits, _ : -):
      persist="local"   (the default with a name) localStorage, <prefix>checklist_<name>
      persist="server"  the bound TallUiKit\Support\Checklist\ChecklistStore: read here
                        on render, written through $wire by the WithChecklist trait on
                        the surrounding Livewire component (outside Livewire it falls
                        back to localStorage)
      persist="none"    nothing kept (also without a name)
    Every change also fires a bubbling `ui-checklist` DOM event, detail
    { name, action: 'collapse' | 'dismiss', collapsed }, for apps that store it their own way.

    Props: title, description, items (list of [label, hint, done, optional, href,
    action (or 'wire:click'), actionLabel, key]), name, collapsible, collapsed (initial,
    overrides the store), dismissible, dismissed (overrides the store), persist,
    loading (skeleton), completeText ("You're all set."), dismissLabel ("Dismiss"),
    headingLevel (2), id. Instead of `items`, x-ui.checklist.item rows can go in the
    slot with `completed` and `total` (and `next` on the right row).
    States: loading (skeleton with one status), partial, all done, dismissed (nothing).
--}}
@props([
    'title',
    'description' => null,
    'items' => [],
    'name' => null,
    'collapsible' => false,
    'collapsed' => null,
    'dismissible' => false,
    'dismissed' => null,
    'persist' => null,
    'loading' => false,
    'completeText' => "You're all set.",
    'dismissLabel' => 'Dismiss',
    'headingLevel' => 2,
    'completed' => null,
    'total' => null,
    'id' => null,
])

@php
    if ($name !== null && preg_match(\TallUiKit\Support\Checklist\ChecklistStore::KEY_PATTERN, $name) !== 1) {
        throw new \InvalidArgumentException("x-ui.checklist name [{$name}] may only hold letters, digits, _ : and -, up to 64 characters.");
    }

    $persist = $name === null ? 'none' : ($persist ?? 'local');
    $id ??= 'checklist-'.\Illuminate\Support\Str::slug($name ?? $title);

    $rows = collect($items)->values()->map(fn (array $item, int $i): array => [
        'key' => (string) ($item['key'] ?? $i),
        'label' => (string) $item['label'],
        'hint' => $item['hint'] ?? null,
        'done' => (bool) ($item['done'] ?? false),
        'optional' => (bool) ($item['optional'] ?? false),
        'href' => $item['href'] ?? null,
        'action' => $item['action'] ?? $item['wire:click'] ?? null,
        'actionLabel' => $item['actionLabel'] ?? $item['action_label'] ?? null,
    ]);

    if ($rows->isNotEmpty()) {
        $required = $rows->reject(fn (array $r): bool => $r['optional']);
        $total = $required->count();
        $completed = $required->where('done', true)->count();
        $nextIndex = $required->search(fn (array $r): bool => ! $r['done']);
    } else {
        $total = (int) $total;
        $completed = (int) $completed;
        $nextIndex = false;
    }

    $complete = $total > 0 ? $completed >= $total : $rows->isNotEmpty() && $rows->every(fn (array $r): bool => $r['done']);

    $store = $persist === 'server' ? app(\TallUiKit\Support\Checklist\ChecklistStore::class) : null;
    $collapsed = $collapsible && (bool) ($collapsed ?? $store?->collapsed($name) ?? false);
    $dismissed = $dismissible && $complete && (bool) ($dismissed ?? $store?->dismissed($name) ?? false);

    $progressText = $completed.' of '.$total.' done';
    $tag = 'h'.max(2, min(6, (int) $headingLevel));
@endphp

@if ($loading)
    <div {{ $attributes->class(['min-w-0 rounded-lg border border-line bg-white']) }} role="status" data-checklist-loading>
        <span class="sr-only">Loading {{ $title }}</span>
        <div aria-hidden="true">
            <div class="flex flex-wrap items-start justify-between gap-x-4 gap-y-2 border-b border-line px-4 py-3">
                <div class="min-w-0 flex-1">
                    <x-ui.skeleton.line role="body" width="w-48 max-w-full" />
                    <x-ui.skeleton.line role="meta" width="w-64 max-w-full" />
                </div>
                <x-ui.skeleton.line role="meta" width="w-40" />
            </div>
            @for ($i = 0; $i < max(3, $rows->count()); $i++)
                <div class="flex gap-3 border-b border-line px-4 py-3 last:border-b-0">
                    <x-ui.skeleton.line role="body" width="w-4" />
                    <div class="min-w-0 flex-1">
                        <x-ui.skeleton.line role="body" width="w-2/5" />
                        <x-ui.skeleton.line role="meta" width="w-3/5" />
                    </div>
                </div>
            @endfor
        </div>
    </div>
@elseif (! $dismissed)
    <section
        {{ $attributes->class(['min-w-0 rounded-lg border border-line bg-white']) }}
        id="{{ $id }}"
        aria-labelledby="{{ $id }}-title"
        x-data="uiChecklist(@js(['name' => $name, 'persist' => $persist, 'collapsed' => $collapsed]))"
        x-show="! hidden"
        @if ($persist === 'local' && $dismissible && $complete) x-cloak @endif
        data-checklist="{{ $name ?? $id }}"
        data-complete="{{ $complete ? '1' : '0' }}"
        data-dismissible="{{ $dismissible ? '1' : '0' }}"
    >
        <div class="flex flex-wrap items-start justify-between gap-x-4 gap-y-2 px-4 py-3">
            <div class="min-w-0 flex-1 basis-56">
                <{{ $tag }} id="{{ $id }}-title" class="type-heading wrap-anywhere">{{ $title }}</{{ $tag }}>
                @if ($description)
                    <p class="type-meta wrap-anywhere">{{ $description }}</p>
                @endif
            </div>
            <div class="flex w-full items-center gap-3 sm:w-auto">
                <p id="{{ $id }}-progress" class="type-meta whitespace-nowrap">{{ $progressText }}</p>
                @if ($total > 0)
                    <x-ui.progress :label="$title.' progress'" :value="$completed" :max="$total" :value-text="$progressText" hide-label :id="$id.'-bar'" class="min-w-24 flex-1 sm:w-32 sm:flex-none" />
                @endif
                @if ($collapsible)
                    <x-ui.button
                        variant="ghost"
                        size="icon-sm"
                        aria-label="Show steps"
                        aria-controls="{{ $id }}-steps"
                        aria-expanded="{{ $collapsed ? 'false' : 'true' }}"
                        x-bind:aria-expanded="collapsed ? 'false' : 'true'"
                        x-on:click="toggle()"
                        class="-my-1 -mr-1.5 [&[aria-expanded=true]_svg]:rotate-180"
                        data-checklist-toggle
                    >
                        <x-ui.icon name="chevron-down" class="transition-transform duration-150 ease-motion-out motion-reduce:transition-none" />
                    </x-ui.button>
                @endif
            </div>
        </div>

        <div id="{{ $id }}-steps" class="border-t border-line" x-show="! collapsed" @if ($collapsed) x-cloak @endif data-checklist-steps>
            <ol class="divide-y divide-line" aria-labelledby="{{ $id }}-title" aria-describedby="{{ $id }}-progress">
                @if ($rows->isNotEmpty())
                    @foreach ($rows as $i => $row)
                        <x-ui.checklist.item
                            :id="$id.'-'.\Illuminate\Support\Str::slug($row['key'])"
                            :label="$row['label']"
                            :hint="$row['hint']"
                            :done="$row['done']"
                            :optional="$row['optional']"
                            :next="$nextIndex === $i"
                            :href="$row['href']"
                            :action="$row['action']"
                            :action-label="$row['actionLabel']"
                            wire:key="{{ $id }}-{{ $row['key'] }}"
                        />
                    @endforeach
                @else
                    {{ $slot }}
                @endif
            </ol>
        </div>

        @if ($complete)
            <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2 border-t border-line px-4 py-3" data-checklist-complete>
                <p class="type-body text-ink">{{ $completeText }}</p>
                @if ($dismissible)
                    <x-ui.button variant="outline" size="sm" x-on:click="dismiss()" data-checklist-dismiss>{{ $dismissLabel }}</x-ui.button>
                @endif
            </div>
        @endif

        <p class="sr-only" role="status" aria-live="polite" wire:ignore data-checklist-live></p>
    </section>
@endif
