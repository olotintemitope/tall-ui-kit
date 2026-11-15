{{--
    Put once in <head>, before the kit's scripts. Prints the storage prefix on <html>
    (data-ui-storage-prefix, read by resources/js/storage.js) and applies stored state
    before first paint, so nothing flashes or jumps: privacy mode (x-ui.money) and,
    with `area`, the sidebar collapse and focus mode of that shell.
    Props: area (null), privacyKey (null: <prefix>privacy_mode; an app keeping an
    older key passes it here).
--}}
@props(['area' => null, 'privacyKey' => null])

@php
    $prefix = (string) config('tall-ui-kit.storage_prefix', 'tallui_');
    $privacyKey ??= $prefix.'privacy_mode';
@endphp

<script>
    (function (root) {
        root.dataset.uiStoragePrefix = @js($prefix);
        root.dataset.uiPrivacyKey = @js($privacyKey);
        try {
            if (localStorage.getItem(@js($privacyKey)) === 'hidden') root.dataset.privacy = 'hidden';
            @if ($area)
                if (localStorage.getItem(@js($prefix.'sidebar_'.$area)) === 'true') root.dataset.sidebar = 'collapsed';
                if (localStorage.getItem(@js($prefix.'focus_'.$area)) === 'on') root.dataset.focus = 'on';
            @endif
        } catch (e) {}
    })(document.documentElement);
</script>
