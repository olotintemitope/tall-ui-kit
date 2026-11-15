<?php

namespace TallUiKit\Support\DataTable;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

/**
 * Renders the <tr>s of x-data-table as compact HTML. Rows are the bulk of
 * every Livewire response (sort, search, page), and a Blade loop adds about
 * 8 KB of indentation and block markers per row; this keeps a 25-row page well
 * inside the 40 KB budget (docs/ui-kit-tables.md, Performance). Text is
 * escaped with e(); cell views are rendered with view() and only have their
 * whitespace between tags collapsed.
 *
 * Stacked-layout labels (data-label) and ARIA table roles are added in the
 * browser after each morph (uiDataTable.decorate()), from the header.
 *
 * Classes used here also appear in the data-table views, so Tailwind sees them.
 */
final class RowRenderer
{
    private const ROLE_CLASSES = [
        'strong' => 'type-body-strong',
        'meta' => 'type-meta',
        'mono' => 'type-numeric text-ink-muted',
        'numeric' => 'type-numeric text-ink',
        'body' => 'type-body',
    ];

    /**
     * @param  bool  $lean  leave out the per-cell classes (role and width): the browser
     *                      copies them from each th's data-cell after the morph. The first,
     *                      non-Livewire render keeps them inline so nothing paints unstyled.
     */
    public static function render(TableView $table, bool $lean = false): HtmlString
    {
        $html = '';
        foreach ($table->rows?->items() ?? [] as $row) {
            $html .= self::row($table, $row, $lean);
        }

        return new HtmlString($html);
    }

    private static function row(TableView $table, Model $row, bool $lean): string
    {
        $id = e((string) $row->getKey());
        $meta = $table->rowMeta[(string) $row->getKey()];
        $selected = $table->selectAllMatching || in_array((string) $row->getKey(), $table->selectedIds, true);
        $label = e($meta['label']);

        // Pinned, title, checkbox and actions cells are styled by position (assets.blade.php),
        // and focus keys (row-{id}-check, row-{id}-actions) are derived from the row, so each
        // row carries only what changes.
        $out = '<tr wire:key="r'.$id.'" data-row="'.$id.'"'.($selected ? ' data-selected' : '').'>';

        if ($table->bulkActions !== []) {
            $out .= '<td>'.($table->selectAllMatching
                ? '<input type="checkbox" class="dt-check" checked wire:click="deselectFromAll(\''.$id.'\')"'
                : '<input type="checkbox" class="dt-check" wire:model.live="selected" value="'.$id.'"').' aria-label="Select row '.$label.'"></td>';
        }

        foreach ($table->columns as $column) {
            $second = $column->secondaryValue($row);
            $out .= '<td'.($lean ? '' : ' class="'.e(self::cellClasses($column)).'"').($column->numeric ? ' data-n' : '').($second !== null || $column->wrap ? ' data-wrap' : '').'>'
                .self::cell($table, $column, $row, $second).'</td>';
        }

        if ($table->rowActions !== []) {
            $out .= '<td>'.($meta['actions'] === [] ? '' : '<button type="button" class="dt-act" aria-label="Actions for '.$label.'" aria-haspopup="menu"'
                .' data-actions="'.e(implode(' ', $meta['actions'])).'"'
                // Single-quoted so the JSON's double quotes need no &quot; (read with JSON.parse(dataset.hrefs)).
                .($meta['hrefs'] !== [] ? " data-hrefs='".str_replace("'", '&#039;', htmlspecialchars((string) json_encode($meta['hrefs'], JSON_UNESCAPED_SLASHES), ENT_NOQUOTES))."'" : '')
                .'>'.self::icon('ellipsis').'</button>').'</td>';
        }

        return $out.'</tr>';
    }

