{{--
    Running text in one type role. Props: as (p), role (body|body-strong|meta|caption),
    tone (default: the role's own colour | muted ink-muted 5.86:1 | danger `danger-text`
    6.42:1 | success `success-text` 5.36:1).
    Text is never cut off (docs/ui-kit.md): it wraps, and long unbroken strings (emails,
    references) break anywhere. There is no one-line or line-clamp option.
--}}
@props(['as' => 'p', 'role' => 'body', 'tone' => 'default'])

@php
    $role = in_array($role, ['body', 'body-strong', 'meta', 'caption'], true) ? $role : 'body';
@endphp

<{{ $as }} {{ $attributes->class([
    'type-'.$role,
    'min-w-0 wrap-anywhere',
    'text-ink-muted' => $tone === 'muted',
    'text-danger-text' => $tone === 'danger',
    'text-success-text' => $tone === 'success',
]) }}>{{ $slot }}</{{ $as }}>
