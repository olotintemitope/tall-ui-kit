# Audit, November 2026 (v0.7.0)

Every component under `resources/views/components` (193 Blade files, 68 docs pages) was checked against the kit's consistency rules, the hard rules in [rules.md](rules.md) and the WAI-ARIA APG pattern of each interactive component. Method: scripted scans of every view (sizes, radii, border widths, focus classes, opacity, icon sizes and strokes, motion, prop names), a read of every component and its Alpine module against its APG pattern, and a live example of every component on the docs site, which surfaced more. Findings were then fixed, or deferred when the fix would change a public API.

## Headline numbers

| Severity | Found | Fixed | Deferred (breaking) | Open (not breaking) |
|---|---|---|---|---|
| High: blocks keyboard or screen reader use, or breaks a hard rule | 2 | 2 | 0 | 0 |
| Medium: APG deviation, missing name or state, a visible bug | 22 | 22 | 0 | 0 |
| Low: polish, consistency, documentation drift | 42 | 35 | 4 | 3 |
| **Total** | **66** | **59** | **4** | **3** |

Fix commits: `3db2d67` (one size scale), `6682524` (prop aliases), `3d1a0bc` (accessibility, focus and behaviour fixes), `76c3d6c` (copy-text tip). Tests for the fixes: `tests/Feature/AuditFixesTest.php`.

## The audit rules

Each component is held to these. The same list is the checklist for new components.

**Consistency**

- Sizes: controls are 32px (`sm`), 36px (`default`, alias `md`) or 40px (`lg`), the same for inputs, selects, the combobox, number input, tag input, colour, date pickers, copy controls and buttons (`FormControl::size()` and `::height()`). Segmented controls, the theme toggle and tabs keep `sm` and `default` only.
- Spacing on the 4px grid; radii from one scale: 4px (`rounded-sm`, checkboxes, segments), 6px (`rounded-md`, controls), 8px (`rounded-lg`, panels, menus, dialogs), full for pills, avatars and radios only.
- Borders 1px, `control` on interactive edges, `line` or `panel-border` on decorative ones. Only the 2px focus outline is thicker.
- Type from the `type-*` roles. Icons: 16px (`size-4`) with a 2px stroke; 14px for inline arrows and remove buttons; 12px glyphs inside checkboxes (3px stroke).
- States: focus is a 2px `focus` outline on `:focus-visible` only (text fields also show their focus border on click, as fields do); hover one step of fill or border; pressed one more; disabled a dashed `control` border on `surface-subtle` with `ink-muted` text, never opacity; loading keeps the name and sets `aria-busy`.
- Error and invalid: a `danger` border plus a `danger/20` ring on focus, the message in `type-caption text-danger-text` 4px below, linked by `aria-describedby`, and `aria-invalid="true"`.
- Empty states: one meta line plus an optional action (`x-ui.empty-state`); no icon tiles.
- Prop names: `size`, `variant` (or `tone` for status colour; each accepts the other), `label`, `hint`, `error`, `disabled`, `headingLevel`. Inconsistent names get aliases, never renames.

**Rules**: no AI look; one-line pills; popups open below; no native `<select>` or date inputs; nothing cut off; tables never scroll the page; contrast in all four theme and mode sets; reduced motion; Livewire payloads under 40 KB. Guards: `KitGuardsTest`, `NoCutOffTest`, `PopupPlacementTest`, `TableResponsiveTest`, `UiKitContrastTest`, `UiKitPerformanceTest`, `composer contrast`, and the axe audit of the gallery and the docs site.

**Accessibility**: each interactive component's keyboard map against its APG pattern, names and roles, focus on every state change (open, close, remove, finish), live announcements, and WCAG 2.2 AA (including 1.4.13 content on hover, 2.5.3 label in name, 2.5.7 dragging alternatives).

## Prop names

| Concept | Before | After (all old names still work) |
|---|---|---|
| Status colour | `x-ui.badge` `variant`, `x-ui.alert` `tone`, `x-ui.toast` `variant` with `error` where badges said `danger` | badge also takes `tone`; alert also takes `variant` and gains `info`; toast (and the toast store) also takes `tone`; `error` and `danger` are read as the same tone everywhere |
| Help text | `x-ui.field` `hint`, `x-ui.checkbox` `description` | checkbox also takes `hint` |
| Explicit error | `x-ui.field` `error`, the controls only `invalid` | `x-ui.checkbox` and `x-ui.radio-group` also take `error` |
| Heading level | `headingLevel` on checklist and timeline, `level` on notification groups and headings | notification groups also take `headingLevel`; `x-ui.page-header` gains `level` (default 1) |
| Group name | `x-ui.fieldset` `legend`, `hideLegend` | also `label`, `hideLabel` |
| Popup side | `x-ui.popover` `placement`, `x-ui.tooltip` `position` | tooltip also takes `placement` |
| Size | buttons `sm`, `default`, `lg`; other controls `sm`, `default` | every control takes `sm`, `default` (alias `md`), `lg`; buttons add `icon-lg` |
| Width | classes on `x-ui.dropdown` and `x-ui.popover` (`min-w-[10rem]`, `w-72`), keywords on controls (`full`, `md`, `sm`) | unchanged: a low finding, deferred (breaking), see below |

