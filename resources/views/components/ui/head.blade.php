{{--
    Put once in <head>, before the stylesheet and the kit's scripts. Prints the storage
    prefix on <html> (data-ui-storage-prefix, read by resources/js/storage.js) and
    applies stored state before first paint, so nothing flashes or jumps: the colour
    scheme (html[data-color-scheme], docs/theming.md "Dark mode"), privacy mode
    (x-ui.money) and, with `area`, the sidebar collapse and focus mode of that shell.

    Colour scheme, first match wins: `scheme` (a value the server decided for this
    page), the bound ColorSchemeStore when config('tall-ui-kit.color_scheme.persist') is
    'server', localStorage <prefix>color_scheme, then the configured default. Values
    outside config('tall-ui-kit.color_scheme.schemes') are ignored. A server value is
    copied to localStorage, so later pages and other tabs agree.
    Props: area (null), privacyKey (null: <prefix>privacy_mode; an app keeping an
    older key passes it here), scheme (null).
--}}
@props(['area' => null, 'privacyKey' => null, 'scheme' => null])

@php
    $prefix = (string) config('tall-ui-kit.storage_prefix', 'tallui_');
    $privacyKey ??= $prefix.'privacy_mode';
    $schemes = \TallUiKit\Support\ColorScheme\ColorScheme::schemes();
    $server = is_string($scheme) && in_array($scheme, $schemes, true) ? $scheme : \TallUiKit\Support\ColorScheme\ColorScheme::stored();
@endphp

<script>
    (function (root) {
        root.dataset.uiStoragePrefix = @js($prefix);
        root.dataset.uiPrivacyKey = @js($privacyKey);
        root.dataset.colorSchemes = @js(implode(' ', $schemes));
        root.dataset.colorSchemePersist = @js(\TallUiKit\Support\ColorScheme\ColorScheme::persist());
        var schemes = @js($schemes), scheme = @js($server), key = @js($prefix.'color_scheme');
        try {
            if (scheme) localStorage.setItem(key, scheme);
            else scheme = localStorage.getItem(key);
        } catch (e) {}
        root.dataset.colorScheme = schemes.indexOf(scheme) > -1 ? scheme : @js(\TallUiKit\Support\ColorScheme\ColorScheme::default());
        try {
            if (localStorage.getItem(@js($privacyKey)) === 'hidden') root.dataset.privacy = 'hidden';
            @if ($area)
                if (localStorage.getItem(@js($prefix.'sidebar_'.$area)) === 'true') root.dataset.sidebar = 'collapsed';
                if (localStorage.getItem(@js($prefix.'focus_'.$area)) === 'on') root.dataset.focus = 'on';
            @endif
        } catch (e) {}
    })(document.documentElement);
</script>
