# UI kit: HTML5 and media elements

Companion to [the components index](components.md). Native elements first, styled with kit tokens; behaviour that HTML cannot give lives in `resources/js/forms.js` (imported by `resources/js/ui.js`). Gallery: the workbench gallery, sections "Native inputs", "Disclosure, popover, progress and meter", "Text and structure" and "Media", each with a properties table. Tests: `tests/Feature/HtmlElementsTest.php`, `tests/Unit/EmbedTest.php`.

Every control is `h-9` (36px), or `h-8` with `size="sm"`, has a 1px `control` border (3.65:1), the 2px focus treatment, and the kit's disabled look (dashed `control` border on `surface-subtle`, `ink-muted` text, never opacity; `tests/Feature/AdminComponentsTest.php` rejects `opacity-` behind any disabled variant).

## Browser defaults removed (`tall-ui-kit.css`, base layer)

| Default | Kit |
|---|---|
| Autofill yellow | `:autofill` and `:-webkit-autofill` (separate rules) paint white with an inset shadow; text and caret ink; the border stays the control's |
| Number spin buttons | hidden (`::-webkit-inner-spin-button`, `appearance: textfield`); use `x-ui.number-input` |
| Search cancel button | hidden; `x-ui.search` has its own clear button |
| Date and time picker icon | the WebKit indicator is a Lucide calendar or clock mask in `ink-muted` (5.86:1); Firefox and Safari keep their own button |
| UA accent blue | `accent-color: ink` on `:root` |
| Selection | ink on `primary` at 25% (12.05:1) |

## States

| State | How |
|---|---|
| Client invalid | `:user-invalid` on the control turns its `[data-control-box]` border `danger`, only after the user edits or submits. Fallback for every browser and for screen readers: on blur, a kit control that fails its own constraints (`type=email`, `url`, `pattern`, `min`/`max`) gets `aria-invalid="true"` (`data-client-invalid`), removed once valid. Server errors (`aria-invalid` from `$errors`) are never removed by it |
| Read-only | `[data-control-box]` with a read-only kit input: `panel-footer` fill, default cursor, border unchanged. `x-ui.copy-field` stays white |
| Print | data pages print without the sidebar, top bar, progress bar, toasts or anything with `data-print-hide`; links in `main` print their address after the text (`data-print-no-url` opts out); table headers repeat; panels lose their shadow; privacy-mode money stays blurred |

## Inputs

