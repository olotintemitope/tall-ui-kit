{{--
    Older name for x-ui.confirm, kept so existing callers (data tables, the gallery)
    work unchanged: same props (name, title, description, confirmLabel, action,
    variant destructive | default), same events (open-confirm / close-confirm, and
    `confirmed` with the name when there is no action). Destructive is the default
    here, as before. New code uses x-ui.confirm or x-ui.confirm.destructive.
--}}
@props(['name', 'title', 'description' => null, 'confirmLabel' => 'Delete', 'action' => null, 'variant' => 'destructive'])

<x-ui.confirm :name="$name" :title="$title" :description="$description" :confirm-label="$confirmLabel" :action="$action" :variant="$variant === 'destructive' ? 'destructive' : 'default'" {{ $attributes }} />
