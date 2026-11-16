<?php

namespace Workbench\App\Docs;

use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

/**
 * The docs site's map: every guide page, every pattern and one page per component
 * family member. Each component page lists the Blade files it documents; a test
 * checks that every file under resources/views/components belongs to exactly one
 * page, so a new component cannot ship without docs.
 */
final class Catalog
{
    /** Families in navigation order, each page => [title, the component names it documents]. */
    public const FAMILIES = [
        'Foundations' => [
            'head' => ['Head', ['ui.head']],
            'theme-toggle' => ['Theme toggle', ['ui.theme-toggle']],
            'logo' => ['Logo', ['ui.logo']],
            'icon' => ['Icon', ['ui.icon']],
            'button' => ['Button', ['ui.button']],
            'badge' => ['Badge', ['ui.badge']],
            'kbd' => ['Kbd', ['ui.kbd']],
            'avatar' => ['Avatar', ['ui.avatar', 'ui.avatar-group']],
        ],
        'Forms' => [
            'form' => ['Form', ['ui.form', 'ui.form-page', 'ui.form-section', 'ui.form-actions', 'ui.panel']],
            'form-layout' => ['Form layouts', ['ui.form-layout.single', 'ui.form-layout.inline', 'ui.form-layout.split', 'ui.form-layout.section-nav', 'ui.form-layout.wizard']],
            'field' => ['Field', ['ui.field', 'ui.field-row', 'ui.fieldset', 'ui.label', 'ui.output']],
            'input' => ['Input', ['ui.input', 'ui.tel', 'ui.password', 'ui.search']],
            'textarea' => ['Textarea', ['ui.textarea']],
            'select' => ['Select', ['ui.select']],
            'combobox' => ['Combobox', ['ui.combobox']],
            'checkbox' => ['Checkbox', ['ui.checkbox']],
            'radio-group' => ['Radio group', ['ui.radio-group']],
            'switch' => ['Switch', ['ui.switch']],
            'number-input' => ['Number input', ['ui.number-input']],
            'otp' => ['One-time code', ['ui.otp']],
            'date-picker' => ['Date and time pickers', ['ui.date', 'ui.datetime', 'ui.time-input', 'ui.month', 'ui.week', 'ui.date-range', 'ui.date-picker']],
            'slider' => ['Slider and range', ['ui.slider', 'ui.range']],
            'color' => ['Colour', ['ui.color']],
            'tag-input' => ['Tag input', ['ui.tag-input']],
            'file' => ['File', ['ui.file']],
            'error-summary' => ['Error summary', ['ui.error-summary']],
        ],
        'Navigation' => [
            'tabs' => ['Tabs', ['ui.tabs', 'ui.tabs.panel']],
            'segmented' => ['Segmented control', ['ui.segmented']],
            'dropdown' => ['Dropdown menu', ['ui.dropdown', 'ui.dropdown.item', 'ui.dropdown.sub', 'ui.dropdown.label', 'ui.dropdown.separator']],
            'sidebar' => ['Sidebar', ['ui.sidebar', 'ui.sidebar.header', 'ui.sidebar.section', 'ui.sidebar.item', 'ui.sidebar.group', 'ui.sidebar.account']],
            'breadcrumb' => ['Breadcrumb', ['ui.breadcrumb']],
            'pagination' => ['Pagination', ['ui.pagination', 'ui.load-more']],
            'page-header' => ['Page header', ['ui.page-header']],
            'link' => ['Link', ['ui.link', 'ui.link-arrow']],
        ],
        'Overlays' => [
            'dialog' => ['Dialog', ['ui.dialog', 'ui.dialog.base', 'ui.dialog.form', 'ui.dialog.steps', 'ui.dialog.fullscreen', 'ui.dialog.action']],
            'sheet' => ['Sheet', ['ui.sheet']],
            'confirm' => ['Confirm', ['ui.confirm', 'ui.confirm.destructive', 'ui.confirm.reason', 'ui.confirm-dialog', 'ui.alert-dialog']],
            'popover' => ['Popover', ['ui.popover']],
            'tooltip' => ['Tooltip', ['ui.tooltip']],
            'lightbox' => ['Lightbox', ['ui.lightbox']],
        ],
        'Feedback' => [
            'alert' => ['Alert', ['ui.alert']],
            'banner' => ['Banner', ['ui.banner']],
            'toast' => ['Toast', ['ui.toast', 'ui.toaster']],
            'empty-state' => ['Empty state', ['ui.empty-state']],
            'loading' => ['Loading', ['ui.spinner', 'ui.loading-inline', 'ui.loading-overlay', 'ui.progress-bar']],
            'skeleton' => ['Skeleton', ['ui.skeleton', 'ui.skeleton.line', 'ui.skeleton.text', 'ui.skeleton.avatar', 'ui.skeleton.stat', 'ui.skeleton.stats', 'ui.skeleton.table', 'ui.skeleton.card', 'ui.skeleton.chart', 'ui.skeleton.grid', 'ui.skeleton.list']],
            'progress' => ['Progress', ['ui.progress', 'ui.progress-ring', 'ui.progress-steps', 'ui.meter']],
            'upload-progress' => ['Upload progress', ['ui.upload-progress', 'ui.upload-progress.list']],
            'notifications' => ['Notifications', ['ui.notification-bell', 'ui.notification-panel', 'ui.notification-group', 'ui.notification-item']],
            'checklist' => ['Checklist', ['ui.checklist', 'ui.checklist.item']],
            'stepper' => ['Stepper', ['ui.stepper']],
        ],
        'Data display' => [
            'card' => ['Card', ['ui.card', 'ui.card.header', 'ui.card.title', 'ui.card.description', 'ui.card.content', 'ui.card.footer', 'ui.card.summary', 'ui.card.list', 'ui.card.row', 'ui.card.table', 'ui.card.chart', 'ui.card.detail', 'ui.card.detail-item', 'ui.card.link', 'ui.card.profile', 'ui.card.selectable']],
            'stat' => ['Stat', ['ui.stat', 'ui.stat-strip', 'ui.delta']],
            'chart' => ['Chart', ['ui.chart', 'ui.chart.sparkline', 'ui.segment-bar']],
            'table' => ['Table', ['ui.table', 'ui.table.th', 'ui.table.row', 'ui.table.td']],
            'data-table' => ['Data table', ['data-table', 'data-table.header', 'data-table.toolbar', 'data-table.filter', 'data-table.tokens', 'data-table.bulk-bar', 'data-table.row-menu', 'data-table.states', 'data-table.footer', 'data-table.pagination', 'data-table.properties', 'data-table.assets', 'ui.filter-token']],
            'export' => ['Export', ['ui.export-menu', 'ui.export-columns']],
            'key-values' => ['Key values', ['ui.kv-list', 'ui.kv', 'ui.dl', 'ui.dl.item']],
            'ranked-list' => ['Ranked list', ['ui.ranked-list', 'ui.ranked-item']],
            'money' => ['Money and numbers', ['ui.money', 'ui.number', 'ui.time']],
            'timeline' => ['Timeline', ['ui.timeline', 'ui.timeline.day', 'ui.timeline.item']],
            'tree' => ['Tree', ['ui.tree', 'ui.tree.node']],
            'sortable' => ['Sortable', ['ui.sortable', 'ui.sortable.item']],
        ],
        'Community' => [
            'chat' => ['Chat', ['ui.chat', 'ui.chat.message', 'ui.chat.day', 'ui.chat.typing', 'ui.chat.composer']],
            'chat-inbox' => ['Inbox', ['ui.chat.inbox']],
            'post' => ['Post', ['ui.post']],
            'comment-thread' => ['Comment thread', ['ui.comment-thread', 'ui.comment-thread.comment']],
            'reactions' => ['Reactions', ['ui.reactions']],
            'member-card' => ['Member card and profile header', ['ui.member-card', 'ui.profile-header', 'ui.follow-button']],
            'poll' => ['Poll', ['ui.poll']],
            'channel-list' => ['Channel list', ['ui.channel-list']],
            'leaderboard' => ['Leaderboard', ['ui.leaderboard']],
        ],
        'Content' => [
            'typography' => ['Typography', ['ui.heading', 'ui.text', 'ui.section-label', 'ui.list', 'ui.blockquote', 'ui.prose']],
            'code' => ['Code', ['ui.code', 'ui.code-block']],
            'copy' => ['Copy to clipboard', ['ui.copy-button', 'ui.copy-field', 'ui.copy-text', 'ui.copy-icons']],
            'disclosure' => ['Disclosure and accordion', ['ui.disclosure', 'ui.accordion', 'ui.accordion.item']],
            'media' => ['Media', ['ui.figure', 'ui.audio', 'ui.video', 'ui.embed']],
        ],
    ];