| Component | Props | Notes |
|---|---|---|
| `x-ui.input type="email\|url\|tel\|search\|number"` | as `x-ui.input` | Adds `inputmode`, `autocomplete`, `autocapitalize="none"` and `spellcheck="false"` per type; any attribute passed wins |
| `x-ui.tel` | `country` ("+234"), `size`, `width`, `invalid` | The code sits inside the border as a prefix and is read as a description ("Country code +234"); `autocomplete` becomes `tel-national` |
| `x-ui.password` | `autocomplete` (current-password), `strength` (false), `size`, `width` (full) | Show/hide is a button with `aria-pressed` and the fixed name "Show password". `strength` adds a polite caption: "Too short", "Weak", "Fair", "Strong password." Text only, never blocks submit. Promoted from `x-auth.password-input`, which is now an alias |
| `x-ui.otp` | `length` (6), `label`, `id`, `error` | Fieldset and legend; one string through `x-modelable`; paste and one-time-code autofill spread across the boxes; `otp-complete` event. Promoted from `x-auth.otp-input` (alias). The auth pages use the kit names |
| `x-ui.number-input` | `min`, `max`, `step` (1), `format` (number, money, percent), `currency`, `decimals`, `noun`, `size`, `width`, `invalid`, `label`, `value` | `role="spinbutton"` on a text input (so money can be grouped), two 32px buttons inside the border, each divided by a 1px `control` rule. Up/Down step, Page Up/Down x10, Home/End to the bounds; the buttons are `tabindex="-1"` (the keyboard steps on the field) and `aria-disabled` at a bound. Plain digits while editing, `₦150,000` after blur; clamped and rounded to `step`. Bind on the component |
| `x-ui.range` | `label`, `min`, `max`, `step`, `steps`, `dual`, `format`, `currency`, `unit`, `openEnded`, `value`, `minModel`, `maxModel`, `disabled`, `hideLabel`, `id` | Native ranges. Track 4px `control` (3.65:1), chosen part ink, thumb 24px (the target) with a 1px `control` edge and `shadow-thumb`; focus ring on the thumb. `<output>` shows the value; each thumb has `aria-valuetext`. `x-marketplace.range` is now this component with Livewire models |
| `x-ui.color` | `value`, `size`, `width`, `label`, `swatchLabel` | Native swatch (28px, opens the system picker) and a hex field; #RGB expands; a bad hex is `aria-invalid` until fixed |
| `x-ui.date`, `x-ui.time-input`, `x-ui.datetime`, `x-ui.month`, `x-ui.week`, `x-ui.date-range` | `size`, `width`, `invalid`, `min`, `max`, `step`, `disabledDates`, `weekStart`, `hours`, `minuteStep`, `presets` (range) | Custom pickers, not native inputs: a typed text field plus a popup below it, the same in every browser. Values as the native inputs posted (Y-m-d, H:i, Y-m, 2026-W47, Y-m-d\TH:i). See [forms.md](forms.md), Dates and times |
| `x-ui.combobox` | `options` (value => label, or `value`, `label`, `hint`, `disabled`), `value`, `placeholder`, `allowCustom`, `empty`, `native`, `size`, `width`, `invalid`, `label` | ARIA 1.2 combobox: `role="combobox"` input, `aria-expanded`, `aria-controls`, `aria-activedescendant`; `role="listbox"` popup named by the field label. Type-ahead filter with a polite count; Down/Up, Alt+Down, Enter, Escape (twice clears), Tab keeps the choice. `native` renders `<input list>` and `<datalist>` for free text with suggestions where the browser's unstyled popup is acceptable |
| `x-ui.checkbox indeterminate` | | The native mixed state (announced as "mixed"), an ink box with a white dash; bind `x-effect="$el.indeterminate = ..."` for a live value |
| `x-ui.fieldset` | `legend`, `description`, `size` (field, subheading), `hideLegend`, `disabled`, `id` | No box: the legend and 16px spacing group the controls; the description is `aria-describedby` |
| `x-ui.output` | `for`, `label`, `size` (default, figure), `name` | A computed value, polite |
| `x-ui.select multiple` | `multiple`, `rows` (kept, unused) | A custom multi-select listbox with checkmarks (see docs/, Select); a string key whose value is an array is a labelled group; `selected` may be a list; one hidden input per value |
| `x-ui.file variant="images"` | `variant` (list, images) | Thumbnails (square, 8px radius, file name as alt) in a 3 or 4 column grid with a 24px Remove button that rebuilds the input's files and fires `change` (so `wire:model` uploads follow); focus goes to the next Remove, else the input |

## Interactive

