{{--
    File picker with a drop area and a list of chosen files. The real <input type="file">
    covers the area, so drag-and-drop and click both feed it and wire:model uploads work
    unchanged (progress comes from Livewire's upload events). Props: accept, multiple,
    hint (inside the area, e.g. "PNG or JPG, up to 5 MB"), invalid, variant (list |
    images). `images` previews each chosen image as a square thumbnail (8px radius,
    object-cover, the file name as alt text) in a grid of 4 (3 on phones) with a 24px
    Remove button per image that takes the file out of the input (a DataTransfer
    rebuild, then a change event, so wire:model uploads follow) and returns focus to
    the next thumbnail's Remove, else the drop area. `accept` defaults to image/* there.
    A `files` slot can replace the client-side list, e.g. with the server's temporary
    uploads (`$file->temporaryUrl()`).

    Additive props (v0.9; without any of them the field works exactly as before):
      maxSize        bytes or "10MB" per file; maxFiles; maxTotalSize (bytes or "50MB")
      validate       check each file's type against `accept` (on with any limit above)
                     Each refused file is listed under the chosen ones with its reason
                     ("Larger than 10 MB.") and is never sent; the rest upload as usual.
      hint           when not given and there are limits, it is written from `accept` and
                     them: "PDF or images, up to 10 MB each. At most 5 files."
      reorder        the chosen files are a sortable list (drag, Up / Down, or Space and
                     the arrows on the grip); reorderAction (Livewire) gets the indexes
                     in their old order, e.g. [2, 0, 1]
      removeAction   Livewire method called with the index when a file is removed
                     (otherwise the temporary upload is removed with $wire.removeUpload)
      paste          files on the clipboard are added while focus is in the field
      capture        "environment" (rear camera) or "user" (front) on phones
      chunked        the URL of the app's ChunkedUploadController routes: files go up in
                     pieces (chunkSize, default from the server), resume after a network
                     drop, and the upload ids are set on the wire:model property (see
                     docs/components/forms.md, "Chunked uploads")
    The behaviour of these loads lazily (resources/js/file-core.js).
--}}
@props([
    'multiple' => false, 'hint' => null, 'invalid' => null, 'variant' => 'list',
    'maxSize' => null, 'maxFiles' => null, 'maxTotalSize' => null, 'validate' => false, 'reorder' => false, 'reorderAction' => null,
    'removeAction' => null, 'paste' => false, 'capture' => null, 'chunked' => null, 'chunkSize' => null,
])
@aware(['name' => null, 'id' => null, 'error' => null, 'required' => false])

@php
    use TallUiKit\Support\FormControl;

    $fieldName = $name ?? FormControl::model($attributes);
    $controlId = FormControl::id($attributes->get('id', $id), $fieldName) ?? 'file-'.uniqid();
    $message = FormControl::error($errors ?? null, $fieldName ? preg_replace('/\.\*$/', '', $fieldName) : null, $error);
    $isInvalid = $invalid ?? (bool) $message;
    $areaHintId = $controlId.'-area';

    $maxBytes = \TallUiKit\Support\Files::parseSize($maxSize);
    $totalBytes = \TallUiKit\Support\Files::parseSize($maxTotalSize);
    $enhanced = $maxBytes || $maxFiles || $totalBytes || $validate || $reorder || $paste || $chunked;
    if ($enhanced && $hint === null) {
        $hint = \TallUiKit\Support\Files::hint($attributes->get('accept') ?? ($variant === 'images' ? 'image/*' : null), $maxBytes, $maxFiles ? (int) $maxFiles : null, $totalBytes, (bool) $multiple);
    }
    if ($paste && $hint !== null) {
        $hint = rtrim($hint).' You can also paste '.($multiple ? 'files' : 'a file').'.';
    }
    $captureValue = $capture === true ? 'environment' : (in_array($capture, ['user', 'environment'], true) ? $capture : null);
@endphp

@if ($enhanced)
    @include('tall-ui-kit::partials.file-enhanced')
    @php return; @endphp
@endif

<div
    x-data="{
        dragging: false,
        progress: null,
        files: [],
        read(input) {
            this.files.forEach((file) => file.url && URL.revokeObjectURL(file.url));
            this.files = Array.from(input.files || []).map((file, index) => ({ key: file.name + ':' + file.size + ':' + index, name: file.name, size: file.size, url: file.type.startsWith('image/') ? URL.createObjectURL(file) : null }));
        },
        remove(index) {
            const input = this.$refs.input;
            const transfer = new DataTransfer();
            Array.from(input.files).forEach((file, i) => { if (i !== index) transfer.items.add(file) });
            input.files = transfer.files;
            input.dispatchEvent(new Event('change', { bubbles: true }));
            this.$nextTick(() => {
                const buttons = this.$root.querySelectorAll('[data-file-remove]');
                (buttons[Math.min(index, buttons.length - 1)] ?? input).focus();
            });
        },
        destroy() { this.files.forEach((file) => file.url && URL.revokeObjectURL(file.url)) },
        size(bytes) { return bytes < 1024 ? bytes + ' B' : bytes < 1048576 ? (bytes / 1024).toFixed(0) + ' KB' : (bytes / 1048576).toFixed(1) + ' MB' },
    }"
    x-on:livewire-upload-start="progress = 0"
    x-on:livewire-upload-progress="progress = $event.detail.progress"
    x-on:livewire-upload-finish="progress = null"
    x-on:livewire-upload-error="progress = null"
    x-on:livewire-upload-cancel="progress = null"
    {{ $attributes->only('class')->class(['w-full']) }}
