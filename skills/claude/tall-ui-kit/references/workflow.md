# Workflow and progress components

Steppers, activity feeds, tag inputs, avatar groups, sortable lists, sliders, banners, trees and the progress family. Every one takes its content as data (or as child rows in the slot), knows nothing about the app's models or routes, and works with Livewire and Alpine. Gallery: workbench `/workflow`, both themes. Tests: `tests/Feature/WorkflowComponentsTest.php`. Contrast: the "Workflow" and "Progress" rows in `TallUiKit\Support\KitContrast` (`composer contrast`).

JavaScript: `resources/js/widgets.js` (imported by `index.js`) registers the tag input, banner, avatar group and upload list, and loads the slider, tree and sortable behaviour lazily (`slider-core.js` 1.4 KB, `sortable-core.js` 2.0 KB, `tree-core.js` 2.5 KB gzip, fetched only when a page has one). No dependency was added: sorting uses native pointer events.

| Need | Use |
|---|---|
| Steps the user sees by name, can go back to, or that can fail | `x-ui.stepper` |
| A compact "Step 3 of 5" with no step names needed | `x-ui.progress-steps` |
| One step per screen in a form | `x-ui.form-layout.wizard` (its header is `x-ui.stepper`) |
| Setup or onboarding tasks done in any order | `x-ui.checklist` |
| Progress of one task | `x-ui.progress`; where a bar does not fit, `x-ui.progress-ring` |
| A file upload | `x-ui.upload-progress` (one row), `x-ui.upload-progress.list` (Livewire uploads) |
| A value in a range, a native control is fine | `x-ui.range` (two native inputs; unchanged) |
| A value in a range with a custom look, money formatting, or two thumbs that must not cross | `x-ui.slider` |
| A message for the whole page that stays | `x-ui.banner`; for one form or section, `x-ui.alert`; for the result of an action, a toast |

## Stepper

`x-ui.stepper`: a `<nav>` (named by `label`) holding an `<ol>`. Steps are strings or `['label', 'description', 'state', 'href', 'action', 'key']`; `state` is complete, current, upcoming or error, and `current` (1-based) fills in states that are not given.

```blade
<x-ui.stepper label="Set up payouts" :current="3" clickable action="goTo" :steps="[
    ['label' => 'Account', 'description' => 'Email and password.'],
    ['label' => 'Bank account', 'state' => 'error', 'description' => 'The name did not match.'],
    ['label' => 'Review'],
    ['label' => 'Go live'],
]" />
```

