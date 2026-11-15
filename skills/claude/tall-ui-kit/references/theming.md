# Theming

The kit's CSS is split in two:

- `resources/css/tall-ui-kit.css`: structure. Variants (`state-hover:`, `rail:`, `focus-mode:`), the type scale and `type-*` roles, elevation shadows, motion tokens, the base layer and every component layer. No colours, no fonts.
- `resources/css/themes/<theme>.css`: colours and fonts, as Tailwind 4 `@theme` tokens, plus the chart tokens in `@theme static` (always emitted, because charts read them through `var()` at runtime).

Import Tailwind, the kit, then **exactly one** theme. Switching themes is changing that one line:

```css
@import 'tailwindcss';
@import '../../vendor/olotintemitope/tall-ui-kit/resources/css/tall-ui-kit.css';
@import '../../vendor/olotintemitope/tall-ui-kit/resources/css/themes/neutral.css';
/* @import '../../vendor/olotintemitope/tall-ui-kit/resources/css/themes/newbanger.css'; */
```

## Shipped themes

| Theme | Accent | Fonts | Lowest text pair | Lowest non-text pair |
|---|---|---|---|---|
| `neutral` (default) | Muted slate: `primary` `#AFC0D1` fill carrying ink text (8.77:1), `primary-text` `#3D5A78` (7.16:1), focus `#5B7A99` (4.48:1 on white, 3.64:1 on the dark sidebar) | System UI stack, nothing to load | 4.77:1 | 3.29:1 |
| `newbanger` | `#FF614D` fill carrying ink text (5.32:1), `primary-text` `#BC4530` (5.21:1), focus `#DB4C37` | Space Grotesk (titles, figures), Inter (everything else); load them in your layout | 4.62:1 | 3.16:1 |

Run `composer contrast` (or `composer contrast neutral`) to print all 86 measured pairs per theme: every text pair must be at least 4.5:1 and every boundary, mark and icon at least 3:1, in every state, on every surface they sit on (white, the tinted page, the panel footer, the dark sidebar, hover and pressed fills). The script exits 1 on any failure; `tests/Feature/UiKitContrastTest.php` runs the same check in `composer test`.

## Tokens a theme must define

Every theme defines the same colour tokens (a test compares them):

| Token | Role |
|---|---|
| `primary` | The one accent: the primary button fill. It carries `ink` text, so it must be light enough for 4.5:1 |
| `primary-hover` | The primary button's edge (3:1 on white, because a light fill alone is under 3:1) |
| `primary-fill-hover`, `primary-fill-active` | Hovered and pressed primary fills (ink on them 4.5:1) |
| `primary-text` | Accent text and links (4.5:1 on white and on `primary/10`) |
| `secondary`, `secondary-light`, `neutral-900` | Dark fill: secondary button (white text), checked controls, the dark sidebar, pressed secondary |
| `ink`, `ink-body`, `ink-muted` | Text: titles, body, meta (ink-muted 4.5:1 on white, `page-muted`, `panel-footer` and `surface-subtle`) |
| `line`, `line-strong` | Decorative rules and tag edges (no contrast requirement; never a control border) |
| `surface-subtle`, `surface-muted` | Hover and pressed fills, disabled fill, neutral badge |
| `page-muted`, `panel-border`, `panel-footer` | Elevation: tinted page, panel and popover borders, footers and table headers |
| `control`, `control-hover` | Borders of every input, checkbox, radio, switch, outline button and segmented edge (3:1 on white and on `surface-subtle`) |
| `focus` | The focus outline (3:1 on white and on `secondary`) |
| `scrim` | Behind modals (no blur) |
| `card`, `card-foreground`, `popover`, `popover-foreground` | Older shadcn-style names a few class strings use |
| `chart-accent`, `chart-1`, `chart-2`, `chart-3` | Series colours, each 3:1 on white (`chart-2` also on `surface-muted`) |
| `chart-grid`, `chart-axis`, `chart-tick` | Gridlines, axis, tick text (`chart-tick` 4.5:1) |

Fonts: `--font-sans`, `--font-heading` (titles and figures), `--font-body`.

Status colours (danger, warning, success, info) are Tailwind's red, amber, emerald and blue at fixed steps and are the same in every theme.

## Writing your own theme

1. Copy `themes/neutral.css` into your app and change the values.
2. Measure it: `php vendor/olotintemitope/tall-ui-kit/scripts/contrast.php /absolute/path/to/your-theme.css`, from a checkout of the package with its dev dependencies, or copy the script. Fix every failing pair.
3. Import your file instead of a shipped theme.

## Brand slots

`config/tall-ui-kit.php` (`php artisan vendor:publish --tag=tall-ui-kit-config`):

```php
'brand' => [
    'name' => env('APP_NAME'),
    'logo_light' => '/images/logo-black.png', // on light sidebars
    'logo_dark' => '/images/logo-white.png',  // on the dark sidebar
],
```

With no logo the brand name prints as text in the heading role. `x-ui.sidebar.header` also takes a `logo` slot for an inline SVG.

## Storage keys

The kit keeps a few choices in the browser (sidebar collapse, focus mode, privacy mode, wizard drafts) under `config('tall-ui-kit.storage_prefix')` (default `tallui_`). `x-ui.head` prints the prefix on `<html>`; an app that already has keys can keep them by setting the prefix (and `privacyKey`) to match.