>
    <div
        @class([
            'relative flex flex-col items-start gap-1 rounded-md border border-dashed bg-surface px-4 py-4 transition-colors duration-100',
            'has-focus-visible:border-focus has-focus-visible:ring-2 has-focus-visible:ring-primary/20',
            'has-disabled:bg-surface-subtle has-disabled:cursor-not-allowed',
            'border-danger' => $isInvalid,
            'border-control hover:border-control-hover' => ! $isInvalid,
        ])
        x-bind:class="dragging && 'border-secondary! bg-surface-subtle'"
    >
        <input
            x-ref="input"
            type="file"
            @if ($variant === 'images' && ! $attributes->has('accept')) accept="image/*" @endif
            id="{{ $controlId }}"
            data-ui="file"
            @if ($multiple) multiple @endif
            @if ($captureValue) capture="{{ $captureValue }}" @endif
            @if ($isInvalid) aria-invalid="true" @endif
            @if ($required) aria-required="true" @endif
            aria-describedby="{{ trim($areaHintId.' '.FormControl::describedBy($controlId, false, (bool) $message)) }}"
            x-on:change="read($el)"
            x-on:dragenter="dragging = true"
            x-on:dragleave="dragging = false"
            x-on:drop="dragging = false"
            {{ $attributes->except(['class', 'id'])->class(['absolute inset-0 size-full cursor-pointer opacity-0 disabled:cursor-not-allowed']) }}
        />
        <p class="type-body text-ink! pointer-events-none">
            <span class="font-medium underline underline-offset-2 decoration-line-strong">Choose {{ $multiple ? 'files' : 'a file' }}</span> or drag {{ $multiple ? 'them' : 'it' }} here
        </p>
        <p id="{{ $areaHintId }}" class="type-caption pointer-events-none">{{ $hint ?? ($multiple ? 'Any number of files.' : 'One file.') }}</p>
        <div x-show="progress !== null" x-cloak class="mt-2 h-1 w-full overflow-hidden rounded-full bg-surface-muted" role="progressbar" aria-label="Upload progress" x-bind:aria-valuenow="progress" aria-valuemin="0" aria-valuemax="100">
            <div class="h-full origin-left bg-secondary transition-transform duration-150 motion-reduce:transition-none" x-bind:style="`transform: scaleX(${progress / 100})`"></div>
        </div>
    </div>

    @isset($files)
        {{ $files }}
    @elseif ($variant === 'images')
        <ul x-show="files.length" x-cloak class="mt-3 grid grid-cols-3 gap-3 sm:grid-cols-4" aria-label="Chosen images" data-file-previews>
            <template x-for="(file, index) in files" :key="file.key">
                <li class="flex min-w-0 flex-col gap-1">
                    <div class="relative aspect-square overflow-hidden rounded-lg border border-line bg-surface-subtle">
                        <img x-show="file.url" x-bind:src="file.url" x-bind:alt="file.name" class="size-full object-cover" decoding="async" />
                        <x-ui.icon name="file" class="absolute inset-0 m-auto size-6 text-ink-muted" x-show="! file.url" />
                        <button type="button" class="absolute right-1 top-1 flex size-6 items-center justify-center rounded-md border border-control bg-surface text-ink focus-ring hover:bg-surface-subtle" x-on:click="remove(index)" x-bind:aria-label="'Remove ' + file.name" data-file-remove>
                            <x-ui.icon name="x" class="size-3.5" />
                        </button>
                    </div>
                    <span class="type-caption wrap-anywhere" x-text="file.name"></span>
                </li>
            </template>
        </ul>
    @else
        <ul x-show="files.length" x-cloak class="mt-2 divide-y divide-line rounded-md border border-line" aria-label="Chosen files">
            <template x-for="file in files" :key="file.name">
                <li class="flex items-center gap-2 px-3 min-h-9 py-1.5">
                    <x-ui.icon name="file" class="size-4 text-ink-muted" />
                    <span class="type-body wrap-anywhere flex-1 min-w-0" x-text="file.name"></span>
                    <span class="type-numeric text-ink-muted" x-text="size(file.size)"></span>
                </li>
            </template>
        </ul>
    @endisset

    {{-- Inside x-ui.field the field prints the message (with this id); printing it here too would repeat it. --}}
    @if ($message && $__env->getConsumableComponentData('label') === null)
        <p id="{{ FormControl::errorId($controlId) }}" class="type-caption text-danger-text! mt-1">{{ $message }}</p>
    @endif
</div>
