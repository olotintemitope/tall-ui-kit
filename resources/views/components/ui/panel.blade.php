{{--
    Elevation level 1: a white form surface with a border, 8px radius and the panel
    shadow. Use on the tinted page (x-ui.form-page or a layout with surface="muted"),
    never on plain white and never inside a dialog. Body padding 16px, 20px from sm.
    Slots: default (body, children 16px apart), footer (action bar: #FAFAFA, top border,
    right-aligned; buttons go full width side by side on mobile).
--}}
<div {{ $attributes->class(['rounded-lg border border-panel-border bg-card shadow-panel']) }} data-panel>
    <div class="flex flex-col gap-4 p-4 sm:p-5">{{ $slot }}</div>

    @isset($footer)
        <div class="flex items-center justify-end gap-2 rounded-b-lg border-t border-panel-border bg-panel-footer px-4 py-3 sm:px-5 max-sm:[&>*]:flex-1">{{ $footer }}</div>
    @endisset
</div>
