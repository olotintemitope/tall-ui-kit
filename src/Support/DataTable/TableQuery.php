<?php

namespace TallUiKit\Support\DataTable;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Query and markup helpers behind WithDataTable: search terms, LIKE escaping,
 * search across columns and relations, sorts through relations and counts,
 * and <mark> highlighting. Stateless and side-effect free.
 */
final class TableQuery
{
    /** LIKE escape character. Not a backslash: MySQL and SQLite disagree on how to write one. */
    public const ESCAPE = '!';

    public const MIN_TERM = 2;

    public const MAX_TERM = 100;

    /** Trimmed, single-spaced, capped term; '' when shorter than MIN_TERM. */
    public static function term(string $raw): string
    {
        $term = trim((string) preg_replace('/\s+/u', ' ', $raw));
        $term = mb_substr($term, 0, self::MAX_TERM);

        return mb_strlen($term) >= self::MIN_TERM ? $term : '';
    }

    /** Escapes LIKE wildcards so a user's % or _ matches literally. */
    public static function escapeLike(string $value): string
    {
        return str_replace([self::ESCAPE, '%', '_'], [self::ESCAPE.self::ESCAPE, self::ESCAPE.'%', self::ESCAPE.'_'], $value);
    }

    /**
     * Each word of the term must match at least one searchable column, so
     * "tola ade" finds first name Tola, last name Ade. Prefix columns use
     * `word%` (index friendly), the rest `%word%`. Dot paths are grouped per
     * relation into one whereHas.
     *
     * @param  list<Column>  $columns
     */
    public static function search(Builder $query, array $columns, string $term): void
    {
        $words = array_slice(array_values(array_filter(explode(' ', $term))), 0, 5);

        $query->where(function (Builder $outer) use ($columns, $words): void {
            foreach ($words as $word) {
                $outer->where(function (Builder $any) use ($columns, $word): void {
                    $relations = [];
                    foreach ($columns as $column) {
                        if ($column->searchClosure) {
                            $any->orWhere(fn (Builder $q) => ($column->searchClosure)($q, $word, self::escapeLike($word)));

                            continue;
                        }
                        foreach ($column->searchPaths as $path) {
                            $like = $column->searchPrefix ? self::escapeLike($word).'%' : '%'.self::escapeLike($word).'%';
                            if (str_contains($path, '.')) {
                                $relations[substr($path, 0, strrpos($path, '.'))][] = [substr($path, strrpos($path, '.') + 1), $like];
                            } else {
                                self::orLike($any, $any->qualifyColumn($path), $like);
                            }
                        }
                    }
                    foreach ($relations as $relation => $pairs) {
                        $any->orWhereHas($relation, function (Builder $q) use ($pairs): void {
                            $q->where(function (Builder $q) use ($pairs): void {
                                foreach ($pairs as [$column, $like]) {
                                    self::orLike($q, $q->qualifyColumn($column), $like);
                                }
                            });
                        });
                    }
                });
            }
        });
    }

    private static function orLike(Builder $query, string $qualified, string $like): void
    {
        $wrapped = $query->getQuery()->getGrammar()->wrap($qualified);
        $query->orWhereRaw("{$wrapped} like ? escape '".self::ESCAPE."'", [$like]);
    }

    /**
     * Orders by a column definition's sort. Strings are a column, a belongsTo
     * dot path (correlated subquery, no join, so selected columns and keys
     * stay the base table's), or `count:relation`.
     */
    public static function sort(Builder $query, Column $column, string $direction): void
    {
        $by = $column->sortBy === true ? $column->key : $column->sortBy;

        if ($by instanceof Closure) {
            $by($query, $direction);

            return;
        }

        if (str_starts_with($by, 'count:')) {
            $relation = substr($by, 6);
            $query->withCount($relation)->orderBy(Str::snake($relation).'_count', $direction);

            return;
        }

        if (str_contains($by, '.')) {
            $query->orderBy(self::relationSubquery($query, explode('.', $by)), $direction);

            return;
        }

        $query->orderBy($query->qualifyColumn($by), $direction);
    }

    /**
     * select related.col from related where related.owner = parent.foreign,
     * nested once per belongsTo hop.
     *
     * @param  list<string>  $segments  relation, [relation, ...] column
     */
    private static function relationSubquery(Builder $parent, array $segments): Builder
    {
        $relationName = array_shift($segments);
        $relation = $parent->getModel()->{$relationName}();

        if (! $relation instanceof BelongsTo) {
            throw new InvalidTableDefinition("Sorting through [{$relationName}] needs a belongsTo relation; use a sort closure for other relations.");
        }

        $related = $relation->getRelated()->newQuery();
        $related->whereColumn($related->qualifyColumn($relation->getOwnerKeyName()), $parent->qualifyColumn($relation->getForeignKeyName()));

        if (count($segments) === 1) {
            return $related->select($related->qualifyColumn($segments[0]))->limit(1);
        }

        return $related->select(['dt_sort' => self::relationSubquery($related, $segments)])->limit(1);
    }

    /**
     * Escapes $text and wraps every case-insensitive match of the term's words
     * in the kit's mark style.
     */
    public static function highlight(string $text, string $term): HtmlString
    {
        $words = array_values(array_filter(explode(' ', $term), fn (string $w): bool => mb_strlen($w) >= self::MIN_TERM));
        if ($term === '' || $words === []) {
            return new HtmlString(e($text));
        }

        usort($words, fn (string $a, string $b): int => mb_strlen($b) <=> mb_strlen($a));
        $pattern = '/('.implode('|', array_map(fn (string $w): string => preg_quote($w, '/'), $words)).')/iu';
        $parts = preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [$text];

        $html = '';
        foreach ($parts as $i => $part) {
            $html .= $i % 2 === 1 ? '<mark>'.e($part).'</mark>' : e($part);
        }

        return new HtmlString($html);
    }
}