| Prop | Meaning |
|---|---|
| `steps`, `current` | The steps; the current step (1-based) |
| `variant` | `default` (markers) or `inline` (the wizard's text list "1 Song · 2 Targets Done ·", "Step 2 of 5" below `sm`) |
| `orientation` | `horizontal`: a row once the stepper's own box is 42rem wide (a container query, so it follows the panel, not the window), stacked below that; `vertical`: always stacked |
| `clickable` | `false`; `true` (complete and error steps); `"all"` (every step but the current one) |
| `action` | Livewire method called with the step number (`goTo(2)`); a step's own `href` or `action` wins; without either, a bubbling `ui-step` event, detail `{ step, key }` |
| `label`, `id` | The nav's name ("Progress") |

**Look.** 24px markers: complete a 1px ink ring with an ink check, current an ink disc with a white number (15.81:1), upcoming a 1px `control` ring with an ink-muted number (3.65:1 and 5.85:1), error a 1px red-700 ring with an x, the label and description in red-700 (6.42:1). Labels in body (current in body-strong), descriptions in meta. A 1px `line` rule joins steps. No fills besides the current disc, no progress bar.

**Accessibility.** `aria-current="step"` on the current `<li>`; every label starts with a hidden "Completed:", "Current:", "Upcoming:" or "Error:", so state never rests on colour or the marker. Clickable steps are buttons or links with the focus ring; the rest are text.

Do: keep labels short and say what failed in the error step's description. Don't: make upcoming steps clickable when they depend on earlier ones; use it for unordered tasks (use `x-ui.checklist`).

## Timeline

`x-ui.timeline`: an activity feed. Data mode: `items` of `['key', 'time', 'actor', 'avatar', 'text', 'status', 'body', 'attachment']`, where `status` is a label or `[label, variant]` (an `x-ui.badge` variant) and `attachment` is `['name', 'href', 'meta']`.

```blade
<x-ui.timeline :items="$activity" group-by-day :limit="10" label="Payout activity" />
```

| Prop | Meaning |
|---|---|
| `items` | The entries, newest first |
| `groupByDay` | Day headings: "Today", "Yesterday", "14 Nov 2026" (in `tz`), one `<ol>` per day under an `h3` (`headingLevel`) |
| `limit` | Show the first N and a "Show N more" button (`moreLabel`, `:count` is replaced) that reveals the rest in place, no round trip; focus moves to the first new entry |
| `total`, `moreAction` | Server paging instead: an `x-ui.load-more` row calls the Livewire method |
| `timeMode` | `relative` ("2h ago", the full date in `title`) or `absolute` |
| `tz`, `now` | Time zone (Africa/Lagos); a fixed "now" for tests |
| `label`, `empty`, `id` | The list's name without day headings ("Activity"); "No activity yet." |

Slot mode: `x-ui.timeline.item` rows (props `time`, `actor`, `avatar`, `text`, `status`, `attachment`, `timeMode`, `tz`, `now`, `extra`; the slot is the body, e.g. a comment or a diff), optionally inside `x-ui.timeline.day label="Today"`. Mark rows `extra` and pass `limit` for "Show more".

**Look.** Flat: the actor's avatar (or a small `control` ring) on a 1px `line` rail, "**Actor** text" in body, the status pill on the same line (it drops below on narrow rows, never clipped), the time in meta, the body below. **Accessibility:** the time is `<time datetime>`; the reveal button has `aria-expanded` and `aria-controls`; hidden entries are `display: none` until revealed. One Alpine component per feed (only with `limit`), none per entry; give the feed a stable `label` (its id comes from it, so rows keep their `wire:key` when entries arrive).

Do: print what changed in the text ("approved the payout PAY-9F3C21AB"). Don't: use icons in coloured tiles as markers, or more than one pill per entry.

## Tag input

`x-ui.tag-input`: several short values in one field, bound as an array.

```blade
<x-ui.field label="Genres" name="genres" hint="Up to 5.">
    <x-ui.tag-input wire:model.live="genres" :value="$genres" :max="5" :max-length="24" :suggestions="$allGenres" />
</x-ui.field>
```

| Prop | Meaning |
|---|---|
| `wire:model` / `x-model` | The array (`x-modelable`); plain `wire:model` sends nothing until the next action, `.live` one request per change |
| `value` | The first paint (the binding arrives after Alpine starts) |
| `suggestions` | Strings or `['value', 'hint']`; the input becomes an APG combobox with a listbox below it |
| `max`, `maxLength` (50), `pattern`, `patternMessage`, `allowCustom` (true), `caseSensitive` (false) | Checked before a tag is added; the reason is said in a polite message under the field |
| `separator` (","), `addOnBlur` (true), `placeholder` ("Add a tag"), `size`, `width` (md), `invalid`, `label`, `name` (plain forms post `name[]`), `removeLabel` ("Remove"), `empty` ("No matches") | |

**Keyboard.** Enter or the separator adds; pasting "a, b, c" or one value per line adds each (one message: "Added 2 tags. 1 skipped: ..."); Backspace in the empty input removes the last tag; Left at the start of the input moves to the tags, Left and Right between them, Home and End to the ends, Backspace or Delete removes the focused tag, Right past the last or Escape returns to the input. With suggestions: Down and Up move, Enter picks, Escape closes. After a removal focus moves to the previous tag, else the next, else the input.

**Look.** The `x-ui.input` box with `surface-muted` chips (4px radius, never pills; ink 13.63:1, the x in ink-muted 3:1 or more), "3 of 5" in caption when `max` is set. Long tags wrap inside their chip. **Validate the array again on the server** (count, length, pattern): the browser check is a convenience. The gallery's `FieldsDemo::updatedGenres` does.

## Avatar group

`x-ui.avatar-group`: stacked `x-ui.avatar`s with a "+N" disc.

```blade
<x-ui.avatar-group :people="$reviewers->map(fn ($u) => ['name' => $u->name, 'src' => $u->photo_url])" :max="4" label="Reviewers" />
```

Props: `people` (names, or `['name', 'src']`), `max` (4; the "+N" disc replaces the last slot when two or more are hidden), `size` (xs, sm, default, lg; sm), `label` (prefix for the name), `focusable` (true), `id`.

**Accessibility.** One `role="img"` named after everyone ("Reviewers: Ada Obi, Bola Ade and 5 others" lists all of them), so screen readers hear every name. It takes focus, and hover or focus shows the same list as a tooltip (fixed, so scroll boxes never clip it; Escape hides it, WCAG 1.4.13). No Alpine per group: one delegated listener for the page (`avatar-group.js`), so a table full of groups costs nothing. Set `focusable` false when the group sits inside a link or button that names the people already.

## Sortable

`x-ui.sortable`: a list reordered by dragging or from the keyboard. The new order goes to Livewire in **one call**.

```blade
<x-ui.sortable label="Payment methods" action="reorder" :items="$methods" />
```

```php
public function reorder(array $keys): void
{
    // Keys come from the browser: accept only a permutation of the ones you rendered.
    abort_unless(collect($keys)->sort()->values()->all() === collect($this->order)->sort()->values()->all(), 422);
    $this->order = $keys;
}
```

Props: `items` (`['key', 'label', 'description']`, or `x-ui.sortable.item` rows in the slot: props `key`, `label`, `description`, `help`, `disabled`, slot for rich content), `action`, `label` (the list's name, "Reorder list"), `bordered` (true), `disabled`, `id`. Events: a bubbling `ui-sort` with `{ order, key, from, to }` on every change.

**Pointer and touch.** Press the 32px grip and drag; the row follows the pointer, the others slide aside (transform, `--motion-sm`), the window scrolls near its edges, and the row lands where it was dropped. The grip has `touch-action: none`, so a finger drag never scrolls the page; the rest of the row scrolls as usual. Escape during a drag puts it back.

**Keyboard.** Space or Enter on a grip picks the row up, Up and Down (Home, End) move it, Space or Enter drops it, Escape cancels, Tab drops it. A polite live region says each step ("Picked up Card. Position 1 of 5.", "Card moved to position 3 of 5.", "Card dropped. Position 3 of 5.", "Cancelled. Card is back at position 1 of 5."), and focus stays on the grip.

**Decision: no library.** SortableJS (MIT, about 13 KB gzip) and `@shopify/draggable` were considered; native pointer events cover mouse, pen and touch in about 2 KB, keyboard sorting needed custom code either way, and rows stay plain markup (no Alpine per row; one lazy module with delegated listeners serves every list). Keep long lists paginated; reorder within a page.

Do: give each row a stable `key` and keep `label` (it names the grip and the announcements). Don't: put the whole row under `touch-action: none`, or save after every step of a keyboard move (the kit sends once, on drop).

## Slider

`x-ui.slider`: a custom slider on the APG slider and multi-thumb slider patterns. `x-ui.range` stays as it is.

```blade
<x-ui.slider label="Budget" :min="5000" :max="100000" :step="2500" format="money" wire:model.live="budget" :value="$budget" bounds />
<x-ui.slider label="Followers" dual :min="0" :max="1000000" :step="10000" wire:model.live="followers" :value="$followers" />
```

| Prop | Meaning |
|---|---|
| `wire:model` / `x-model` | A number, or `[low, high]` with `dual`; committed on release and on each key press (one request per drag) |
| `min`, `max`, `step`, `pageStep` | Page Up/Down move `pageStep` (a tenth of the range by default) |
| `format`, `currency`, `decimals`, `unit` | `number`, `money` (NGN or USD, as `x-ui.money`), `percent`; the formatted value is printed and is each thumb's `aria-valuetext` |
| `value`, `bounds`, `disabled`, `hideLabel`, `name`, `minLabel`, `maxLabel`, `id` | First paint; min and max under the track; plain-form inputs (`name` or `name[]`) |

**Keyboard.** Right/Up +step, Left/Down -step, Page Up/Down, Home/End (to the lowest or highest the thumb may take). With two thumbs each one's `aria-valuemin`/`aria-valuemax` stops at the other's value, so they never cross. **Pointer.** Press anywhere on the track to move the nearest thumb there, then drag; `touch-action: none` on the track. **Look.** As `x-ui.range`: a 4px `control` track, the chosen part in ink, 20px white thumbs with a 1px `control` edge, the focus ring on the focused thumb; disabled: dashed track and thumbs, no opacity.

**Which to use.** `x-ui.range` is two native `<input type="range">` over one track: the browser's own keyboard, forms and assistive-tech support for free, uneven `steps` lists and the `minModel`/`maxModel` split binding. `x-ui.slider` is for a formatted value per thumb (money, units), an `[low, high]` array binding, two thumbs that must never cross, Page Up/Down steps you choose, and a click-anywhere track. Both commit once per drag.

## Banner

`x-ui.banner`: a page-level announcement in the page flow (top of `<main>`). Not a toast: it never takes focus, never stacks and stays until dismissed.

```blade
<x-ui.banner tone="warning" title="Payouts pause on 20 November." name="payouts-2026-11" dismissible
    action-label="Read the notice" action-href="{{ route('notices.show', $notice) }}">
    Requests made that day go out on 21 November.
</x-ui.banner>
```

| Prop | Meaning |
|---|---|
| `tone` | info (blue-700 on blue-50, 6.28:1), success (emerald-800 on emerald-50, 7.23:1), warning (amber-900 on amber-50, 8.73:1), danger (red-800 on red-50, 7.64:1) |
| `title`, slot, `actionLabel`, `actionHref` | Title in body-strong, message in body, the action an underlined link in the same tone |
| `variant` | `page` (full width, 1px rule below) or `inset` (1px border, 6px radius, inside content) |
| `dismissible`, `name`, `persist`, `dismissed`, `dismissLabel` | See below |
| `label`, `id` | A banner with a title (or `label`) is a named region; without either it is a plain block, so untitled banners never repeat a landmark name |

**Persistence: the same store pattern as `x-ui.checklist`.** With a `name` (letters, digits, `_ : -`, up to 57 characters; put a version in it, "maintenance-2026-11"):

- `persist="local"` (default): localStorage `<prefix>dismiss_banner:<name>`, applied before first paint by a one-line inline script, so it never flashes.
- `persist="server"`: `TallUiKit\Support\Dismiss\DismissStore`, read on render and written through `$wire.dismissNotice('banner:<name>')` by `TallUiKit\Livewire\Concerns\WithDismissals` (renderless). Outside Livewire, or when the server refuses, it falls back to local.
- `persist="none"`: nothing kept.

```php
interface DismissStore
{
    public const KEY_PATTERN = '/^[A-Za-z0-9_:-]{1,64}$/';
    public function dismissed(string $key): bool;
    public function dismiss(string $key): void;
}
interface ChecklistStore extends DismissStore { /* collapsed, setCollapsed */ }
```

`ChecklistStore` now extends `DismissStore` (no change for checklist code or existing implementations, which already have both methods). The kit binds `DismissStore` with `bindIf` to whatever `ChecklistStore` resolves to, so an app's per-user checklist store keeps banners per user too; bind `DismissStore` yourself to store them elsewhere. In the component, override `dismissibleKeys()` (422 for anything else) and `canDismiss($key)` (403). Every dismissal fires a bubbling `ui-dismiss` event, `{ key }`. After Dismiss, focus moves to the next control on the page, else the page heading.

Do: one banner at a time per page; say what happens and when. Don't: use it for a form error (`x-ui.alert`, `x-ui.error-summary`) or a save confirmation (toast), or add an icon or coloured edge.

## Tree

`x-ui.tree`: a nested list on the APG tree view pattern.

```blade
<x-ui.tree label="Files to share" :nodes="$folders" checkable lazy="children" wire:model.live="checked" :value="$checked" />
```

```php
#[Renderless]
public function children(string $id): array
{
    $folder = Folder::whereUuid($id)->firstOrFail();
    $this->authorize('view', $folder); // the id comes from the browser
    return $folder->children->map(fn ($f) => ['id' => $f->uuid, 'label' => $f->name, 'hasChildren' => $f->children_count > 0])->all();
}
```

| Prop | Meaning |
|---|---|
| `nodes` | `['id', 'label', 'children', 'hasChildren', 'expanded', 'href', 'disabled']` |
| `label` | The tree's name (required for screen readers) |
| `selectable` | One row at a time (`aria-selected`); `wire:model` gets its id |
| `checkable` | A check per row (`aria-checked` true, false or mixed); checking a parent checks its loaded children, a parent with some children checked is mixed; `wire:model` gets every checked id |
| `lazy` | Livewire method for children of `hasChildren` nodes, called once with the node's id the first time it opens; return the same shape (at most 500 rows are built) |
| `value`, `expanded`, `id` | First paint; ids open on load |

**Keyboard.** Down/Up, Right (open, then first child), Left (close, then parent), Home/End, Enter (select, follow `href`, or toggle), Space (select or check), `*` (open every sibling), typeahead (500ms buffer). One row in the Tab order. **Lazy loading:** the row is `aria-busy` and says "Loading"; "3 items loaded." is announced; a failure says "Could not load" and Right or a click retries. Rows from the server are built with `textContent`, never HTML. **Morphs:** the `role="tree"` list is `wire:ignore`, so expansion and loaded rows survive re-renders; the binding flows both ways; change the tree's `wire:key` to rebuild it from new data. No Alpine per row.

**Look.** 32px rows in body, 16px indent per level, a chevron that turns (`--motion-sm`), hover `surface-subtle`, selected `surface-muted` (ink 13.63:1) with 500 weight, checks in the `x-ui.checkbox` look, the focus ring inset on the row.

## Progress

### `x-ui.progress` (extended, compatible)

The existing props (`label`, `value`, `max`, `valueText`, `hideLabel`, `id`) work as before. The native `<progress>` still carries the label, value and `aria-valuetext`; the visible fill is now drawn with `transform: scaleX()` so a value change animates (`--motion-md`, `ease-motion-out`, instant under reduced motion) when a Livewire morph keeps the element.

| Prop | Meaning |
|---|---|
| `size` | `sm` 4px (default), `md` 8px, `lg` 12px |
| `tone` | `default` (chart-1), `success` (emerald-700), `warning` (amber-800), `danger` (red-700): each 3:1 or more on the `surface-muted` track and on white |
| `labelPosition` | `top` (label left, value right), `end` (the value after the bar on one line), `hidden` (both kept for assistive tech) |
| `format` | `percent`, `count` ("3 of 5"), `bytes` ("2.1 MB of 5 MB", decimal units) |
| `buffer` | A second, lighter fill (`line-strong`) on the same scale, for uploads and streams that load ahead; each tone's fill is 3:1 or more against it |

### `x-ui.progress-ring`

A circular SVG progress for tiles, cells and buttons where a bar does not fit. It is a loading indicator, never a KPI: figures go in `x-ui.stat`. `role="progressbar"`, `aria-valuemin` 0, `aria-valuemax`, `aria-valuenow` (left out when indeterminate), `aria-valuetext`, named by `label`. Props: `label`, `value` (null: indeterminate, a quarter arc turns; under reduced motion it holds and pulses), `max`, `size` (24, 40, 64; strokes 3, 4, 6), `tone` (as above), `showValue` (the percent in the middle at 40 and 64), `valueText`.

### `x-ui.progress-steps`

"Step 3 of 5" as N equal segments, 2px apart. One `role="progressbar"` (`aria-valuetext` "Step 3 of 5"); `showText` prints it above (meta). Optional `labels` sit under their segments (caption, the current one in ink with `aria-current="step"`) and wrap inside their column on phones. Props: `label`, `total`, `current`, `labels`, `size` (sm 4px, md 8px), `tone`, `showText` (true). Use `x-ui.stepper` instead when the steps have names the user needs or can go back to.

### `x-ui.upload-progress` and `x-ui.upload-progress.list`

A file row: the name (body, never cut: it wraps anywhere), size and status in meta ("2.4 MB · Uploading, 45%", "Uploaded", "Failed" in red-700 with the error below), a linear progress (danger tone on failure; label hidden but kept), and the actions for its state: Cancel while waiting or uploading, Retry and Remove on failure, Remove when done, each named with the file ("Cancel upload of report.pdf").

- `x-ui.upload-progress` is server-driven: `name`, `size` (bytes), `progress`, `status` (queued, uploading, done, failed), `error`, `cancelAction`, `retryAction`, `removeAction` (Livewire calls; without them the buttons fire a bubbling `ui-upload` event, `{ action, name }`), `as` (div, li).
- `x-ui.upload-progress.list for="files"` follows Livewire's own upload events from the input bound with `wire:model="files"` (`livewire-upload-start`, `-progress`, `-finish`, `-error`, `-cancel`; livewire.laravel.com/docs/4.x/uploads, checked 2026-11-15) and shows a row per chosen file, plus an overall line and bar with several files ("Uploading 3 files, 45%"). Cancel calls `$wire.cancelUpload`, Retry uploads the same files again (`$wire.upload` / `uploadMultiple`), Remove sets a single file's property to null or calls `removeAction(index)`. Props: `for`, `multiple`, `removeAction`, `label` ("Uploads"). Livewire uploads a multiple selection as one request, so its files share one percent.

```blade
<x-ui.field label="Attachments" name="files">
    <x-ui.file wire:model="files" multiple>
        <x-slot:files>
            <x-ui.upload-progress.list for="files" multiple remove-action="removeFile" />
        </x-slot:files>
    </x-ui.file>
</x-ui.field>
```

Validate files on the server as usual; the list only shows progress.
