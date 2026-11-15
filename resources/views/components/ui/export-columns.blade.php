{{--
    Column picker for exports, in an x-ui.dialog titled "Columns to export".
    Props: name (dialog name; open with $dispatch('open-dialog', name)), columns (from
    WithExports::exportColumnOptions(): key, heading, description, required, checked,
    default, position), formats (csv and/or pdf), format (initial), landscapeAfter (8),
    suggestCsvAfter (12).
    Rows: a 24px drag handle (Alt+ArrowUp/ArrowDown moves the focused column), a checkbox,
    the heading in body-strong and the description in meta, then 24px Move up and Move
    down buttons: the single-pointer alternative to dragging (WCAG 2.5.7). Every move is
    announced in an aria-live region. Required columns are checked, disabled (dashed
    border, ink-muted fill, never faded) and tagged "Required". A select-all box
    shows an indeterminate state; a search box appears above 12 columns. Export stays
    disabled until at least one column is chosen. Export calls runExport(format, scope,
    keys) from the surrounding x-ui.export-menu, or dispatches `export-columns` on
    window with { name, format, columns } when used on its own. A window event
    `export-columns-load` with { name, columns } replaces the list (lazy loading).
--}}
@props([
    'name' => 'export-columns',
    'columns' => [],
    'formats' => ['csv', 'pdf'],
    'format' => 'csv',
    'landscapeAfter' => null,
    'suggestCsvAfter' => null,
])

@php
    $columns = array_values($columns);
    $landscapeAfter ??= (int) config('tall-ui-kit.exports.pdf_landscape_after', 8);
    $suggestCsvAfter ??= (int) config('tall-ui-kit.exports.pdf_suggest_csv_after', 12);
    $formatOptions = array_intersect_key(['csv' => 'CSV', 'pdf' => 'PDF'], array_flip($formats));
    $format = array_key_exists($format, $formatOptions) ? $format : array_key_first($formatOptions);
    $hintId = $name.'-reorder-hint';

    $state = 'columns: '.\Illuminate\Support\Js::from($columns).",\n"
        .'committed: '.\Illuminate\Support\Js::from($columns).",\n"
        .'format: '.\Illuminate\Support\Js::from($format).",\n"
        .'pickerName: '.\Illuminate\Support\Js::from($name).",\n"
        .'landscapeAfter: '.(int) $landscapeAfter.",\n"
        .'suggestCsvAfter: '.(int) $suggestCsvAfter.",\n";

    $alpine = '{'.$state.<<<'JS'
        query: '',
        announcement: '',
        dragKey: null,
        get checkedCount() { return this.columns.filter((c) => c.checked).length },
        get allChecked() { return this.columns.every((c) => c.checked) },
        get someChecked() { return this.columns.some((c) => c.checked) },
        get canExport() { return this.checkedCount > 0 },
        matches(column) {
            const q = this.query.trim().toLowerCase();
            return q === '' || column.heading.toLowerCase().includes(q) || (column.description ?? '').toLowerCase().includes(q);
        },
        toggleAll(checked) {
            this.columns = this.columns.map((c) => ({ ...c, checked: c.required ? true : checked }));
        },
        move(key, step) {
            const from = this.columns.findIndex((c) => c.key === key);
            const to = from + step;
            if (from < 0 || to < 0 || to >= this.columns.length) return;
            const next = [...this.columns];
            const [column] = next.splice(from, 1);
            next.splice(to, 0, column);
            this.columns = next;
            this.announcement = column.heading + ' moved to position ' + (to + 1) + ' of ' + next.length + '.';
            this.$nextTick(() => this.$root.querySelector('[data-column-handle=' + JSON.stringify(key) + ']')?.focus());
        },
        /* The arrow buttons keep focus on the pressed button; at an end it hands focus to the other arrow. */
        nudge(key, step) {
            this.move(key, step);
            this.$nextTick(() => {
                const row = this.$root.querySelector('[data-column=' + JSON.stringify(key) + ']');
                const same = row?.querySelector(step < 0 ? '[data-column-up]' : '[data-column-down]');
                const other = row?.querySelector(step < 0 ? '[data-column-down]' : '[data-column-up]');
                (same && same.getAttribute('aria-disabled') !== 'true' ? same : other)?.focus();
            });
        },
        dragStart(event, key) {
            this.dragKey = key;
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', key);
            const row = event.target.closest('li');
            if (row) event.dataTransfer.setDragImage(row, 12, 16);
        },
        dragOver(event, key) {
            if (this.dragKey === null || this.dragKey === key) return;
            event.preventDefault();
            const from = this.columns.findIndex((c) => c.key === this.dragKey);
            const to = this.columns.findIndex((c) => c.key === key);
            this.move(this.dragKey, to - from);
        },
        dragEnd() { this.dragKey = null },
        reset() {
            this.columns = [...this.columns]
                .sort((a, b) => a.position - b.position)
                .map((c) => ({ ...c, checked: c.default || c.required }));
            this.announcement = 'Columns reset to the default selection and order.';
        },
        restore() { this.columns = this.committed.map((c) => ({ ...c })); this.query = '' },
        submit() {
            if (! this.canExport) return;
            const keys = this.columns.filter((c) => c.checked).map((c) => c.key);
            this.committed = this.columns.map((c) => ({ ...c }));
            this.$dispatch('close-dialog', this.pickerName);
            if (typeof this.runExport === 'function') {
                this.runExport(this.format, this.format === 'pdf' ? 'detailed' : 'filtered', keys);
            } else {
                window.dispatchEvent(new CustomEvent('export-columns', { detail: { name: this.pickerName, format: this.format, columns: keys } }));
            }
        },
    }
    JS;
