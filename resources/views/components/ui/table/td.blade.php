{{--
    Body cell, 40px tall, never wraps. Props: numeric (mono, tabular, right-aligned:
    amounts, counts), strong (body-strong: names, primary text), muted (meta: dates,
    secondary text), mono (ids, left-aligned), wrap (allows wrapping and adds 8px
    vertical padding, for a cell with a second line).
--}}
@props(['numeric' => false, 'strong' => false, 'muted' => false, 'mono' => false, 'wrap' => false])

<td {{ $attributes->class([
    'h-10 px-4',
    // Cells wrap (row height grows); only numbers stay on one line. Never an ellipsis.
    'wrap-break-word py-2',
    'whitespace-nowrap' => $numeric,
    'type-numeric text-ink text-right' => $numeric,
    'type-numeric text-ink-muted' => $mono && ! $numeric,
    'type-body-strong' => $strong && ! $numeric && ! $mono,
    'type-meta' => $muted && ! $numeric && ! $mono && ! $strong,
    'type-body' => ! $numeric && ! $mono && ! $strong && ! $muted,
]) }}>{{ $slot }}</td>
