{{--
    Shows one file (docs/components/files.md, "File preview"): the right viewer for its type,
    its details, Download and Open in new tab, and Previous / Next across a set. Inline as a
    panel, or in an x-ui.dialog or x-ui.sheet opened by name.

    Viewers by type (from the mime type, else the extension; `kind` overrides):
      image  fitted to the box; Zoom out, Zoom in and Fit (also -, + and 0 on the image),
             the zoomed image scrolls inside its box
      pdf    the browser's own viewer in an <object>, with a link to open it as fallback
      audio  x-ui.audio; video: x-ui.video (`poster`)
      text   x-ui.code-block with `text` (the app reads its own file and passes the
             content; capped at `maxText` characters with a note)
      other  "No preview for this type of file." and Download
    The kit never fetches a URL on the server: the viewers load the URLs the app gives.

    Details: size, type, created and modified (x-ui.time), then `fields` (label => value).
    Previous / Next: `files` is the set (each [key, name]) and `file['key']` the current one;
    the buttons call `navigateAction` with the key (or dispatch a bubbling
    `ui-file-preview` event, detail { key }); Left and Right arrows do the same while focus
    is in the preview (not in a field or a media control). "3 of 12" says where you are.

    The name, fields and text are user input: always escaped.
    Props: file (key, name, url, type, size, created, modified, fields, text, poster,
    download, kind), files, navigateAction, variant (panel | dialog | sheet), name (the
    dialog or sheet name), maxText (100000), tz, now.
--}}
@props(['file' => [], 'files' => [], 'navigateAction' => null, 'variant' => 'panel', 'name' => 'file-preview', 'maxText' => 100000, 'tz' => 'Africa/Lagos', 'now' => null])

@php
    $variant = in_array($variant, ['panel', 'dialog', 'sheet'], true) ? $variant : 'panel';
    $title = (string) ($file['name'] ?? 'File');
@endphp

@if ($variant === 'dialog')
    <x-ui.dialog :name="$name" :title="$title" size="xl" {{ $attributes }}>
        <x-ui.file-preview.body :file="$file" :files="$files" :navigate-action="$navigateAction" :max-text="$maxText" :tz="$tz" :now="$now" :heading="false" />
    </x-ui.dialog>
@elseif ($variant === 'sheet')
    <x-ui.sheet :name="$name" :title="$title" size="lg" {{ $attributes }}>
        <x-ui.file-preview.body :file="$file" :files="$files" :navigate-action="$navigateAction" :max-text="$maxText" :tz="$tz" :now="$now" :heading="false" />
    </x-ui.sheet>
@else
    <section aria-label="Preview of {{ $title }}" {{ $attributes->class(['min-w-0 rounded-lg border border-line bg-surface p-4']) }} data-file-preview-panel>
        <x-ui.file-preview.body :file="$file" :files="$files" :navigate-action="$navigateAction" :max-text="$maxText" :tz="$tz" :now="$now" />
    </section>
@endif