    /** Guides, in navigation order: slug => title. */
    public const GUIDES = [
        'index' => 'Getting started',
        'tokens' => 'Tokens',
        'rules' => 'Rules',
        'audit' => 'Audit rules',
        'changelog' => 'Changelog',
    ];

    public const PATTERNS = [
        'forms' => 'Forms',
        'tables' => 'Tables',
        'dialogs' => 'Dialogs',
        'onboarding' => 'Onboarding checklist',
        'dashboards' => 'Dashboards',
    ];

    public static function root(): string
    {
        return dirname(__DIR__, 3).'/resources/views/components';
    }

    /** @return array<string, array{title: string, family: string, components: list<string>}> */
    public static function components(): array
    {
        $pages = [];
        foreach (self::FAMILIES as $family => $members) {
            foreach ($members as $slug => [$title, $components]) {
                $pages[$slug] = ['title' => $title, 'family' => $family, 'components' => $components];
            }
        }

        return $pages;
    }

    /** @return array{title: string, family: string, components: list<string>}|null */
    public static function component(string $slug): ?array
    {
        return self::components()[$slug] ?? null;
    }

    /** Every component file as name => relative path. @return array<string, string> */
    public static function files(): array
    {
        $files = [];
        foreach (Finder::create()->files()->in(self::root())->name('*.blade.php')->sortByName() as $file) {
            $relative = str_replace('\\', '/', $file->getRelativePathname());
            $files[ComponentSource::nameFor($relative)] = $relative;
        }

        return $files;
    }