    private static function cell(TableView $table, Column $column, Model $row, ?string $second): string
    {
        $highlight = $column->highlights() && $table->term !== '';

        if ($column->view) {
            $main = self::compact(view($column->view, ['row' => $row, 'column' => $column, 'term' => $table->term])->render());
        } else {
            $value = $column->value($row);
            if ($value instanceof Htmlable) {
                $main = $value->toHtml();
            } elseif ($value === null || $value === '') {
                $main = '<span class="text-ink-muted">&ndash;</span>';
            } else {
                $text = (string) $value;
                $inner = $highlight ? TableQuery::highlight($text, $table->term)->toHtml() : e($text);
                // A title only where the text can be cut off: a column with a width, a long value.
                $tip = $column->width !== null && mb_strlen($text) > 24 ? ' title="'.e($text).'"' : '';
                // One line: the td wraps it. Two lines: each line is its own block, and both wrap (never an ellipsis).
                $main = $second === null && $tip === '' ? $inner : '<span class="dt-t"'.$tip.'>'.$inner.'</span>';
            }
        }

        if ($second !== null) {
            $main .= '<span class="dt-t type-meta"'.($column->width !== null && mb_strlen($second) > 28 ? ' title="'.e($second).'"' : '').'>'
                .($highlight ? TableQuery::highlight($second, $table->term)->toHtml() : e($second)).'</span>';
        }

        return $main;
    }

    /** Collapses whitespace between tags and drops Livewire's block markers in a cell view's output. */
    private static function compact(string $html): string
    {
        $html = str_replace(['<!--[if BLOCK]><![endif]-->', '<!--[if ENDBLOCK]><![endif]-->'], '', $html);

        return trim((string) preg_replace(['/>\s+</', '/\s{2,}/'], ['> <', ' '], $html));
    }

    public static function cellClasses(Column $column): string
    {
        return trim(self::ROLE_CLASSES[$column->role].' '.($column->width ?? ''));
    }

    /** An icon from the table's sprite (assets.blade.php): ellipsis, sort, chevron. */
    public static function icon(string $name, string $class = 'size-4'): string
    {
        return '<svg class="'.$class.'" aria-hidden="true"><use href="#dt-i-'.$name.'"/></svg>';
    }

    /**
     * The header row. Sortable headers are buttons inside the th, with aria-sort on the
     * th; the active column shows a chevron in ink, inactive ones a muted double chevron
     * on hover or focus only.
     */
    public static function header(TableView $table): HtmlString
    {
        $stack = $table->layout === 'stack';
        $hasCheck = $table->bulkActions !== [];
        $out = '<tr'.($stack ? ' role="row"' : '').'>';

        if ($hasCheck) {
            $out .= '<th scope="col"'.($stack ? ' role="columnheader"' : '').'>'
                .'<input type="checkbox" class="dt-check" aria-label="Select all rows on this page" wire:click="togglePage" data-fk="select-page"'
                .($table->pageAllSelected ? ' checked' : '')
                .' x-effect="$el.indeterminate = '.($table->pageSomeSelected ? 'true' : 'false').'"'
                .($table->loaded ? '' : ' aria-disabled="true"').'></th>';
        }

        foreach ($table->columns as $i => $column) {
            $active = $table->sort === $column->key;
            $classes = trim('type-label '.($column->width ?? ''));
            $out .= '<th scope="col" class="'.e($classes).'" data-cell="'.e(self::cellClasses($column)).'" wire:key="'.e($table->name).'-th-'.e($column->key).'"'
                .($column->isSortable() ? ' aria-sort="'.($active ? ($table->direction === 'asc' ? 'ascending' : 'descending') : 'none').'"' : '')
                .($column->numeric ? ' data-n' : '')
                .($stack ? ' role="columnheader"' : '').'>';

            if ($column->isSortable()) {
                $icon = $active
                    ? self::icon('chevron', 'size-3.5 shrink-0 text-ink'.($table->direction === 'asc' ? ' rotate-180' : ''))
                    : self::icon('sort', 'dt-sort-idle size-3.5 shrink-0 text-ink-muted');
                $out .= '<button type="button" wire:click="sortBy(\''.e($column->key).'\')" data-fk="sort-'.e($column->key).'" class="dt-sort'.($active ? ' text-ink' : '').'">'
                    .($column->numeric ? $icon.'<span>'.e($column->label).'</span>' : '<span>'.e($column->label).'</span>'.$icon).'</button>';
            } else {
                $out .= e($column->label);
            }
            $out .= '</th>';
        }

        if ($table->rowActions !== []) {
            $out .= '<th scope="col"'.($stack ? ' role="columnheader"' : '').'><span class="sr-only">Actions</span></th>';
        }

        return new HtmlString($out.'</tr>');
    }
}
