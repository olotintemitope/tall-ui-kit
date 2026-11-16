{{--
    Upload progress for Livewire's own file uploads (docs/components/workflow.md, "Upload
    progress"). Put it next to the file input bound with wire:model="{{ for }}" (the
    input can be anywhere on the page). It follows Livewire's upload events from that
    input (livewire-upload-start, -progress, -finish, -error, -cancel) and shows one row
    per chosen file in the x-ui.upload-progress look, with an overall line and bar when
    there are several ("Uploading 3 files, 45%").

    Livewire uploads a multiple selection as one request, so the files of one batch share
    its percent. Actions: Cancel ($wire.cancelUpload(for)), Retry (uploads the same files
    again with $wire.upload / $wire.uploadMultiple; the browser still holds them), Remove
    (single: sets the property to null; multiple: calls `removeAction` with the file's
    index, or just drops the row). A polite status says each change ("report.pdf
    uploaded.", "Upload failed: report.pdf."). One Alpine component for the list, none
    per row. Validate the files on the server as usual: this only shows progress.

    Props: for (the wire:model property, required), multiple (false), removeAction,
    label ("Uploads": names the list).
--}}
@props(['for', 'multiple' => false, 'removeAction' => null, 'label' => 'Uploads'])

<div
    x-data="uiUploadProgress(@js(['model' => $for, 'multiple' => (bool) $multiple, 'removeAction' => $removeAction]))"
    {{ $attributes->class(['min-w-0']) }}
    data-upload-list="{{ $for }}"
>
    <div class="flex flex-col gap-1.5 pb-1" x-show="files.length > 1" x-cloak data-upload-overall>
        <p class="type-meta" x-text="overallText" aria-hidden="true"></p>
        <div class="ui-progress-track relative h-1 overflow-hidden rounded-sm" data-tone="default">
            <progress class="ui-progress" max="100" x-bind:value="overall" aria-label="{{ $label }}, overall" x-bind:aria-valuetext="overallText"></progress>
            <span class="ui-progress-fill" aria-hidden="true" x-bind:style="'transform: scaleX(' + overall / 100 + ')'" style="transform: scaleX(0)"></span>
        </div>
    </div>
    <ul aria-label="{{ $label }}" class="divide-y divide-line" x-show="files.length" x-cloak>
        <template x-for="(file, index) in files" :key="file.id">
            <li class="flex min-w-0 items-start gap-3 py-3" data-upload-row x-bind:data-status="file.status">
                <span class="ui-file-type mt-0.5" aria-hidden="true" x-text="type(file)" data-upload-type></span>
                <div class="flex min-w-0 flex-1 flex-col gap-1.5">
                    <div class="flex flex-wrap items-baseline justify-between gap-x-3">
                        <p class="type-body min-w-0 wrap-anywhere text-ink" x-text="file.name"></p>
                        <p class="type-meta" x-bind:class="file.status === 'failed' && 'text-danger-text!'" x-text="meta(file)"></p>
                    </div>
                    <div class="ui-progress-track relative h-1 overflow-hidden rounded-sm" x-show="file.status !== 'done'" x-bind:data-tone="file.status === 'failed' ? 'danger' : 'default'">
                        <progress class="ui-progress" max="100" x-bind:value="file.progress" x-bind:aria-label="'Upload of ' + file.name" x-bind:aria-valuetext="word(file)"></progress>
                        <span class="ui-progress-fill" aria-hidden="true" x-bind:style="'transform: scaleX(' + file.progress / 100 + ')'"></span>
                    </div>
                </div>
                <div class="flex shrink-0 items-center gap-1">
                    <x-ui.button variant="ghost" size="sm" type="button" x-show="file.status === 'uploading'" x-bind:aria-label="'Cancel upload of ' + file.name" x-on:click="cancel()" data-upload-action="cancel">Cancel</x-ui.button>
                    <x-ui.button variant="ghost" size="sm" type="button" x-show="file.status === 'failed'" x-bind:aria-label="'Retry upload of ' + file.name" x-on:click="retry()" data-upload-action="retry">Retry</x-ui.button>
                    <x-ui.button variant="ghost" size="sm" type="button" x-show="file.status === 'done' || file.status === 'failed'" x-bind:aria-label="'Remove ' + file.name" x-on:click="remove(index)" data-upload-action="remove">Remove</x-ui.button>
                </div>
            </li>
        </template>
    </ul>
    <p class="sr-only" aria-live="polite" x-text="status" data-upload-live></p>
</div>