## Findings by component

Components not listed passed every check. Fix: the commit that fixed it.

### Forms

| Component | Finding | Severity | Fix |
|---|---|---|---|
| input, select, combobox, number-input, tag-input, color, date pickers, copy-field, copy-button | No 40px `lg` size (only buttons had one); `md` not accepted | Medium | `3db2d67` |
| combobox | Escape on an open list also closed the `<dialog>` around it (no `preventDefault`) | Medium | `3d1a0bc` |
| combobox | Arrow keys stopped on disabled options | Low | `3d1a0bc` |
| combobox, tag-input | Listbox `aria-labelledby` pointed at nothing outside `x-ui.field` | Low | `3d1a0bc` |
| search | Escape on a filled field also closed a dialog | Medium | `3d1a0bc` |
| search | Default `aria-label` overrode the field's visible label (2.5.3) | Low | `3d1a0bc` |
| color | A server error did not set `aria-invalid`; no focus ring when invalid; no disabled look; client-side invalid hex not announced | Medium | `3d1a0bc` |
| tag-input | No disabled look | Low | `3d1a0bc` |
| tag-input | Suggestions as `[value, hint]` lists lost the hint | Low | `3d1a0bc` |
| checkbox, switch, radio-group | The description was read twice (in the name and as the description) | Low | `3d1a0bc` |
| checkbox | No `hint` or `error` props, unlike x-ui.field | Low | `3d1a0bc` |
| switch | Invalid border fought the bound border class; no hover state | Low | `3d1a0bc` |
| switch | Toggling fired no `input` or `change`, so `x-ui.form` never saw it as dirty | Medium | `3d1a0bc` |
| number-input | Home and End did not fire `change`; +/- worked on a disabled field | Low | `3d1a0bc` |
| otp | Two codes on a page shared `id="field-code"` | Low | `3d1a0bc` |
| date pickers | Month and Year buttons named "Month" and "Year" while showing "November" and "2026" (2.5.3) | Medium | `3d1a0bc` |
| date pickers | The `value` attribute was not documented | Low | `3d1a0bc` |
| slider | Percent format dropped zeros on the first paint (70 showed as 7%) | Medium | `3d1a0bc` |
| range | The dual range's first paint ignored `value` and `openEnded` | Low | `3d1a0bc` |
| file | Inside `x-ui.field` the error printed twice with the same id | Medium | `3d1a0bc` |
| file | Upload bar animated `width`; list rows had a fixed height; error spacing 8px instead of 4px | Low | `3d1a0bc` |
| fieldset | `legend`/`hideLegend` where fields say `label` | Low | `6682524` |
| form-page | Bleeds 32px at `lg` into any container narrower than `<main>`'s padding | Low | By design (it bleeds to `<main>`); the docs preview now matches `<main>` |

### Navigation and overlays

| Component | Finding | Severity | Fix |
|---|---|---|---|
| dropdown | Escape was swallowed while the menu was closed, so a dialog could not close from the trigger | Medium | `3d1a0bc` |
| dropdown | Menu had no name; trigger had no `aria-controls` | Medium | `3d1a0bc` |
| dropdown | No type-ahead; Escape closes every level, not one; labels are not `role="group"` | Low | Deferred (breaking), below |
| tabs, segmented | No tab stop when the bound value matches nothing | Low | `3d1a0bc` |
| tabs.panel | Every panel showed until Alpine started | Low | `3d1a0bc` |
| page-header | Actions did not wrap (sideways scroll at 320px with three actions) | Medium | `3d1a0bc` |
| page-header | Title always an `h1` | Low | `3d1a0bc` (`level`) |
| pagination | `wire:loading.attr="disabled"` dropped focus while a page loaded | Medium | `3d1a0bc` |
| pagination | Unavailable Previous and Next were unnamed on phones | Low | `3d1a0bc` |
| load-more | After the last batch focus fell to the top of the page | Medium | `3d1a0bc` |
| sidebar.group | Rail tooltip could not be dismissed with Escape (1.4.13) | Medium | `3d1a0bc` |
| sidebar.header | Collapse button changed its label and `aria-expanded` (double state) | Low | `3d1a0bc` |
| sidebar | Icons drawn with a 1.75 stroke, the rest of the kit 2 | Low | `3d1a0bc` |
| tooltip | `aria-describedby` on the wrapper span, not the control | Medium | `3d1a0bc` |
| tooltip, copy-text | The tip closed when the pointer moved onto it; Escape inside a dialog closed the dialog (1.4.13) | Medium | `3d1a0bc`, `76c3d6c` |
| tooltip | `position` where popover says `placement` | Low | `3d1a0bc` |
| alert-dialog | Escape closed without acknowledging | Medium | `3d1a0bc` |
| dialog.steps | Closing with edits did not ask "Discard changes?" as documented | Medium | `3d1a0bc` |
| lightbox | First focus on "Open full size" (a new-tab link), not Close | Low | `3d1a0bc` |
| confirm.destructive | Why the confirm button is unavailable was not linked to it | Low | `3d1a0bc` |
| confirm, confirm.reason | `confirmed` carries a string from one and `{ name, reason }` from the other | Low | Deferred (breaking), below |

