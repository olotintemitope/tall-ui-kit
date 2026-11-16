<?php

namespace Workbench\App\Docs;

use Symfony\Component\Process\Process;

/**
 * The changelog, read from git: one release per v* tag (newest first) with its
 * conventional-commit subjects grouped by type, plus the commits after the newest tag
 * as "Unreleased". Needs the full history (CI checks out with fetch-depth 0).
 */
final class Changelog
{
    private const GROUPS = ['feat' => 'Features', 'fix' => 'Fixes', 'perf' => 'Performance', 'docs' => 'Docs', 'test' => 'Tests', 'refactor' => 'Refactors', 'build' => 'Build', 'ci' => 'CI', 'chore' => 'Chores', 'other' => 'Other'];

    /** @return list<array{version: string, date: string|null, groups: array<string, list<string>>}> */
    public static function releases(): array
    {
        $tags = array_values(array_filter(explode("\n", self::git(['tag', '--list', 'v*', '--sort=-v:refname', '--format=%(refname:short)|%(creatordate:short)']))));
        $releases = [];
        $previous = null;

        $head = $tags === [] ? 'HEAD' : explode('|', $tags[0])[0].'..HEAD';
        $unreleased = self::commits($head);
        if ($unreleased !== []) {
            $releases[] = ['version' => 'Unreleased', 'date' => null, 'groups' => self::group($unreleased)];
        }

        foreach ($tags as $i => $line) {
            [$tag, $date] = explode('|', $line) + [1 => null];
            $older = isset($tags[$i + 1]) ? explode('|', $tags[$i + 1])[0] : null;
            $releases[] = ['version' => $tag, 'date' => $date, 'groups' => self::group(self::commits($older ? $older.'..'.$tag : $tag))];
        }

        return $releases;
    }

    public static function currentVersion(): string
    {
        $tag = trim(self::git(['describe', '--tags', '--abbrev=0']));

        return ltrim($tag !== '' ? $tag : '0.0.0', 'v');
    }

    /** @return list<string> */
    private static function commits(string $range): array
    {
        return array_values(array_filter(explode("\n", self::git(['log', $range, '--no-merges', '--format=%s']))));
    }

    /** @return array<string, list<string>> */
    private static function group(array $subjects): array
    {
        $groups = [];
        foreach ($subjects as $subject) {
            $type = 'other';
            $text = $subject;
            if (preg_match('/^(\w+)(\([^)]*\))?!?:\s*(.+)$/', $subject, $m)) {
                $type = array_key_exists($m[1], self::GROUPS) ? $m[1] : 'other';
                $text = ($m[2] !== '' ? trim($m[2], '()').': ' : '').$m[3];
            }
            $groups[self::GROUPS[$type]][] = ucfirst($text);
        }

        return array_intersect_key(array_merge(array_fill_keys(self::GROUPS, null), $groups), $groups);
    }

    private static function git(array $args): string
    {
        $process = new Process(['git', ...$args], dirname(__DIR__, 3));
        $process->run();

        return $process->isSuccessful() ? $process->getOutput() : '';
    }
}
