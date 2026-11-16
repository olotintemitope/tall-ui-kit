{{--
    Folders and files the user browses, selects and manages (docs/components/files.md,
    "File manager"). Data in, Livewire actions out: the component never stores files.

    Layout: a toolbar (breadcrumbs; New folder, Upload, the sort menu and the Grid / List
    switch), then the items as a grid of cards or a list of rows (the choice is kept in
    localStorage under `persist`), "Load more", and the bulk bar while items are selected
    (the x-data-table bulk-bar look: "3 selected · Select all · Clear" and the actions).

    The items are one listbox (role="listbox", aria-multiselectable) in both views; each
    item is an option (folder icon, thumbnail or type label; the full name, wrapping;
    size or item count; modified date). One Alpine component for the manager and none per
    item: resources/js/file-manager-core.js (lazy) handles keys, pointer, drag and drop
    and rename on delegated listeners. Selection lives in the browser (wire:ignore.self on
    each option keeps it across re-renders) and goes to the server with each action.

    Keyboard (focus is roving: one item in the Tab order):
      Arrows      move focus (grid: Up and Down move by a row); Shift+arrows extend the
                  selection; Home / End: first / last item
      Space       selects or deselects the focused item (Shift+Space: a range)
      Enter       opens it (a folder opens in place; a file calls `openAction` too)
      F2          renames it inline: Enter saves, Escape cancels (focus returns to it)
      Delete      asks to delete the selection (x-ui.confirm.destructive)
      Ctrl/Cmd+A  selects every loaded item; Escape clears the selection
      a letter    moves to the next item whose name starts with it
    Pointer: a click selects one item, Ctrl/Cmd+click toggles, Shift+click selects a
    range, the check mark toggles (for touch), a double click opens.

    Actions (Livewire method names; every id comes from the browser, so check access on
    the server):
      openAction(id)               a folder or file; breadcrumbs call it with the crumb's id
      renameAction(id, name)       return a string to show it as the error
      moveAction(ids, folderId)    from the Move dialog: an x-ui.tree of `folders`
      deleteAction(ids)            after x-ui.confirm.destructive
      createFolderAction(name)     from the New folder dialog; a string is the error
      downloadAction(ids)          several items, or files without `href` (return a download)
      sortAction(field, direction) field name | modified | size, direction asc | desc
      loadMoreAction()             with `total` above the items shown
      retryAction()                the error state's Retry
    Without renameAction, moveAction, deleteAction or createFolderAction that command is
    hidden. Uploads: `upload` names a WithFileUploads array property; Upload opens the
    picker, and files dragged from the desktop onto the manager (or onto a folder) are
    uploaded to it through Livewire, with x-ui.upload-progress.list. `uploadTarget` names
    a property set (without a request) to the folder dropped on, or `folder`, first.

    Payload: the toolbar, the bulk bar, the dialogs and the upload list are sent once; later
    Livewire responses carry an empty wire:ignore placeholder for them (TallUiKit\Support\
    ChromeCache, the X-Tall-Ui-Chrome header), so a round trip carries the items only.

    States: `loading` (a skeleton in the current view), `error` (an alert with Retry),
    no items (`empty`, one meta line). Screen readers hear selection counts and finished
    actions ("3 items selected.", "Moved 2 items to Invoices.") in a polite status.

    Item data: id, name, folder (bool), size (bytes), count (a folder's items), modified,
    type (mime), thumb (image URL), href (download URL), disabled. Names are user input:
    always escaped.

    Props: items, breadcrumbs (list of [id, label]), folder (current folder id), folders
    (x-ui.tree nodes for Move), label ("Files"), view (grid | list), persist (storage key,
    "file-manager-view"; false keeps the choice for the page only), sort, direction, total,
    the actions above, upload, uploadTarget ("uploadFolder"), accept, loading, error,
    empty, id, tz, now.
--}}
@props([
    'items' => [], 'breadcrumbs' => [], 'folder' => null, 'folders' => [], 'label' => 'Files', 'view' => 'grid', 'persist' => 'file-manager-view',
    'sort' => 'name', 'direction' => 'asc', 'total' => null,
    'openAction' => null, 'renameAction' => null, 'moveAction' => null, 'deleteAction' => null, 'createFolderAction' => null,
    'downloadAction' => null, 'sortAction' => null, 'loadMoreAction' => null, 'retryAction' => null,
    'upload' => null, 'uploadTarget' => 'uploadFolder', 'accept' => null,
    'loading' => false, 'error' => null, 'empty' => 'This folder is empty.', 'id' => null, 'tz' => 'Africa/Lagos', 'now' => null,
])

@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Js;
    use TallUiKit\Support\Files;

    $id ??= 'fm-'.substr(md5($label), 0, 6);
    $view = $view === 'list' ? 'list' : 'grid';
    $items = collect($items)->values();
    $shown = $items->count();
    $total ??= $shown;
    $sorts = [
        'name:asc' => 'Name, A to Z', 'name:desc' => 'Name, Z to A',
        'modified:desc' => 'Newest first', 'modified:asc' => 'Oldest first',
        'size:desc' => 'Largest first', 'size:asc' => 'Smallest first',
    ];
    $sortKey = $sort.':'.($direction === 'desc' ? 'desc' : 'asc');
    $crumbs = collect($breadcrumbs)->values();
    $here = $crumbs->last()['label'] ?? $label;
    $options = [
        'id' => $id, 'persist' => $persist, 'view' => $view, 'sort' => $sortKey, 'here' => $here,
        'open' => $openAction, 'rename' => $renameAction, 'move' => $moveAction, 'delete' => $deleteAction,
        'create' => $createFolderAction, 'download' => $downloadAction, 'upload' => $upload, 'uploadTarget' => $uploadTarget,
    ];

    /*
     * The toolbar, the bulk bar, the dialogs and the upload list never change between
     * renders (unless the folders or commands do), so they are sent once. The browser
     * names the chrome it already holds in an X-Tall-Ui-Chrome header (resources/js/files.js);
     * when this manager's key is listed, an empty wire:ignore placeholder with the same
     * wire:key goes instead and Livewire's morph keeps what is on the page. A manager that
     * first appears in a Livewire update is not listed, so it gets the full markup.
     */
    $base = json_encode([array_diff_key($options, ['sort' => 1, 'here' => 1, 'view' => 1]), $accept, $label]);
    $chromeKey = $id.':'.substr(md5($base), 0, 12);
    $chrome = ! \TallUiKit\Support\ChromeCache::has($chromeKey);
    // The Move dialog has its own key: new or moved folders resend it alone.
    $moveKey = $id.':m'.substr(md5($base.json_encode($folders)), 0, 12);
    $moveChrome = ! \TallUiKit\Support\ChromeCache::has($moveKey);

    /*
     * The items are built as one string: a folder can hold hundreds of them, and every
     * Blade conditional in a loop adds morph markers to each Livewire response.
     */
    $html = '';
    foreach ($items as $i => $item) {
        $itemId = (string) ($item['id'] ?? $i);
        $name = (string) ($item['name'] ?? 'Untitled');
        $isFolder = ! empty($item['folder']);
        $typeLabel = $isFolder ? 'Folder' : Files::typeLabel($name, $item['type'] ?? null);
        $metaText = $isFolder
            ? (isset($item['count']) ? number_format((int) $item['count']).' '.((int) $item['count'] === 1 ? 'item' : 'items') : '')
            : (string) Files::bytes($item['size'] ?? null);
        $at = ! empty($item['modified']) ? Carbon::parse($item['modified'])->setTimezone($tz) : null;
        // Compact: no ids, classes or icons per item (the check and the folder are drawn in CSS).
        $visual = $isFolder
            ? '<i class="ui-fm-folder"></i>'
            : (! empty($item['thumb'])
                ? '<img src="'.e($item['thumb']).'" alt="" loading="lazy" decoding="async" class="ui-fm-img">'
                : '<span class="ui-file-type">'.e($typeLabel).'</span>');
        $html .= '<li role="option" aria-selected="false" tabindex="'.($i === 0 ? '0' : '-1').'" data-id="'.e($itemId).'"'
            .($isFolder ? ' data-folder' : '')
            .(! empty($item['disabled']) ? ' aria-disabled="true"' : '')
            .(! $isFolder && ! empty($item['href']) ? ' data-href="'.e($item['href']).'"' : '')
            .' wire:key="'.e($id.'-'.$itemId).'" wire:ignore.self>'
            .'<span class="ui-fm-visual" aria-hidden="true">'.$visual.'</span>'
            .'<span class="ui-fm-name">'.e($name).'</span>'
            .'<span class="ui-fm-meta"><span class="ui-fm-kind"><span class="sr-only">, </span>'.e($typeLabel).'</span>'
            .($metaText !== '' ? '<span class="ui-fm-size"><span class="sr-only">, </span>'.e($metaText).'</span>' : '')
            .($at ? '<span class="ui-fm-date"><span class="sr-only">, modified </span><time datetime="'.$at->format('Y-m-d').'">'.e($at->format('j M Y')).'</time></span>' : '')
            .'</span></li>';
    }
@endphp

<div
    x-data="uiFileManager({{ Js::from($options) }})"
    x-bind:data-view="view"
    data-view="{{ $view }}"
    data-folder="{{ $folder }}"
    data-here="{{ $here }}"
    data-sort="{{ $sortKey }}"
    x-on:confirmed="if ($event.detail === {{ Js::from($id.'-delete') }}) fmDelete()"
    {{ $attributes->class(['ui-fm relative min-w-0']) }}
    id="{{ $id }}"
    data-file-manager
>
    <div class="flex min-w-0 flex-wrap items-center justify-between gap-x-4 gap-y-3 pb-3">
        <nav aria-label="Folder path in {{ $label }}" class="min-w-0 flex-1" data-fm-crumbs>
            <ol class="flex min-w-0 flex-wrap items-center gap-x-1.5 gap-y-1 type-body">
                @foreach ($crumbs as $i => $crumb)
                    <li class="flex min-w-0 items-center gap-1.5">
                        @if ($i > 0)<span aria-hidden="true" class="select-none text-ink-muted">/</span>@endif
                        @if ($i === $crumbs->count() - 1)
                            <h2 class="type-body-strong min-w-0 wrap-anywhere text-ink" aria-current="location" tabindex="-1" data-fm-heading>{{ $crumb['label'] }}</h2>
                        @elseif ($openAction)
                            <button type="button" class="min-w-0 rounded-sm text-left text-ink-muted wrap-anywhere transition-colors duration-100 hover:text-ink focus-ring" wire:click="{{ $openAction }}({{ Js::from($crumb['id'] ?? null) }})" data-fm-crumb>{{ $crumb['label'] }}</button>
                        @else
                            <span class="min-w-0 text-ink-muted wrap-anywhere">{{ $crumb['label'] }}</span>
                        @endif
                    </li>
                @endforeach
                @if ($crumbs->isEmpty())
                    <li><h2 class="type-body-strong text-ink" tabindex="-1" data-fm-heading>{{ $label }}</h2></li>
                @endif
            </ol>
        </nav>
        <div class="flex flex-wrap items-center gap-2" data-fm-toolbar wire:key="{{ $chromeKey }}-top" wire:ignore @if ($chrome) data-fm-chrome="{{ $chromeKey }}" @endif>
            @if ($chrome)
            @if ($createFolderAction)
                <x-ui.button variant="outline" size="sm" x-on:click="$dispatch('open-dialog', {{ Js::from($id.'-new') }})" data-fm-new-folder><x-ui.icon name="folder-plus" />New folder</x-ui.button>
            @endif
            @if ($upload)
                <input type="file" multiple class="sr-only" tabindex="-1" aria-hidden="true" wire:model="{{ $upload }}" @if ($accept) accept="{{ $accept }}" @endif x-ref="upload" x-on:click="fmPrepareUpload(null)" data-fm-upload-input>
                <x-ui.button variant="outline" size="sm" x-on:click="$refs.upload.click()" data-fm-upload><x-ui.icon name="upload" />Upload</x-ui.button>
            @endif
            @if ($sortAction)
                <x-ui.dropdown align="right" width="min-w-[12rem]">
                    <x-slot:trigger>
                        <x-ui.button variant="outline" size="sm" data-fm-sort><x-ui.icon name="arrow-up-down" /><span>Sort<span class="sr-only" x-text="', now ' + ({{ Js::from($sorts) }}[sort] ?? '')"></span></span></x-ui.button>
                    </x-slot:trigger>
                    @foreach ($sorts as $value => $text)
                        @php([$field, $dir] = explode(':', $value))
                        <x-ui.dropdown.item role="menuitemradio" x-bind:aria-checked="sort === '{{ $value }}' ? 'true' : 'false'" wire:click="{{ $sortAction }}('{{ $field }}', '{{ $dir }}')" data-fm-sort-option="{{ $value }}">
                            <span class="flex size-4 items-center justify-center"><x-ui.icon name="check" x-show="sort === '{{ $value }}'" /></span>{{ $text }}
                        </x-ui.dropdown.item>
                    @endforeach
                </x-ui.dropdown>
            @endif
            <x-ui.segmented size="sm" label="View" x-model="view" :value="$view" :options="['grid' => ['label' => 'Grid', 'icon' => 'layout-grid', 'iconOnly' => true], 'list' => ['label' => 'List', 'icon' => 'list', 'iconOnly' => true]]" data-fm-view />
            @endif
        </div>
    </div>

    <p class="sr-only" role="status" aria-live="polite" aria-atomic="true" x-text="status" data-fm-live></p>

    @if ($error)
        <x-ui.alert variant="danger" data-fm-error>
            <span class="wrap-anywhere">{{ $error }}</span>
            @if ($retryAction)
                <x-ui.button variant="link" size="sm" wire:click="{{ $retryAction }}" :loading="$retryAction">Try again</x-ui.button>
            @endif
        </x-ui.alert>
    @elseif ($loading)
        <div role="status" class="ui-fm-skeleton" data-fm-skeleton>
            <span class="sr-only">Loading {{ $here }}</span>
            <div class="ui-fm-list" aria-hidden="true">
                @for ($i = 0; $i < 4; $i++)
                    <div class="ui-fm-item"><span class="ui-fm-visual"><x-ui.skeleton class="size-6" /></span><span class="ui-fm-name"><x-ui.skeleton.line role="body" width="w-3/4" /></span><span class="ui-fm-meta"><x-ui.skeleton.line role="meta" width="w-1/3" /></span></div>
                @endfor
            </div>
        </div>
    @else
        <x-ui.loading-overlay :target="collect([$openAction, $sortAction])->filter()->implode(',') ?: null" :label="'Loading '.$here" class="min-w-0">
            <div class="ui-fm-drop" data-fm-drop>
                @if ($shown === 0)
                    <p class="type-meta py-6" data-fm-empty>{{ $empty }}@if ($upload) Drop files here or use Upload.@endif</p>
                @else
                    <div class="ui-fm-head" aria-hidden="true"><span></span><span></span><span>Name</span><span class="ui-fm-meta"><span class="ui-fm-kind">Type</span><span class="ui-fm-size">Size</span><span class="ui-fm-date">Modified</span></span></div>
                    <ul role="listbox" aria-multiselectable="true" aria-label="{{ $here }}" aria-describedby="{{ $id }}-help" class="ui-fm-list" data-fm-list>{!! $html !!}</ul>
                @endif
                <p class="ui-fm-drop-hint type-body-strong" aria-hidden="true" data-fm-drop-hint>Drop to upload to <span x-text="dropName">{{ $here }}</span></p>
            </div>
        </x-ui.loading-overlay>
        <p id="{{ $id }}-help" class="sr-only">Space selects, Enter opens, F2 renames, Delete deletes, Control A selects all.</p>
        @if ($loadMoreAction && $total > $shown)
            <x-ui.load-more :shown="$shown" :total="$total" :action="$loadMoreAction" noun="items" />
        @elseif ($shown > 0)
            <p class="type-meta py-3" data-fm-count>{{ number_format($total) }} {{ $total === 1 ? 'item' : 'items' }}</p>
        @endif
    @endif

    <div class="min-w-0" wire:key="{{ $chromeKey }}-bottom" wire:ignore @if ($chrome) data-fm-chrome="{{ $chromeKey }}" @endif>
    @if ($chrome)
    @if ($upload)
        <x-ui.upload-progress.list :for="$upload" multiple label="Uploads" class="mt-2" />
    @endif

    <div x-show="count > 0" x-cloak class="sticky bottom-0 z-20 mt-3 flex flex-wrap items-center justify-between gap-x-4 gap-y-2 border-t border-line bg-surface py-2.5 shadow-bar" data-fm-bulk-bar>
        <p class="type-body flex flex-wrap items-center gap-x-2">
            <span class="type-body-strong tabular-nums" x-text="count + ' selected'"></span>
            <span class="text-ink-muted" aria-hidden="true">&middot;</span>
            <x-ui.button variant="link" size="sm" class="h-6 px-0" x-show="count < loaded" x-on:click="fmSelectAll()" data-fm-select-all>Select all <span x-text="loaded"></span></x-ui.button>
            <span class="text-ink-muted" aria-hidden="true" x-show="count < loaded">&middot;</span>
            <x-ui.button variant="link" size="sm" class="h-6 px-0" x-on:click="fmClear(true)" data-fm-clear>Clear</x-ui.button>
        </p>
        <div class="flex flex-wrap items-center gap-2">
            @if ($openAction)
                <x-ui.button variant="outline" size="sm" x-show="count === 1" x-on:click="fmOpenSelected()" data-fm-action="open">Open</x-ui.button>
            @endif
            @if ($renameAction)
                <x-ui.button variant="outline" size="sm" x-show="count === 1" x-on:click="fmRenameSelected()" data-fm-action="rename"><x-ui.icon name="pencil" />Rename</x-ui.button>
            @endif
            <x-ui.button variant="outline" size="sm" x-show="canDownload" x-on:click="fmDownload()" data-fm-action="download"><x-ui.icon name="download" />Download</x-ui.button>
            @if ($moveAction)
                <x-ui.button variant="outline" size="sm" x-on:click="fmAskMove()" data-fm-action="move"><x-ui.icon name="folder-input" />Move</x-ui.button>
            @endif
            @if ($deleteAction)
                <x-ui.button variant="destructive" size="sm" x-on:click="fmAskDelete()" data-fm-action="delete"><x-ui.icon name="trash-2" />Delete</x-ui.button>
            @endif
        </div>
    </div>

    @if ($deleteAction)
        <x-ui.confirm.destructive :name="$id.'-delete'" title="Delete the selected items?" description="Deleted items and everything in deleted folders are removed. This cannot be undone." confirm-label="Delete" />
    @endif

    @if ($createFolderAction)
        <x-ui.dialog :name="$id.'-new'" title="New folder" size="sm" data-fm-new-dialog>
            <form x-on:submit.prevent="fmCreate($data)" id="{{ $id }}-new-form" novalidate>
                <x-ui.field label="Folder name" :id="$id.'-new-name'">
                    <x-ui.input id="{{ $id }}-new-name" x-model="newName" maxlength="255" autocomplete="off" x-bind:aria-invalid="error ? 'true' : null" aria-describedby="{{ $id }}-new-error" />
                </x-ui.field>
                <p id="{{ $id }}-new-error" class="type-caption text-danger-text! mt-1" x-text="error" x-show="error" x-cloak></p>
            </form>
            <x-slot:footer>
                <x-ui.button variant="outline" size="sm" x-on:click="close()" data-dialog-cancel>Cancel</x-ui.button>
                <x-ui.button size="sm" type="submit" form="{{ $id }}-new-form" x-bind:aria-busy="busy ? 'true' : null" data-fm-create>Create folder</x-ui.button>
            </x-slot:footer>
        </x-ui.dialog>
    @endif
    @endif
    </div>

    @if ($moveAction)
    <div class="contents" wire:key="{{ $moveKey }}" wire:ignore @if ($moveChrome) data-fm-chrome="{{ $moveKey }}" @endif>
    @if ($moveChrome)
        <x-ui.dialog :name="$id.'-move'" title="Move to folder" description="Choose where the selected items go." size="sm" data-fm-move-dialog>
            <x-ui.tree :nodes="$folders" label="Folders" selectable x-model="moveTarget" :expanded="collect($folders)->pluck('id')->all()" :id="$id.'-tree'" />
            <p class="type-caption text-danger-text! mt-2" x-show="error" x-text="error" x-cloak data-fm-move-error></p>
            <x-slot:footer>
                <x-ui.button variant="outline" size="sm" x-on:click="close()" data-dialog-cancel>Cancel</x-ui.button>
                <x-ui.button size="sm" x-on:click="fmMove($data)" x-bind:aria-disabled="busy || moveTarget === null ? 'true' : null" x-bind:aria-busy="busy ? 'true' : null" data-fm-move-confirm>Move here</x-ui.button>
            </x-slot:footer>
        </x-ui.dialog>
    @endif
    </div>
    @endif
</div>
