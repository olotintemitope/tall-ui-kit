# Files

`x-ui.file-manager`, `x-ui.file-preview`, `x-ui.file-card` and `x-ui.attachment-list` (v0.9), plus the upgraded `x-ui.file` ([forms](forms.md), "File"). Data in, Livewire actions out: the components never store or fetch files. Names are user input and are always escaped; they wrap anywhere and are never cut off. The shared helper `TallUiKit\Support\Files` gives the type label (`typeLabel()`: "PDF", "JPG", "MP3"), byte sizes (`bytes()`), the preview kind (`kind()`) and size parsing (`parseSize('10MB')`).

## File manager

```blade
<x-ui.file-manager
    label="My files"
    :items="$items"
    :breadcrumbs="$trail"
    :folder="$folder"
    :folders="$folderTree"
    :sort="$sort" :direction="$direction" :total="$total"
    open-action="open" rename-action="rename" move-action="move"
    delete-action="delete" create-folder-action="createFolder"
    download-action="download" sort-action="sortBy" load-more-action="loadMore"
    upload="uploads" upload-target="uploadFolder"
/>
```

| Prop | Meaning |
|---|---|
| `items` | List of `id`, `name`, `folder` (bool), `size` (bytes), `count` (a folder's items), `modified`, `type` (mime), `thumb` (image URL), `href` (download URL), `disabled` |
| `breadcrumbs` | List of `id`, `label`; earlier crumbs call `openAction` with their id, the last is the folder heading (`h2`, `aria-current="location"`) |
| `folder` | The current folder id (drops on the background upload here) |
| `folders` | `x-ui.tree` nodes for the Move dialog |
| `view` | `grid` (default) or `list`; `persist` keeps the user's choice in localStorage (`file-manager-view`; `false` for the page only) |
| `sort`, `direction` | The current order; `sortAction(field, direction)` with field `name`, `modified` or `size` and direction `asc` or `desc` (a custom menu, never a native select) |
| `total`, `loadMoreAction` | "Showing 24 of 134" and Load more (`x-ui.load-more`) |
| `openAction(id)` | A folder opens in place (focus goes to its first item); a file too (open `x-ui.file-preview` from it) |
| `renameAction(id, name)` | Inline rename (F2 or the bulk bar): Enter saves, Escape cancels; return a string to show it under the field |
| `moveAction(ids, folderId)` | From the Move dialog (an `x-ui.tree` of `folders`); a string is the error |
| `deleteAction(ids)` | After `x-ui.confirm.destructive`; a string is the error (toast and announcement) |
| `createFolderAction(name)` | From the New folder dialog; a string is the error |
| `downloadAction(ids)` | Several items, or folders; one file with `href` downloads straight from the link |
| `upload`, `uploadTarget`, `accept` | Upload button and drag and drop from the desktop: a `WithFileUploads` array property, and a property set (without a request) to the folder dropped on (`uploadFolder`) |
| `loading`, `error`, `retryAction`, `empty` | Skeleton in the current view; an alert with Try again; one meta line |
| `label`, `id`, `tz`, `now` | Accessible names, the unique id (needed with two managers), dates |

Every id, name and folder id comes from the browser: check access on the server.

Selection, keyboard and focus: the items are one listbox (`aria-multiselectable`) in both views; focus is roving. Arrows move (grid: Up and Down by a row), Shift+arrows extend, Space toggles, Enter opens, F2 renames, Delete asks to delete, Ctrl/Cmd+A selects all, Escape clears, letters jump. A click selects one item, Ctrl/Cmd+click toggles, Shift+click selects a range, the check box (top left) toggles for touch, a double click opens. After a delete or move focus goes to the next item, else the previous, else the heading. A polite status says "3 items selected.", "Renamed to Brief.pdf.", "Moved 2 items to Invoices.", "Deleted 1 item.", "Uploading 2 files to Invoices."

Drag and drop: a drag of files over the manager shows a dashed edge and "Drop to upload to <folder>"; over a folder the folder is the target (a `secondary` edge). The Upload button is the pointer and keyboard alternative.

Performance: no Alpine per item (one component for the manager, `resources/js/file-manager-core.js` lazily); each item is a few hundred bytes (the check and folder icon are CSS); options carry `wire:ignore.self` so re-renders keep the selection. The toolbar, the bulk bar, the dialogs and the upload list are sent once: on later Livewire requests the browser names them in the `X-Tall-Ui-Chrome` header (`TallUiKit\Support\ChromeCache`) and the server sends an empty `wire:ignore` placeholder, so a round trip carries the items. The Move dialog has its own key, so new folders resend only it. In the workbench a folder of 24 items is about 35 KB per response.

## File preview

```blade
<x-ui.file-preview :file="$file" :files="$siblings" navigate-action="preview" />
<x-ui.file-preview variant="dialog" name="preview" :file="$file" :files="$siblings" navigate-action="preview" />
```

`file`: `key`, `name`, `url` (what the viewer loads), `download` (default `url`), `type` (mime), `kind` (override), `size`, `created`, `modified`, `fields` (label => value), `text` (text and code: the app reads its own file and passes the content), `poster`, `duration`, `alt`. `files`: the set (`key`, `name`) for Previous / Next; `navigateAction` gets the key (else a bubbling `ui-file-preview` event, `{ key }`). `variant`: `panel` (default), `dialog`, `sheet` (`name` opens it). `maxText` (100000 characters, then a note).

Viewers: image (Fit, Zoom in, Zoom out; `+`, `-`, `0` on the image; a zoomed image scrolls in its box), PDF (`<object>` with a link as fallback), audio (`x-ui.audio`), video (`x-ui.video`), text and code (`x-ui.code-block`, scrolling), anything else a plain "No preview for this type of file" with Download. Details: size, type, created and modified (`x-ui.time`), then `fields`. Left and Right go to the previous and next file while focus is in the preview (not in a field or media control). The kit never fetches a URL on the server.

Do: serve files from owner-checked or signed URLs with `Content-Disposition` and `X-Content-Type-Options: nosniff` (SVG with a sandboxing CSP). Don't: preview untrusted HTML inline.

## File card and attachment list

```blade
<x-ui.file-card name="contract.pdf" :size="180000" href="{{ $url }}" remove-action="removeFile(3)" />
<x-ui.attachment-list label="Receipts" :items="$receipts" remove-action="removeReceipt" retry-action="retryReceipt" />
```

`x-ui.file-card`: `name`, `key`, `size`, `meta` (the line under the name instead of the size), `type`, `href`, `thumb`, `alt`, `status` (uploading, done, failed, infected, blocked), `progress`, `error`, `variant` (row, card, compact), `as` (div, li), `actions` (event buttons: download, cancel, retry, remove, as a bubbling `ui-file` event `{ action, key, name }`), `downloadAction`, `cancelAction`, `retryAction`, `removeAction`, `newTab`. Status words in a one-line pill: Uploading (with a bar), Uploaded, Failed, Infected, Blocked; infected and blocked never offer Download. The type label is a mono caption with no tile behind it, said once to screen readers after the name (", PDF file"). A compact card with `href` and no actions is one link (chat and timeline attachments use it).

`x-ui.attachment-list`: `items` (each the card's props), `label`, `variant`, `columns` (cards, 2 to 4), `actions`, the four action method names (called with the item's key), `newTab`, `empty`.

Kit rows that now use these: `x-ui.chat.message` file attachments and `x-ui.timeline.item` attachments are compact file cards (the same props and `data-chat-file` hook); `x-ui.upload-progress` and its `.list` keep their markup and share the type label.
