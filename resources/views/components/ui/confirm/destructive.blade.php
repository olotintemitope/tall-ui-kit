{{--
    Destructive confirmation: a red verb button ("Delete campaign") and focus on
    Cancel when it opens. With `confirmText` the user types it (e.g. the campaign name)
    before the button works; until then the button is aria-disabled with the reason
    linked. Props: name, title, description, confirmLabel (Delete), action, params,
    confirmText.
--}}
@props(['name', 'title', 'description' => null, 'confirmLabel' => 'Delete', 'action' => null, 'params' => [], 'confirmText' => null])

@php $fieldId = 'confirm-'.$name.'-type'; @endphp

<x-ui.confirm
    :name="$name"
    :title="$title"
    :description="$description"
    :confirm-label="$confirmLabel"
    :action="$action"
    :params="$params"
    variant="destructive"
    :can-confirm="$confirmText ? 'typed === '.\Illuminate\Support\Js::from($confirmText) : 'true'"
    :unavailable-reason="$confirmText ? $fieldId.'-hint' : null"
    {{ $attributes }}
>
    @if ($confirmText)
        <x-ui.field :label="'Type '.$confirmText.' to confirm'" :id="$fieldId" :hint="$confirmLabel.' stays unavailable until the text matches.'">
            <x-ui.input :id="$fieldId" x-model="typed" autocomplete="off" spellcheck="false" width="full" data-confirm-type />
        </x-ui.field>
    @endif
</x-ui.confirm>