    public static function source(string $name): ComponentSource
    {
        return ComponentSource::read(self::root(), self::files()[$name]);
    }

    /** The Blade tag for a component name: `x-ui.button`, `x-data-table`. */
    public static function tag(string $name): string
    {
        return 'x-'.$name;
    }

    /** Examples for a page: list of [title, view name, source]. */
    public static function examples(string $slug): array
    {
        $dir = dirname(__DIR__, 2).'/resources/views/docs/examples/'.$slug;
        if (! is_dir($dir)) {
            return [];
        }

        $examples = [];
        foreach (Finder::create()->files()->in($dir)->name('*.blade.php')->sortByName() as $file) {
            $base = Str::before($file->getFilename(), '.blade.php');
            $source = (string) file_get_contents($file->getPathname());
            $title = Str::of($base)->after('-')->replace('-', ' ')->ucfirst()->toString();
            if (preg_match('/^\{\{--\s*(.+?)\s*--\}\}\s*\n/s', $source, $m)) {
                $title = trim($m[1]);
                $source = substr($source, strlen($m[0]));
            }
            $examples[] = ['title' => $title, 'view' => 'docs.examples.'.$slug.'.'.$base, 'source' => rtrim($source)];
        }

        return $examples;
    }

    /** Hand-written notes for a page (keyboard, a11y, do and don't), see resources/docs. */
    public static function notes(string $slug): array
    {
        $file = dirname(__DIR__, 2).'/resources/docs/'.$slug.'.php';

        return array_merge(['intro' => null, 'keyboard' => [], 'a11y' => [], 'do' => [], 'dont' => [], 'events' => []], is_file($file) ? require $file : []);
    }
}