@endphp

<x-ui.dialog
    :name="$name"
    title="Columns to export"
    description="Choose the columns and put them in the order you want: drag a row, or use its arrows."
    width="lg"
    :x-data="$alpine"
    x-on:open-dialog.window="if ($event.detail === pickerName || $event.detail?.name === pickerName) restore()"
    x-on:export-columns-load.window="if ($event.detail?.name === pickerName) { committed = $event.detail.columns; restore() }"
    data-export-columns
    {{ $attributes }}
>
    <div class="flex flex-col gap-3">
        <div x-show="columns.length > 12" @if (count($columns) <= 12) x-cloak @endif data-columns-search>
            <x-ui.search x-model="query" size="sm" width="full" placeholder="Search columns" aria-label="Search columns" />
        </div>

        <div class="flex items-center justify-between gap-3 border-b border-line pb-2">
            <label class="group flex cursor-pointer items-center gap-3">
                <span class="relative flex h-5 w-4 shrink-0 items-center justify-center">
                    <input
                        type="checkbox"
                        class="peer size-4 cursor-pointer appearance-none rounded-sm border border-control bg-surface transition-colors duration-100 checked:border-secondary checked:bg-secondary indeterminate:border-secondary indeterminate:bg-secondary group-hover:border-control-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus"
                        x-bind:checked="allChecked"
                        x-effect="$el.indeterminate = someChecked && ! allChecked"
                        x-on:change="toggleAll($event.target.checked)"
                        data-select-all
                    />
                    <x-ui.icon name="check" :stroke-width="3" class="pointer-events-none absolute size-3 text-on-secondary opacity-0 peer-checked:opacity-100 peer-indeterminate:opacity-0" />
                    <span class="pointer-events-none absolute h-0.5 w-2 rounded-sm bg-on-secondary opacity-0 peer-indeterminate:opacity-100" aria-hidden="true"></span>
                </span>
                <span class="type-body-strong select-none">Select all</span>
            </label>
            <span class="type-meta" x-text="checkedCount + ' of ' + columns.length + ' selected'">{{ collect($columns)->where('checked', true)->count() }} of {{ count($columns) }} selected</span>
        </div>

        <p id="{{ $hintId }}" class="sr-only">Press Alt and Arrow Up or Arrow Down to move this column, drag the handle, or use the Move up and Move down buttons.</p>

        <ul class="-my-1 max-h-[min(24rem,50vh)] overflow-y-auto" role="list" data-column-list>
            <template x-for="(column, index) in columns" :key="column.key">
                <li
                    x-show="matches(column)"
                    class="flex items-start gap-2 rounded-md py-1.5 pr-2 transition-colors duration-100"
                    x-bind:class="dragKey === column.key ? 'bg-surface-subtle' : ''"
                    x-on:dragover="dragOver($event, column.key)"
                    x-on:drop.prevent="dragEnd()"
                    x-bind:data-column="column.key"
                >
                    <button
                        type="button"
                        draggable="true"
                        class="-my-0.5 flex size-6 shrink-0 cursor-grab items-center justify-center rounded-sm text-ink-muted hover:bg-surface-subtle hover:text-ink focus-ring active:cursor-grabbing"
                        x-bind:aria-label="'Move ' + column.heading + ', position ' + (index + 1) + ' of ' + columns.length"
                        aria-describedby="{{ $hintId }}"
                        x-bind:data-column-handle="column.key"
                        x-on:keydown.alt.up.prevent="move(column.key, -1)"
                        x-on:keydown.alt.down.prevent="move(column.key, 1)"
                        x-on:dragstart="dragStart($event, column.key)"
                        x-on:dragend="dragEnd()"
                    >
                        <svg viewBox="0 0 16 16" class="size-4" fill="currentColor" aria-hidden="true"><circle cx="6" cy="3.5" r="1.25"/><circle cx="10" cy="3.5" r="1.25"/><circle cx="6" cy="8" r="1.25"/><circle cx="10" cy="8" r="1.25"/><circle cx="6" cy="12.5" r="1.25"/><circle cx="10" cy="12.5" r="1.25"/></svg>
                    </button>

                    <label class="group flex min-w-0 flex-1 cursor-pointer items-start gap-3 has-disabled:cursor-not-allowed" x-bind:for="'{{ $name }}-col-' + column.key">
                        <span class="relative flex h-5 w-4 shrink-0 items-center justify-center">
                            <input
                                type="checkbox"
                                class="peer size-4 cursor-pointer appearance-none rounded-sm border border-control bg-surface transition-colors duration-100 checked:border-secondary checked:bg-secondary group-hover:border-control-hover focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus disabled:cursor-not-allowed disabled:border-dashed disabled:bg-surface-subtle disabled:checked:border-solid disabled:checked:border-ink-muted disabled:checked:bg-ink-muted"
                                x-bind:id="'{{ $name }}-col-' + column.key"
                                x-model="column.checked"
                                x-bind:disabled="column.required"
                                x-bind:aria-describedby="column.description || column.required ? '{{ $name }}-col-' + column.key + '-meta' : null"
                            />
                            <x-ui.icon name="check" :stroke-width="3" class="pointer-events-none absolute size-3 text-on-secondary opacity-0 peer-checked:opacity-100" />
                        </span>
                        <span class="flex min-w-0 flex-col">
                            <span class="type-body-strong select-none" x-text="column.heading"></span>
                            <span
                                class="type-meta"
                                x-show="column.description || column.required"
                                x-bind:id="'{{ $name }}-col-' + column.key + '-meta'"
                                x-text="column.required ? (column.description ? 'Required. ' + column.description : 'Required') : column.description"
                            ></span>
                        </span>
                    </label>

                    {{-- Single-pointer alternative to dragging: 24px targets, unavailable at the ends (aria-disabled keeps focus). --}}
                    <div class="-my-0.5 flex shrink-0 items-center gap-0.5" data-column-arrows>
                        <button
                            type="button"
                            class="flex size-6 items-center justify-center rounded-sm border border-transparent text-ink-muted hover:bg-surface-subtle hover:text-ink focus-ring aria-disabled:cursor-not-allowed aria-disabled:border-dashed aria-disabled:border-control aria-disabled:bg-surface-subtle aria-disabled:hover:text-ink-muted"
                            x-bind:aria-label="'Move ' + column.heading + ' up'"
                            x-bind:aria-disabled="index === 0 ? 'true' : 'false'"
                            x-on:click="if (index > 0) nudge(column.key, -1)"
                            data-column-up
                        >
                            <x-ui.icon name="chevron-up" class="size-4" />
                        </button>
                        <button
                            type="button"
                            class="flex size-6 items-center justify-center rounded-sm border border-transparent text-ink-muted hover:bg-surface-subtle hover:text-ink focus-ring aria-disabled:cursor-not-allowed aria-disabled:border-dashed aria-disabled:border-control aria-disabled:bg-surface-subtle aria-disabled:hover:text-ink-muted"
                            x-bind:aria-label="'Move ' + column.heading + ' down'"
                            x-bind:aria-disabled="index === columns.length - 1 ? 'true' : 'false'"
                            x-on:click="if (index < columns.length - 1) nudge(column.key, 1)"
                            data-column-down
                        >
                            <x-ui.icon name="chevron-down" class="size-4" />
                        </button>
                    </div>
                </li>
            </template>
        </ul>

        <p class="sr-only" aria-live="polite" x-text="announcement" data-column-announcer></p>

        <div class="flex min-h-4 flex-col gap-0.5" aria-live="polite">
            <p class="type-caption" x-show="! canExport" x-cloak data-columns-empty>Choose at least one column to export.</p>
            <p class="type-caption" x-show="canExport && format === 'pdf' && checkedCount > landscapeAfter && checkedCount <= suggestCsvAfter" x-cloak>
                More than <span x-text="landscapeAfter"></span> columns: the PDF uses landscape pages.
            </p>
            <p class="type-caption" x-show="canExport && format === 'pdf' && checkedCount > suggestCsvAfter" x-cloak>
                <span x-text="checkedCount"></span> columns is a lot for a PDF page, even in landscape. CSV keeps every column readable.
            </p>
        </div>
    </div>

    <x-slot:footer>
        <div class="flex w-full flex-wrap items-center gap-2">
            <x-ui.button variant="ghost" size="sm" class="mr-auto" x-on:click="reset()" data-columns-reset>Reset to default</x-ui.button>
            <div class="ml-auto flex items-center gap-2">
                @if (count($formatOptions) > 1)
                    <x-ui.segmented :options="$formatOptions" :value="$format" size="sm" label="File format" x-model="format" />
                @endif
                <x-ui.button variant="outline" size="sm" x-on:click="$dispatch('close-dialog', pickerName)">Cancel</x-ui.button>
                <x-ui.button size="sm" x-on:click="submit()" x-bind:disabled="! canExport" data-columns-export>Export</x-ui.button>
            </div>
        </div>
    </x-slot:footer>
</x-ui.dialog>
