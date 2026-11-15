# UI kit: icons and copy to clipboard

Companion to [the components index](components.md). Gallery: the workbench gallery, section "Copy to clipboard"
(`resources/views/livewirethe workbench gallery-gallery/copy.blade.php`).

## Icons

`x-ui.icon` draws Lucide icons from `mallardduck/blade-lucide-icons`.

```blade
<x-ui.icon name="chart-line" />
<x-ui.icon name="check" stroke-width="1.5" class="size-4 text-ink-muted" />
```

- API unchanged: `name`, `strokeWidth` (2), other attributes (class, `wire:*`, `x-*`) pass through. `aria-hidden="true"` by default: the control or text beside the icon carries the name; icon-only buttons have `aria-label`.
- Output: one inline `<svg>` (24px box, sized by class), `stroke="currentColor"` and the given `stroke-width`. `TallUiKit\Support\LucideIcons::inner()` takes the package file's child elements and drops its own `<svg>` wrapper, so the root is always the kit's and no attribute is duplicated.
- Names are the React app's (`lucide-react@0.436.0`). Use any Lucide name; `tests/Feature/UiKitIconsTest.php` scans the kit's views, `src` and the workbench for `x-ui.icon` names (literals and the quoted literals in `:name` expressions), `icon="..."` props, `'icon' => '...'` options and `->icon('...')` actions, and fails on any name that does not resolve. An unknown name throws in local and testing and draws nothing in production.

### Version

The package is pinned at `1.23.0`, which ships Lucide `0.412.0`. Checked node for node against `lucide-react@0.436.0`: every icon the Blade views use is identical except the two chart icons below. The next release, `1.24.0`, jumps to Lucide `0.553.0`, where 27 of the icons in use have different elements or path data (search, settings, bell, users and others), so do not upgrade casually: rerun the comparison (each used icon's elements against the React app's) and update this section.

Lucide renamed its chart icons between 0.412 and 0.436 (`bar-chart-*` and `line-chart` became `chart-*`). Views use the React names; `LucideIcons::ALIASES` maps them to the package's files:

| React name | Package file | Drawing |
|---|---|---|
| `chart-line` | `line-chart` | Same lines; the axis corner is square (0.436 rounds it by 2px) |
| `chart-column` | `bar-chart-3` | Same bars; the axis corner is square |
| `chart-no-axes-column` | `bar-chart-2` | Identical |
| `chart-no-axes-column-increasing` | `bar-chart` | Identical |
| `chart-no-axes-gantt` | `gantt-chart` | Identical |
| `file-chart-column` | `file-bar-chart-2` | Identical |
| `file-chart-column-increasing` | `file-bar-chart` | Identical |
| `file-chart-line` | `file-line-chart` | Identical |
| `square-chart-gantt` | `gantt-chart-square` | Identical |

Not in 0.412 at all (none used yet; add them by upgrading, not by copying paths): the other `chart-*` icons (`chart-bar`, `chart-pie`, `chart-area`, `chart-spline`, ...), `map-pin-*` variants, `tickets`, `id-card`, `microchip`, `binoculars` and a few more.

### Performance and production

- `config/blade-icons.php` turns off the package's per-icon Blade components (`<x-lucide-*>`, about 1,650 registrations per request). Use `x-ui.icon`.
- Each icon's markup is read once per request and kept in memory (`LucideIcons::inner`).
- Production: `php artisan icons:cache` writes the icon manifest to `bootstrap/cache/blade-icons.php` (git-ignored); `php artisan icons:clear` removes it. `php artisan optimize` runs `icons:cache` too (blade-icons registers it), so the deploy step in `docs/tall-migration-plan.md` 5.2 covers it; run it on its own where `optimize` is skipped.

## Copy to clipboard

Three components on one Alpine behaviour, `uiCopy` in `resources/js/ui.js`.

