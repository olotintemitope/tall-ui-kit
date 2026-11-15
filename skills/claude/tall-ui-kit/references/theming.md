# Theming

The kit's CSS is split in two:

- `resources/css/tall-ui-kit.css`: structure. Variants (`state-hover:`, `rail:`, `focus-mode:`), the type scale and `type-*` roles, elevation shadows, motion tokens, the base layer and every component layer. No colours, no fonts.
- `resources/css/themes/<theme>.css`: colours and fonts, as Tailwind 4 `@theme` tokens, plus the chart tokens in `@theme static` (always emitted, because charts read them through `var()` at runtime).

Import Tailwind, the kit, then **exactly one** theme. Switching themes is changing that one line; each theme carries its light and dark schemes ([Dark mode](#dark-mode)):

```css
@import 'tailwindcss';
@import '../../vendor/olotintemitope/tall-ui-kit/resources/css/tall-ui-kit.css';
@import '../../vendor/olotintemitope/tall-ui-kit/resources/css/themes/neutral.css';
/* @import '../../vendor/olotintemitope/tall-ui-kit/resources/css/themes/newbanger.css'; */
```

## Shipped themes

| Theme | Accent | Fonts | Lowest text pair (light / dark) | Lowest non-text pair (light / dark) |
|---|---|---|---|---|
| `neutral` (default) | Muted slate: `primary` `#AFC0D1` fill carrying `on-primary` text (8.77:1), `primary-text` `#3D5A78` (7.16:1; `#A9BED3` in dark), focus `#5B7A99` (`#6383A1` in dark) | System UI stack, nothing to load | 4.77 / 4.66:1 | 3.29 / 3.21:1 |
| `newbanger` | `#FF614D` fill carrying `on-primary` text (5.32:1), `primary-text` `#BC4530` (5.21:1; `#FF614D` itself in dark, 5.44:1 at its lowest), focus `#DB4C37` (`#E0563F` in dark) | Space Grotesk (titles, figures), Inter (everything else); load them in your layout | 4.62 / 4.66:1 | 3.16 / 3.04:1 |

Run `composer contrast` to print all 190 measured pairs for each theme in both colour schemes (four sets), `composer contrast neutral` for one theme, or `composer contrast neutral:dark` for one set. Every text pair must be at least 4.5:1 and every boundary, mark and icon at least 3:1, in every state, on every surface they sit on (the page, panels, popovers, the tinted page, the panel footer, the dark sidebar, hover and pressed fills, status tints). The script exits 1 on any failure, or when a light colour token has no dark value; `tests/Feature/UiKitContrastTest.php` runs the same checks in `composer test`.

## Tokens a theme must define

Every theme defines the same colour tokens (a test compares them), and every one of them again in its dark block (a second test diffs the two lists):

| Token | Role |
|---|---|
| `surface` | The page: data pages, inputs, table cells, the sticky action bars. White in light |
| `card`, `card-foreground` | Panels and cards (`x-ui.panel`, `x-ui.card`, skeleton cards). White in light, one step above the page in dark |
| `popover`, `popover-foreground` | Menus, listboxes, popovers, dialogs, sheets, chart tooltips, the date picker popup. White in light, two steps above the page in dark |
| `primary` | The one accent: the primary button fill. It carries `on-primary` text |
| `primary-hover` | The primary button's edge (3:1 on the page) |
| `primary-fill-hover`, `primary-fill-active` | Hovered and pressed primary fills (`on-primary` on them 4.5:1) |
| `primary-text` | Accent text and links (4.5:1 on every surface and on `primary/10`) |
| `on-primary` | Text and spinners on the primary fill (`ink` in light) |
| `secondary`, `secondary-light`, `neutral-900` | The strong fill: secondary button, checked controls, chosen days, the slider range, tooltips. Near-black in light, near-white in dark |
| `on-secondary` | Text, check marks and radio dots on `secondary` (white in light, near-black in dark) |
| `sidebar` | The dark sidebar's surface: `secondary` in light, a dark grey in dark (it never inverts) |
| `ink`, `ink-body`, `ink-muted` | Text: titles, body, meta (ink-muted 4.5:1 on every surface, `page-muted`, `panel-footer` and `surface-subtle`) |
| `line`, `line-strong` | Decorative rules and tag edges (no contrast requirement; never a control border) |
| `surface-subtle`, `surface-muted` | Hover and pressed fills, disabled fill, neutral badge, progress tracks |
| `page-muted`, `panel-border`, `panel-footer` | Elevation: tinted page, panel and popover borders, footers and table headers |
| `control`, `control-hover` | Borders of every input, checkbox, radio, switch, outline button and segmented edge (3:1 on every surface and on `surface-subtle`) |
| `focus` | The focus outline (3:1 on every surface, on `secondary` and on the sidebar) |
| `scrim` | Behind modals and the native `::backdrop` (no blur) |
| `skeleton` | Skeleton bars |
| `danger`, `danger-hover`, `danger-active`, `on-danger` | The destructive button and invalid field edges |
| `danger-text`, `success-text`, `warning-text`, `info-text` | Status text and marks on the page: field errors, deltas, stepper error, progress and meter tones, info badge |
| `danger-strong`, `success-strong`, `warning-strong`, `info-strong` | Status text on its tint: alerts, toasts, banners, success badge |
| `danger-tint`, `success-tint`, `warning-tint`, `info-tint` | Status fills (alerts, toasts, banners, badges, the destructive menu item hover) |
| `danger-edge`, `success-edge`, `warning-edge`, `info-edge` | The 1px edges of tinted notices (decorative) |
| `chart-accent`, `chart-1`, `chart-2`, `chart-3` | Series colours, each 3:1 on the page and on a card (`chart-2` also on `surface-muted`) |
| `chart-grid`, `chart-axis`, `chart-tick` | Gridlines, axis, tick text (`chart-tick` 4.5:1) |

Fonts: `--font-sans`, `--font-heading` (titles and figures), `--font-body`.

Status tones used to be Tailwind's red, amber, emerald and blue at fixed steps. They are tokens now, with the same light values in both themes (Tailwind 4's rendering of red-600/700/800/50/200, emerald-700/800/50/200, amber-800/900/50/200, blue-700/800/50/200), so dark mode can give them dark tints and lighter text.

## Dark mode

Each theme file carries both schemes: the light tokens in its `@theme` blocks, and a dark block at the end that overrides every colour token (plus the shadows) through the kit's `dark` variant:

```css
@layer theme {
    :root {
        @variant dark {
            color-scheme: dark;
            --color-surface: #111213;
            /* ...every colour token again... */
            --shadow-panel: none;
        }
    }
}
```

**How the scheme is chosen.** `html[data-color-scheme]` is `light`, `dark` or `system`. `system` (and a page with no attribute) follows `prefers-color-scheme`. The `dark` custom variant in `tall-ui-kit.css` matches `[data-color-scheme="dark"]`, or `system` while the OS asks for dark, on screen only: print, and with it "Save as PDF", always uses light. Apps may use `dark:` for their own rare exceptions; kit components never need it, because every colour is a token.

**No flash.** `x-ui.head` sets the attribute in an inline script before the stylesheet, so the first paint is already in the right scheme. Put it in `<head>` before the CSS link. The order it checks: its `scheme` prop (a value the server decided), the bound `ColorSchemeStore` when `persist` is `server`, `localStorage` `<prefix>color_scheme`, then `config('tall-ui-kit.color_scheme.default')` (`system`). Values outside `schemes` are ignored.

**The toggle.** `x-ui.theme-toggle` is a Light / Dark / System segmented control (sun, moon, monitor) with the `x-ui.segmented` look: a radiogroup with an accessible name (`label`, "Colour scheme"), arrow keys that move and choose, one tab stop, the kit focus ring, and a polite live region that announces the change ("Colour scheme: Dark", or "System (Dark)"). It changes the scheme at once, writes `localStorage` (other tabs follow through the `storage` event) and dispatches `ui-color-scheme` on `window`. `iconOnly` keeps the words as accessible names for tight headers. In JS: `window.uiColorScheme.get()`, `.set('dark')`, `.resolved()`.

**Config** (`config/tall-ui-kit.php`):

```php
'color_scheme' => [
    'default' => env('TALL_UI_COLOR_SCHEME', 'system'), // light | dark | system
    'schemes' => ['light', 'dark', 'system'],            // ['light'] keeps the app light: stored choices ignored, no toggle
    'persist' => 'local',                                // or 'server'
],
```

**Server persistence** (the `DismissStore` pattern). With `persist` set to `server`, use `TallUiKit\Livewire\Concerns\WithColorScheme` on the Livewire component around the toggle: the toggle calls `setColorScheme()`, which checks the value (422 otherwise) and writes the bound `TallUiKit\Support\ColorScheme\ColorSchemeStore`; `x-ui.head` prints the stored value on the next page and copies it to `localStorage`. The kit binds a session store; bind your own to keep it per user:

```php
$this->app->bind(ColorSchemeStore::class, UserColorSchemeStore::class); // get(): ?string, put(string): void
```

**What changes in dark.** The page is a calm near-black (neutral `#111213`, NewBanger `#121312`, never `#000`); panels (`card`) and popovers one and two steps lighter, so elevation reads from the surface; 1px borders carry every edge (`shadow-panel`, `shadow-thumb` and `shadow-bar` are off, popover and dialog shadows are faint black). `secondary` turns near-white, so checked controls, chosen days, the slider range and tooltips still stand out, with `on-secondary` marks; the dark sidebar keeps its own `sidebar` surface and gains a 1px `line` edge so it never melts into the page. Status tints are dark and their text light. Scrollbars, form controls and `::backdrop` follow through `color-scheme: dark` and `scrim`. No glow, gradient or neon anywhere.

**Lowest measured pairs in dark:** neutral text 4.66:1 (white on the destructive fill), non-text 3.21:1 (the focus outline beside a chosen day); NewBanger text 4.66:1 (same), non-text 3.04:1 (same).

**Always light:**

- **Email.** Mail clients ignore the kit's CSS and most ignore `prefers-color-scheme`; build emails with inline light colours (the light hex values in the theme file) and never rely on tokens.
- **PDFs and printed pages.** The dark variant is screen only, so printing a page uses light tokens. Server PDFs (Dompdf in NewBanger's export pipeline, [exports](exports.md)) render their own light stylesheet; keep them light.
- **Third-party embeds.** Spotify, YouTube and Apple Music players inside `x-ui.embed` keep the provider's own look; the facade around them follows the scheme.

**Logos and images.** `brand.logo_light` shows on light grounds, `brand.logo_dark` on dark ones: the dark sidebar, and in the dark scheme the light and inset sidebars and `x-ui.logo` too (both images are printed and CSS shows one, so switching needs no reload). Photos and avatars are untouched.

**Your own theme.** Copy a shipped theme including its dark block. A theme with no dark block still works: it stays light in every scheme (`composer contrast` says "light only"), but then set `schemes` to `['light']` so no toggle is offered.

### Enabling it in NewBanger (later)

NewBanger still carries its in-repo copy of the kit, so nothing changes there until it opts in:

1. Move to the package (or copy `themes/newbanger.css` with its dark block, `tall-ui-kit.css` with the `dark` variant, `color-scheme.js`, `head.blade.php`, `theme-toggle.blade.php` and `logo.blade.php`).
2. Replace raw colours in app views the same way the kit did (`bg-white` to `bg-surface`, `bg-card` or `bg-popover`; `text-white` on dark fills to `text-on-secondary`; Tailwind status colours to the status tokens), and extend `AdminComponentsTest` to grep for them.
3. Put `<x-ui.head />` before the stylesheet in every layout, set `brand.logo_dark`, and add `<x-ui.theme-toggle />` to the account menu or settings. Start with `'schemes' => ['light']` to ship the code dark-off, then open it up.
4. Optional: bind a `ColorSchemeStore` on the users table and set `persist` to `server`.
5. Keep emails and Dompdf exports light; run `composer contrast` and the axe audit in both schemes.

## Writing your own theme

1. Copy `themes/neutral.css` into your app and change the values, light and dark.
2. Measure it: `php vendor/olotintemitope/tall-ui-kit/scripts/contrast.php /absolute/path/to/your-theme.css`, from a checkout of the package with its dev dependencies, or copy the script. Fix every failing pair.
3. Import your file instead of a shipped theme.

## Brand slots

`config/tall-ui-kit.php` (`php artisan vendor:publish --tag=tall-ui-kit-config`):

```php
'brand' => [
    'name' => env('APP_NAME'),
    'logo_light' => '/images/logo-black.png', // on light grounds
    'logo_dark' => '/images/logo-white.png',  // on the dark sidebar and everywhere in the dark scheme
],
```

With no logo the brand name prints as text in the heading role. `x-ui.sidebar.header` also takes a `logo` slot for an inline SVG (draw it in `currentColor` and it follows the scheme). `x-ui.logo` (`href`, `height`) prints the right logo outside the sidebar.

## Storage keys

The kit keeps a few choices in the browser (colour scheme, sidebar collapse, focus mode, privacy mode, wizard drafts) under `config('tall-ui-kit.storage_prefix')` (default `tallui_`). `x-ui.head` prints the prefix on `<html>`; an app that already has keys can keep them by setting the prefix (and `privacyKey`) to match.