| Component | Props | Notes |
|---|---|---|
| `x-ui.disclosure` | `summary`, `open`, `meta`, `bordered`, `name` | `<details>`/`<summary>`: works without JavaScript, find-in-page opens it. With Alpine the summary click is intercepted so `x-collapse` animates the height (150ms; instant under reduced motion) and the chevron turns 90°. Focus stays on the summary |
| `x-ui.accordion`, `.item` | `exclusive`, `name`, `label`; item `summary`, `open`, `meta` | Items divided by 1px `line` rules. `exclusive` closes the others (give each exclusive accordion a unique `name`) |
| `x-ui.popover` | `label`, `title`, `placement`, `width`; slot `trigger` | See the decision below |
| `x-ui.progress` | `label`, `value`, `max`, `valueText`, `hideLabel`, `id`; `size`, `tone`, `labelPosition`, `format`, `buffer` ([workflow](workflow.md) "Progress") | Native `<progress>` for the semantics, drawn as a `surface-muted` track (4, 8 or 12px) with a fill that scales to the value (`chart-1` 15.8:1, 13.6:1 on the track; or a status tone), label and percent. No `value`: indeterminate, a 30% segment slides (transform only, no stripes); reduced motion holds it and pulses. Not the page-level `x-ui.progress-bar` |
| `x-ui.meter` | `label`, `value`, `min`, `max`, `low`, `high`, `optimum`, `format`, `valueText`, `words` | Native `<meter>`; the browser picks the bar: optimum `success-text`, next `warning-text`, far `danger-text` (5.36, 5.03, 6.42 on white; 4.62, 4.34, 5.53 on the track). The value and "Good", "Fair" or "Poor" are printed, so colour is never the only cue |

### Decision: Popover API

`x-ui.popover` uses the Popover API (`popover="auto"` and `popovertarget` on the trigger), in every browser we support since spring 2024 (Chrome 114, Safari 17, Firefox 125). It gives light dismiss, Escape, the top layer (never clipped by an `overflow` parent) and focus return for free; Alpine only anchors it (`x-anchor`, since CSS anchor positioning is not yet everywhere) and mirrors `aria-expanded`. Where the API is missing, `uiPopover` drops the attribute and shows the same panel with Alpine, handling Escape and a click outside. It fades with `@starting-style` and `allow-discrete`, like dialogs.

Kept on Alpine: `x-ui.dropdown` (menus need roving focus, typeahead and submenus, and a menu is not a popover role) and `x-ui.tooltip` (hover and focus hints: `popover="hint"` is Chromium only, and a tooltip never takes focus). Revisit both when `popover="hint"` and `interestfor` ship in Safari and Firefox.

## Text and structure

Styled once in the base layer, so they look right anywhere (and inside `x-ui.prose`): `mark` (ink on `primary` 18%, 13.03:1), `abbr[title]` (dotted `ink-muted` underline, help cursor), `small` (caption size), `sub`, `sup`, `data` (tabular), `address`, `cite` (ink, 500), `q` (curly quotes), `samp` and `var` (mono, ink), `bdi` (isolates right-to-left names), `wbr`. No italics anywhere (`cite`, `var`, `address`, `dfn`, `em` are upright).

| Component | Props | Notes |
|---|---|---|
| `x-ui.time` | `value`, `mode` (absolute, relative), `withTime`, `format`, `tz` (Africa/Lagos), `now` | Always `<time datetime="ISO 8601">`. Absolute "14 Nov 2026" (with time "14 Nov 2026, 10:00"); relative "Just now", "5m ago", "2h ago", "Yesterday", "3 days ago", "in 2h", "Tomorrow" (`Format::relative`), the full date in `title`. Null prints an en dash. Use it wherever a date renders; the notification item already prints a `<time>` the same way |
| `x-ui.dl`, `x-ui.dl.item` | `items`, `layout` (stacked, inline, grid), `dense`; item `label`, `numeric` | Labels in meta, values in body or numeric |
| `x-ui.breadcrumb` | `items` (`label`, `href`; the last is current), `label`, `collapse` | `<nav aria-label>` and `<ol>`; current page `aria-current="page"`; separators hidden from assistive tech; below `sm` only the parent and current page |
| `x-ui.table` | adds `captionVisible`, `sticky`, `maxHeight`; slots `colgroup`, `foot` | Visible caption in the label role; `<colgroup>`; `<tfoot>` totals over a 1px rule; the header row sticks inside the table's own scroll box (`max-h-[32rem]` by default: a sideways scroller cannot also stick to the page) |
| `x-ui.table.th` | adds `row`, `scope` | `row`: a body or foot row header (`scope="row"`, body-strong). `scope` overrides (colgroup, rowgroup) |

## Media

