<?php

namespace TallUiKit\Support\DataTable;

use Closure;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * One column of a WithDataTable table (docs/ui-kit-tables.md).
 *
 *   Column::make('amount', 'Amount')->numeric()->sortable()->format(fn ($row) => ...)
 *
 * Fields (all public, read by the trait, the views and the properties panel):
 *  - key, label: identity and header text. The key is the URL value of `sort`
 *    and `cols`, so keep it short and stable.
 *  - sortBy: false (not sortable), true (the key's own column), a column name,
 *    a dot path through belongsTo relations (`wallet.user.first_name`, sorted
 *    by a correlated subquery), `count:relation` (withCount), or a Closure
 *    (Builder $query, string $direction).
 *  - searchPaths: columns searched for the term; dot paths search a relation
 *    through whereHas. Empty means not searchable. searchClosure replaces it.
 *  - searchPrefix: match `term%` instead of `%term%`, so a B-tree index on
 *    the column is used (ids, emails, references).
 *  - exactPattern: a regex; when the whole term matches it, the search is an
 *    exact equality on this column's first path and nothing else.
 *  - numeric (right-aligned, numeric role), role (body, strong, meta, mono,
 *    numeric), width (Tailwind classes from a scanned view or Livewire class).
 *  - defaultVisible (hidden() turns it off), required (always shown, cannot
 *    be hidden), ability (capability needed to see, sort, search or export it).
 *  - format (Closure(Model): string|Htmlable|null), view (Blade view for the
 *    cell, given row, column and term), secondary (Closure for a meta line).
 *  - with: relations eager loaded only while the column is visible.
 *  - highlight: wrap search matches in <mark>; on by default for searchable
 *    text columns without a view.
 *  - exportAs (export type text, number, money or date), exportValue
 *    (Closure(Model)), exportable.
 */
final class Column
{
    public const ROLES = ['body', 'strong', 'meta', 'mono', 'numeric'];

    public bool|string|Closure $sortBy = false;

    /** @var list<string> */
    public array $searchPaths = [];

    public ?Closure $searchClosure = null;

    public bool $searchPrefix = false;

    public ?string $exactPattern = null;

    public bool $numeric = false;

    public string $role = 'body';

    public ?string $width = null;

    public bool $defaultVisible = true;

    public bool $required = false;

    public ?string $ability = null;

    public ?Closure $formatter = null;

    public ?string $view = null;

    public ?Closure $secondaryLine = null;

    /** The cell holds two lines (a cell view with a second line), so it gets 8px vertical padding. */
    public bool $wrap = false;

    /** @var list<string> */
    public array $with = [];

    public ?bool $highlightMatches = null;

    public string $exportAs = 'text';

    public ?Closure $exportValue = null;

    public bool $exportable = true;

    private function __construct(public readonly string $key, public readonly string $label) {}

    public static function make(string $key, string $label): self
    {
        if (! preg_match('/^[a-z][a-z0-9_]*$/', $key)) {
            throw new InvalidTableDefinition("Column key [{$key}] must be lower snake case (a-z, 0-9, _), it is used in the URL.");
        }

        return new self($key, $label);
    }

    /** @param  bool|string|Closure(Builder, string): void  $by */
    public function sortable(bool|string|Closure $by = true): self
    {
        $this->sortBy = $by;

        return $this;
    }

    /**
     * @param  bool|string|list<string>|Closure(Builder, string $term, string $like): void  $paths
     */
    public function searchable(bool|string|array|Closure $paths = true, bool $prefix = false): self
    {
        if ($paths instanceof Closure) {
            $this->searchClosure = $paths;
        } else {
            $this->searchPaths = match (true) {
                $paths === true => [$this->key],
                $paths === false => [],
                default => array_values((array) $paths),
            };
        }
        $this->searchPrefix = $prefix;

        return $this;
    }

    /** Exact match on this column when the whole term matches $pattern (a reference like NB-48210). */
    public function exact(string $pattern): self
    {
        if (@preg_match($pattern, '') === false) {
            throw new InvalidTableDefinition("Column [{$this->key}] exact pattern [{$pattern}] is not a valid regex.");
        }
        $this->exactPattern = $pattern;

        return $this;
    }

    public function numeric(bool $numeric = true): self
    {
        $this->numeric = $numeric;
        if ($numeric) {
            $this->role = 'numeric';
        }

        return $this;
    }

    public function role(string $role): self
    {
        if (! in_array($role, self::ROLES, true)) {
            throw new InvalidTableDefinition("Column [{$this->key}] role [{$role}] is not one of ".implode(', ', self::ROLES).'.');
        }
        $this->role = $role;

        return $this;
    }

    public function width(string $classes): self
    {
        $this->width = $classes;

        return $this;
    }

    public function hidden(bool $hidden = true): self
    {
        $this->defaultVisible = ! $hidden;

        return $this;
    }

    public function required(bool $required = true): self
    {
        $this->required = $required;

        return $this;
    }

    public function ability(?string $capability): self
    {
        $this->ability = $capability;

        return $this;
    }

    /** @param  Closure(Model): (string|Htmlable|int|float|null)  $formatter */
    public function format(Closure $formatter): self
    {
        $this->formatter = $formatter;

        return $this;
    }

    public function view(string $view): self
    {
        $this->view = $view;

        return $this;
    }

    /** @param  Closure(Model): (string|null)  $line */
    public function secondary(Closure $line): self
    {
        $this->secondaryLine = $line;

        return $this;
    }

    public function wrap(bool $wrap = true): self
    {
        $this->wrap = $wrap;

        return $this;
    }

    /** @param  string|list<string>  $relations */
    public function with(string|array $relations): self
    {
        $this->with = array_values(array_unique([...$this->with, ...(array) $relations]));

        return $this;
    }

    public function highlight(bool $highlight = true): self
    {
        $this->highlightMatches = $highlight;

        return $this;
    }

    public function export(string $type = 'text', ?Closure $value = null): self
    {
        if (! in_array($type, ['text', 'number', 'money', 'date'], true)) {
            throw new InvalidTableDefinition("Column [{$this->key}] export type [{$type}] is not text, number, money or date.");
        }
        $this->exportAs = $type;
        $this->exportValue = $value;
        $this->exportable = true;

        return $this;
    }

    public function withoutExport(): self
    {
        $this->exportable = false;

        return $this;
    }

    public function isSortable(): bool
    {
        return $this->sortBy !== false;
    }

    public function isSearchable(): bool
    {
        return $this->searchPaths !== [] || $this->searchClosure !== null;
    }

    public function highlights(): bool
    {
        return $this->highlightMatches ?? ($this->isSearchable() && $this->view === null && ! $this->numeric);
    }

    /** The cell's main value: the formatter's output, or the attribute (dot paths follow relations). */
    public function value(Model $row): mixed
    {
        return $this->formatter ? ($this->formatter)($row) : data_get($row, $this->key);
    }

    public function secondaryValue(Model $row): ?string
    {
        $value = $this->secondaryLine ? ($this->secondaryLine)($row) : null;

        return $value === null || $value === '' ? null : (string) $value;
    }

    /**
     * Definition summary for docs and the gallery's properties panel.
     *
     * @return array<string, string>
     */
    public function describe(): array
    {
        $sort = match (true) {
            $this->sortBy === false => 'no',
            $this->sortBy === true => 'yes',
            $this->sortBy instanceof Closure => 'closure',
            default => (string) $this->sortBy,
        };

        return [
            'key' => $this->key,
            'label' => $this->label,
            'sortable' => $sort,
            'searchable' => $this->searchClosure ? 'closure' : ($this->searchPaths === [] ? 'no' : implode(', ', $this->searchPaths).($this->searchPrefix ? ' (prefix)' : '')),
            'exact' => $this->exactPattern ?? '',
            'role' => $this->role,
            'visible' => $this->required ? 'required' : ($this->defaultVisible ? 'default' : 'hidden'),
            'ability' => $this->ability ?? '',
            'cell' => $this->view ? 'view '.$this->view : ($this->formatter ? 'formatter' : 'attribute').($this->secondaryLine ? ' + secondary' : ''),
            'with' => implode(', ', $this->with),
            'export' => $this->exportable ? $this->exportAs : 'no',
        ];
    }
}