### Feedback and data

| Component | Finding | Severity | Fix |
|---|---|---|---|
| toaster | "N more" hid itself while focused, so focus fell to `<body>`; static `aria-expanded` | High | `3d1a0bc` |
| toaster | Alt+T reaches the last toast in DOM order, not the newest | Low | Open |
| tree | The one tab stop could sit under a collapsed parent, making the tree unreachable | High | `3d1a0bc` |
| sortable | No single-pointer alternative to dragging (2.5.7) | Medium | `3d1a0bc` (Move up, Move down) |
| sortable | Rows written in the slot had no instructions on their handles | Low | `3d1a0bc` |
| data-table row menu | Tab jumped past the remaining rows (the menu sits after the footer) | Medium | `3d1a0bc` |
| data-table row menu | Trigger has no `aria-expanded="false"` until first opened | Low | Open (adds bytes to every row) |
| data-table filter sheet | First focus on the close button | Medium | `3d1a0bc` |
| data-table header | `aria-sort="none"` on every unsorted column | Low | `3d1a0bc` |
| notification-item | `<a href="#" role="button">` did not open on Space | Medium | `3d1a0bc` |
| notification-group | `level` where others say `headingLevel` | Low | `6682524` |
| progress-steps | "Step 1 of 5" with a value of 0 | Low | `3d1a0bc` |
| badge, alert, toast | Status vocabulary differed (`variant`/`tone`, `danger`/`error`); alerts had no `info` | Low | `6682524` |
| disclosure, accordion | `[open]` is removed 160ms after collapse, so "expanded" is reported during the animation | Low | Open |
| card.selectable | The label wraps price, badges and the reason (a long name); a disabled card's reason cannot be reached | Low | Deferred (breaking), below |
| card.row, card.detail-item | Comments said "Slot: value"; both use the default slot | Low | `3d1a0bc` (docs) |
| figure | Dead `isset($captionSlot)` check | Low | `3d1a0bc` |
| delta, text | Comments named old Tailwind colours, not the tokens used | Low | `3d1a0bc` (docs) |
| code-block | Its own copy logic and a toast instead of `x-ui.copy-button`; fixed-height caption row | Low | `3d1a0bc` |
| button | A disabled link button was not focusable | Low | `3d1a0bc` |
| docs/accessibility.md | Said segmented controls wrap and that export columns lacked move buttons; neither is true | Low | `3d1a0bc` (docs) |

## Deferred (breaking)

These need a public API or behaviour change, so they wait for a major or a deliberate minor with a migration note.

1. **Dropdown keyboard model.** APG closes one submenu level per Escape and offers type-ahead; the kit closes every level (documented behaviour apps rely on). Grouping labels needs each section wrapped in a new `x-ui.dropdown.group`. Plan: add the group component and type-ahead, switch Escape behind a `nested-escape` prop, flip the default in 1.0.
2. **`confirmed` event shape.** `x-ui.confirm` dispatches the name as a string, `x-ui.confirm.reason` an object. Unifying it changes every listener. Plan: dispatch `confirm-done` with `{ name, reason }` from both alongside the old event, deprecate `confirmed` payloads in 1.0.
3. **`card.selectable` structure.** A short accessible name needs `aria-labelledby` on the input and the reason outside the `<label>`; reachable reasons need `aria-disabled` instead of `disabled`, which changes form posts. Plan: new markup behind a `compact-name` prop.
4. **Width props.** `x-ui.dropdown` and `x-ui.popover` take Tailwind classes for `width`; controls take keywords. Unifying would break every call that passes classes. Plan: accept the keywords as well, keep classes.

## Open (not breaking)

Toaster Alt+T order, the row menu's initial `aria-expanded` (payload cost per row), and the disclosure's late `[open]` removal are low-severity and scheduled for 0.7.x.
