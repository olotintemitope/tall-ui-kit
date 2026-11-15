<?php

namespace TallUiKit\Support;

/**
 * Class strings for each x-ui.sidebar variant (dark, light, inset) and the active-link
 * rule. The dark variant sits on the `sidebar` token, which stays dark in the dark
 * colour scheme too (with a 1px `line` edge there, so it never melts into the page);
 * light and inset follow the scheme through their tokens. Every pair is measured in SidebarContrast. The logo per variant comes from
 * config('tall-ui-kit.brand'); with no logo the brand name prints as text.
 */
class SidebarTones
{
    /**
     * @return array<string, string|null>
     */
    public static function tones(string $variant): array
    {
        if ($variant === 'dark') {
            return [
                'surface' => 'bg-sidebar',
                'line' => 'border-white/[0.08]',
                'label' => 'text-white/65',
                'item' => 'text-white/70 hover:bg-white/[0.06] hover:text-white',
                'itemActive' => 'bg-white/[0.10] text-white font-medium ring-1 ring-inset ring-white/40',
                'itemDisabled' => 'text-white/65 cursor-not-allowed',
                'icon' => 'text-white/60 group-hover/nav:text-white',
                'iconActive' => 'text-white',
                'iconDisabled' => 'text-white/60',
                'count' => 'text-white/65',
                'tag' => 'border-white/40 text-white',
                'control' => 'text-white/70 hover:bg-white/[0.06] hover:text-white',
                'search' => 'border-white/35 bg-white/[0.04] text-white/70 hover:bg-white/[0.07] hover:text-white',
                'name' => 'text-white',
                'meta' => 'text-white/65',
                'chevron' => 'text-white/60',
                'kbd' => 'dark',
                'logo' => config('tall-ui-kit.brand.logo_dark'),
                'logoDarkScheme' => null,
            ];
        }

        return [
            'surface' => $variant === 'inset' ? 'bg-page-muted' : 'bg-panel-footer',
            'line' => 'border-line',
            'label' => 'text-ink-muted',
            'item' => 'text-ink-muted hover:bg-ink/[0.04] hover:text-ink',
            'itemActive' => 'bg-ink/[0.06] text-ink font-medium ring-1 ring-inset ring-control-hover',
            'itemDisabled' => 'text-ink-muted cursor-not-allowed',
            'icon' => 'text-ink-muted group-hover/nav:text-ink',
            'iconActive' => 'text-ink',
            'iconDisabled' => 'text-ink-muted',
            'count' => 'text-ink-muted',
            'tag' => 'border-control text-ink',
            'control' => 'text-ink-muted hover:bg-ink/[0.04] hover:text-ink',
            'search' => 'border-control bg-surface text-ink-muted hover:border-control-hover hover:text-ink',
            'name' => 'text-ink',
            'meta' => 'text-ink-muted',
            'chevron' => 'text-ink-muted',
            'kbd' => 'light',
            'logo' => config('tall-ui-kit.brand.logo_light'),
            // In the dark colour scheme a light or inset sidebar is dark too.
            'logoDarkScheme' => config('tall-ui-kit.brand.logo_dark'),
        ];
    }

    /**
     * Whether a nav href is the current page. Hrefs listed in
     * config('tall-ui-kit.sidebar.exact') (area roots such as /admin) match exactly;
     * anything else also matches its sub-pages.
     */
    public static function isActive(string $href): bool
    {
        $path = ltrim($href, '/');

        if ($href === '/' || in_array($href, (array) config('tall-ui-kit.sidebar.exact', []), true)) {
            return request()->is($path === '' ? '/' : $path);
        }

        return request()->is($path, $path.'/*');
    }
}
