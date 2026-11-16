<?php

namespace Workbench\App\Docs;

use Illuminate\Support\Str;
use Throwable;

/**
 * Reads one kit Blade component so its docs page is generated from the source: the
 * leading {{-- --}} comment (description), the @props array (names, defaults and an
 * inferred type), the prop descriptions found in that comment, the named slots the
 * view reads and the browser events it dispatches or listens for.
 */
final class ComponentSource
{
    public function __construct(
        public readonly string $name,
        public readonly string $path,
        public readonly string $comment,
        /** @var list<array{name: string, type: string, default: string, description: string}> */
        public readonly array $props,
        /** @var list<string> */
        public readonly array $slots,
        /** @var list<string> */
        public readonly array $dispatches,
        /** @var list<string> */
        public readonly array $listens,
    ) {}

    /** The component's `x-ui.*` name for a file under resources/views/components. */
    public static function nameFor(string $relative): string
    {
        $name = str_replace(['/', '.blade.php'], ['.', ''], $relative);

        return Str::endsWith($name, '.index') ? Str::beforeLast($name, '.index') : $name;
    }

    public static function read(string $root, string $relative): self
    {
        $path = $root.'/'.$relative;
        $source = (string) file_get_contents($path);

        $comment = '';
        if (preg_match('/^\s*\{\{--(.*?)--\}\}/s', $source, $m)) {
            $comment = self::unindent($m[1]);
        }

        $defaults = self::props($source);
        $aliases = self::aliases($comment);
        $props = [];
        foreach ($defaults as $prop => $default) {
            $props[] = [
                'name' => $prop,
                'type' => self::type($default),
                'default' => self::show($default),
                'description' => self::describe($prop, $comment, $aliases),
            ];
        }

        return new self(
            name: self::nameFor($relative),
            path: 'resources/views/components/'.$relative,
            comment: $comment,
            props: $props,
            slots: self::slots($source, array_keys($defaults)),
            dispatches: self::matches('/\$dispatch\(\s*[\'"]([a-z][\w:.-]*)[\'"]/i', $source),
            listens: self::matches('/x-on:([a-z][\w:-]*)\.window/i', $source),
        );
    }

    /** The first paragraph of the comment, as plain sentences. */
    public function summary(): string
    {
        $first = preg_split('/\n\s*\n/', trim($this->comment))[0] ?? '';
        $first = preg_replace('/\s+/', ' ', $first);
        // Stop before the props list; the table below carries it.
        $first = preg_split('/\s(?=Props?:)/', $first)[0];

        return trim($first);
    }

    /** @return array<string, mixed> */
    private static function props(string $source): array
    {
        if (! preg_match('/@props\((\[.*?\])\)\s*\n/s', $source, $m)) {
            return [];
        }

        try {
            $array = eval('return '.$m[1].';');
        } catch (Throwable) {
            return [];
        }

        $props = [];
        foreach ((array) $array as $key => $value) {
            is_int($key) ? $props[$value] = null : $props[$key] = $value;
        }

        return $props;
    }

    /** @return list<string> */
    private static function slots(string $source, array $props): array
    {
        $names = self::matches('/(?:@isset\(|isset\()\$([a-zA-Z]\w*)\)/', $source);
        $names = array_merge($names, self::matches('/\$([a-zA-Z]\w*)\?->(?:attributes|isNotEmpty|isEmpty|toHtml)/', $source));
        $names = array_merge($names, self::matches('/\$([a-zA-Z]\w*)->(?:attributes|isNotEmpty|isEmpty)\b/', $source));
        $skip = array_merge($props, ['attributes', 'slot', 'errors', 'loop', 'item', 'option', 'row', 'column', 'table', 'paginator', 'tab', 'node', 'step', 'this', 'app', 'message', 'value']);
        $slots = array_values(array_unique(array_diff($names, $skip)));
        if (str_contains($source, '{{ $slot }}') || str_contains($source, '$slot->')) {
            array_unshift($slots, 'default');
        }

        return array_values(array_unique($slots));
    }

    /** @return list<string> */
    private static function matches(string $pattern, string $source): array
    {
        preg_match_all($pattern, $source, $m);

        return array_values(array_unique($m[1]));
    }

    /** Aliases listed as "`old` is an alias of `new`" in the comment, so both get a row. */
    private static function aliases(string $comment): array
    {
        preg_match_all('/`?(\w+)`? is an alias (?:of|for) `?(\w+)`?/', $comment, $m, PREG_SET_ORDER);

        return collect($m)->mapWithKeys(fn ($x) => [$x[1] => $x[2]])->all();
    }

    private static function describe(string $prop, string $comment, array $aliases): string
    {
        $flat = preg_replace('/\s+/', ' ', $comment);
        // "prop (text)" with balanced parentheses, or "prop: text" up to the next separator.
        if (preg_match('/(?<![\w-])`?'.preg_quote($prop, '/').'`?\s*(\((?:[^()]|(?1))*\))/', $flat, $m)) {
            return trim(substr($m[1], 1, -1));
        }
        if (preg_match('/(?<![\w-])`?'.preg_quote($prop, '/').'`?\s*:\s*([^;.]+)/', $flat, $m) && strlen($m[1]) < 220) {
            return trim($m[1]);
        }
        if (isset($aliases[$prop])) {
            return 'Alias of `'.$aliases[$prop].'`.';
        }

        return '';
    }

    private static function type(mixed $default): string
    {
        return match (true) {
            is_bool($default) => 'bool',
            is_int($default) => 'int',
            is_float($default) => 'float',
            is_array($default) => 'array',
            is_string($default) => 'string',
            default => 'mixed',
        };
    }

    private static function show(mixed $default): string
    {
        return match (true) {
            $default === null => 'null',
            is_bool($default) => $default ? 'true' : 'false',
            is_array($default) => $default === [] ? '[]' : json_encode($default, JSON_UNESCAPED_SLASHES),
            is_string($default) => "'".$default."'",
            default => (string) $default,
        };
    }

    private static function unindent(string $text): string
    {
        $lines = preg_split('/\R/', trim($text, "\n\r"));
        $indent = collect($lines)->filter(fn ($l) => trim($l) !== '')->map(fn ($l) => strlen($l) - strlen(ltrim($l)))->min() ?? 0;

        return trim(implode("\n", array_map(fn ($l) => substr($l, $indent), $lines)));
    }
}