```blade
<x-ui.copy-button value="NB-CMP-048217" />
<x-ui.copy-button value="NB-CMP-048217" variant="icon" size="sm" label="Copy campaign reference" />
<x-ui.copy-button :value="$link" variant="link" label="Copy link" />

<x-ui.field label="Referral link" name="referral">
    <x-ui.copy-field :text="$link" />
</x-ui.field>
<x-ui.copy-field text="whsec_••••3f9a" :value="$secret" label="Webhook secret" mono icon-only />

<x-ui.copy-text label="Copy reference" mono>{{ $payout->reference }}</x-ui.copy-text>
```

| Component | Use | Look |
|---|---|---|
| `x-ui.copy-button` | A standalone action | `label`: outline button, icon and label. `icon`: ghost icon button, label as `aria-label` and tooltip. `link`: link style with the icon. Sizes as `x-ui.button`: default 36px, `sm` 32px |
| `x-ui.copy-field` | Keys, links, secrets | Read-only input and a joined button inside one 1px `control` border, divided by a 1px `control` rule; inset focus ring on the button; the input selects its text on focus |
| `x-ui.copy-text` | A value inside text or a table cell | The value, then a 24px icon button that shows on hover of the value, on keyboard focus, while a state shows, and always on touch screens. Always in the tab order; its space is always kept. The value wraps (long references break anywhere). Lean markup for dense rows (the `.ui-copy-*` classes in `tall-ui-kit.css`, about 1.2 KB per instance, down from 3.2 KB); its tooltip (the label, "Copied" or "Press ⌘C to copy") is anchored `fixed`, so a scrolling table never clips it |

### States

| State | Icon | Label | Colour | Announced |
|---|---|---|---|---|
| Idle | copy | "Copy" (or `label`) | ink (label), ink-muted icon buttons (5.86:1), primary-text links (5.21:1) | |
| Copied, 2s | check, cross-faded over 150ms (opacity only) | "Copied" | success-text, 5.48:1 on white | "Copied to clipboard" (polite) |
| Failed | copy | stays "Copy"; a hint attached below the button reads "Press ⌘C to copy" (Ctrl+C off a Mac) | as idle; the hint is white on ink (15.9:1) | "Copy failed. Press ⌘C to copy." (polite); the button is `aria-describedby` the hint while it shows |

- A repeat click restarts the 2s timer and announces again (the live region is cleared first).
- Failed: the Clipboard API is missing (no secure context) or the write is refused. The value is put in a visually hidden, `aria-hidden` span and selected, so the shortcut works while focus stays on the button; in `x-ui.copy-field` the input's own text is selected too. The state stays until focus leaves the component.
- No width shift: "Copy" and "Copied" sit in one grid cell (`inline-grid`, `[grid-area:1/1]`, the hidden one `invisible`), so the button is only as wide as "Copied" (the old third label, "Press ⌘C to copy", reserved about 166px). The failure hint (`data-copy-hint`) is a tooltip-style bubble anchored with `x-anchor.bottom-start.fixed`, outside the flow, so it never moves the layout and is not clipped by the field's `overflow-hidden` border. Icon-only buttons already carry the guidance in their tooltip. The icons share one 16px cell the same way.
- Focus stays on the button in every state. Reduced motion: the cross-fade is instant (`motion-reduce:transition-none`).
- No toast by default. `toast` adds a "Copied to clipboard." toast, for copies started from a menu that closes (the button's own feedback is gone with the menu).

### What is copied

Only the visible value. `x-ui.copy-text` copies its rendered text and `x-ui.copy-field` its `text`, so a masked value (`•••• 4821`) copies the mask. A page that wants the full value copied passes `value` explicitly, after its own permission check: the value is then in the page's markup. Do not wrap privacy-mode money (`x-ui.money`) in `x-ui.copy-text`.

### Gallery states

`data-force="copied"` or `data-force="failed"` on any of the three draws that state and turns copying off; `data-force="hover"` or `"focus"` on `x-ui.copy-button` draws the pointer state, as on `x-ui.button`.

### Tests

`tests/Feature/UiKitCopyTest.php`: markup, the live region, the failure fallback span, the reserved-width label grid, sizes, forced states, value and mask rules, the toast option, the gallery section. The clipboard itself is checked in the browser.
