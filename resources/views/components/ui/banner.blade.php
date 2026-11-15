{{--
    A page-level announcement (docs/components/workflow.md, "Banner"): maintenance
    windows, a new policy, a billing problem. Not a toast: it sits in the page flow at
    the top of the main region, stays until it is dismissed (or for good when it is not
    dismissible), never takes focus and never stacks. For a result of the user's own
    action use a toast; for a message about one form or section, x-ui.alert.

    Look (the x-ui.alert convention, flat): a light status fill with AA text and a 1px
    rule in a light tone of the status (border-bottom for `page`, all round with a 6px
    radius for `inset`). info blue-700 on blue-50, success emerald-800 on emerald-50,
    warning amber-900 on amber-50, danger red-800 on red-50 (KitContrast "Banner").
    No icon, no coloured edge, no shadow. An optional title (body-strong) runs into the
    message (body); the optional action is an underlined link in the same tone.

    Dismissing: `dismissible` adds an x button ("Dismiss announcement"); after it, focus
    moves to the next control on the page, else the page heading, never <body>. With a
    `name` it stays dismissed, by `persist`:
      local   (the default with a name) localStorage <prefix>dismiss_banner:<name>,
              applied before first paint by a one-line script, so it never flashes
      server  the bound TallUiKit\Support\Dismiss\DismissStore (by default the
              ChecklistStore binding: the session, or the app's per-user store), read
              here on render and written through $wire.dismissNotice('banner:<name>')
              (the WithDismissals trait); outside Livewire it falls back to local
      none    nothing kept
    Put a version in the name ("maintenance-2026-11") so a new announcement shows again.
    Every dismissal fires a bubbling `ui-dismiss` DOM event, detail { key }.

    Props: tone (info | success | warning | danger), title, actionLabel, actionHref,
    dismissible, name (letters, digits, _ : -, up to 57 characters), persist,
    dismissed (overrides the store), variant (page | inset), label (the region's
    name; defaults to the title; without either the banner is not a landmark), dismissLabel
    ("Dismiss announcement"), id. Slot: the message.
--}}
@props([
    'tone' => 'info', 'title' => null, 'actionLabel' => null, 'actionHref' => null, 'dismissible' => false,
    'name' => null, 'persist' => null, 'dismissed' => null, 'variant' => 'page', 'label' => null,
    'dismissLabel' => 'Dismiss announcement', 'id' => null,
])

@php
    $key = $name === null ? null : 'banner:'.$name;
    if ($key !== null && preg_match(\TallUiKit\Support\Dismiss\DismissStore::KEY_PATTERN, $key) !== 1) {
        throw new \InvalidArgumentException("x-ui.banner name [{$name}] may only hold letters, digits, _ : and -, up to 57 characters.");
    }
    $persist = $key === null || ! $dismissible ? 'none' : ($persist ?? 'local');
    $isDismissed = $dismissible && (bool) ($dismissed ?? ($persist === 'server' ? app(\TallUiKit\Support\Dismiss\DismissStore::class)->dismissed($key) : false));
    $id ??= 'banner-'.($name ? \Illuminate\Support\Str::slug($name) : substr(md5((string) $title.$tone), 0, 6));
    $tones = [
        'info' => 'border-info-edge bg-info-tint text-info-text',
        'success' => 'border-success-edge bg-success-tint text-success-strong',
        'warning' => 'border-warning-edge bg-warning-tint text-warning-strong',
        'danger' => 'border-danger-edge bg-danger-tint text-danger-strong',
    ];
    $tone = array_key_exists($tone, $tones) ? $tone : 'info';
    // A named region (landmark) when it has a title or label; a plain block otherwise, so
    // several untitled banners never make landmarks with the same name.
    $region = $label ?? $title;
@endphp

@unless ($isDismissed)
<{{ $region ? 'section' : 'div' }}
    {{ $attributes->class([
        'ui-banner flex min-w-0 items-start gap-3',
        $variant === 'inset' ? 'rounded-md border px-3 py-2.5' : 'border-b px-4 py-2.5 sm:px-6',
        $tones[$tone],
    ]) }}
    id="{{ $id }}"
    @if ($region) aria-label="{{ $region }}" @endif
    data-banner="{{ $tone }}"
    @if ($dismissible) x-data="uiBanner(@js(['key' => $key, 'persist' => $persist]))" x-show="! hidden" @endif
>
    <div class="flex min-w-0 flex-1 flex-wrap items-baseline gap-x-2 gap-y-1 py-0.5">
        @if ($title)
            <p class="type-body-strong text-current wrap-anywhere">{{ $title }}</p>
        @endif
        <div class="type-body text-current min-w-0 wrap-anywhere">{{ $slot }}</div>
        @if ($actionLabel && $actionHref)
            <a href="{{ $actionHref }}" class="type-body-strong rounded-sm text-current underline underline-offset-3 hover:no-underline focus-ring" data-banner-action>{{ $actionLabel }}</a>
        @endif
    </div>
    @if ($dismissible)
        <button type="button" class="-my-0.5 -mr-1.5 inline-flex size-7 shrink-0 items-center justify-center rounded-md text-current hover:bg-surface focus-ring" aria-label="{{ $dismissLabel }}" x-on:click="dismiss()" data-banner-dismiss>
            <x-ui.icon name="x" class="size-4" />
        </button>
    @endif
</{{ $region ? 'section' : 'div' }}>
@if ($persist === 'local')
<script>try{if(localStorage.getItem((document.documentElement.dataset.uiStoragePrefix||'tallui_')+@js('dismiss_'.$key))==='1')document.getElementById(@js($id)).style.display='none'}catch(e){}</script>
@endif
@endunless
