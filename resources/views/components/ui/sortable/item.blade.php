{{--
    One row of x-ui.sortable: the grip handle, then the label (body) and an optional
    description (meta), or the slot for richer content (the slot replaces the
    description; keep `label`, it names the handle and the announcements).
    Props: key (stable: sent to the server in the new order), label, description,
    help (the id of the list's instructions; x-ui.sortable passes it), disabled.
    Other attributes go on the <li> (wire:key).
--}}
@props(['key', 'label', 'description' => null, 'help' => null, 'disabled' => false])

{{-- Compact markup: every whitespace run between tags is a text node each morph walks. --}}
<li {{ $attributes->class(['ui-sortable-item flex min-w-0 items-start gap-2 bg-white px-2 py-2']) }} data-sortable-item data-key="{{ $key }}" data-label="{{ $label }}"><button type="button" class="ui-sortable-handle focus-ring" aria-label="Reorder {{ $label }}" @if ($help) aria-describedby="{{ $help }}" @endif @disabled($disabled) data-sortable-handle><x-ui.icon name="grip-vertical" class="size-4" /></button><div class="min-w-0 flex-1 py-1.5"><p class="type-body wrap-anywhere text-ink">{{ $label }}</p>@if ($slot->isNotEmpty())<div class="mt-0.5 min-w-0">{{ $slot }}</div>@elseif ($description)<p class="type-meta wrap-anywhere">{{ $description }}</p>@endif</div></li>