| Component | Props | Notes |
|---|---|---|
| `x-ui.audio` | `src`, `title`, `artist`, `duration`, `type`, `compact` | Song previews. `preload="none"`. 32px play/pause toggle (`aria-pressed`, "Play Second Wind"), title and artist, a seek bar on the kit range (`aria-valuetext` "0:12 of 0:30"), elapsed / total in numeric, mute toggle. One player at a time (`ui-audio-play` window event). Space or Enter on play toggles; Space on the seek bar too; Left/Right on play or the bar move 5s. Loading: the kit spinner in the button and `aria-busy`. Error: "Preview unavailable." and the button `aria-disabled`. No waveform |
| `x-ui.video` | `src`, `poster`, `type`, `aspect`, `title`, `preload`; slot `<track>` | Native controls, fixed aspect box (no shift), 8px radius, never autoplays |
| `x-ui.embed` | `url`, `title`, `artwork`, `autoload` | Click-to-load facade (nothing from the provider loads, no cookies, until "Load Spotify player"); the iframe then takes the facade's exact size and focus, with `title`, `loading="lazy"`, `referrerpolicy`, the provider's `allow` list and a `sandbox`. Links with no player render "Open on Audiomack" (new tab) |
| `x-ui.lightbox` | `name`, `src`, `thumb`, `alt`, `title`, `description`, `width`, `height`, `size` (96), `fullUrl` | A thumbnail button ("View <alt>", `object-cover`, lazy) that opens the full image in an `x-ui.dialog` (xl): `x-ui.figure` with fit contain at the image's aspect, capped to 55vh, "Open full size" and Close. Promoted from the support ticket attachments. `data-*` attributes go on the thumbnail button |
| `x-ui.figure` | `src`, `alt`, `decorative`, `caption`, `aspect`, `srcset`, `sizes`, `sources`, `width`, `height`, `eager`, `fit` | `<figure>` with `<picture>`: `sources` for WebP or AVIF first, `srcset`/`sizes` on the img; fixed aspect; `loading="lazy"` and `decoding="async"` (`eager` sets `fetchpriority="high"`). `alt` is required; empty only with `decorative` (throws in local and testing) |
| Prices | | Not in the kit: prices are app components (NewBanger: `x-pricing.price`); there is no `x-ui.price` |

### Embed providers (`TallUiKit\Support\Embed`)

Ported from the React resolvers (`src/components/ui/TrackEmbed.tsx`, `src/components/admin/SocialVideoEmbed.tsx`). The host is parsed and must be on `Embed::HOSTS` (exact host; no user info or port), so a URL that only mentions a provider in its path or query never produces an iframe.

| Provider | Hosts | Player |
|---|---|---|
| Spotify | `open.spotify.com` | `/embed/{track,album,playlist,episode,show,artist}/{id}`; 152px, 352px for collections |
| YouTube | `youtube.com`, `www.`, `m.`, `music.`, `youtu.be` | `youtube-nocookie.com/embed/{id}` from watch, short links, shorts, live; 16:9 |
| Apple Music | `music.apple.com` | `embed.music.apple.com` with the same path (only `?i=` kept); 175px song, 450px collection |
| SoundCloud | `soundcloud.com`, `www.`, `m.` | `w.soundcloud.com/player/?url=` the canonical track or set; 166px |
| TikTok | `tiktok.com`, `www.`, `m.`, `vm.` | `tiktok.com/embed/v2/{id}` from `/@user/video/{id}`; 9:16. `vm.` short links carry no id: link only |
| Audiomack | `audiomack.com`, `www.` | Link only. React links out too, and no official embed URL format could be verified |
| Boomplay | `boomplay.com`, `www.` | Link only, as React |

Instagram (also in `SocialVideoEmbed.tsx`) is not ported: no gallery or page needs it yet.

Sample media for the gallery: `public/samples` (a 12s tone, a 4s clip with captions, flat cover images), generated locally.
