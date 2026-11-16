<?php

namespace TallUiKit\Support;

use InvalidArgumentException;

/**
 * Every foreground and background pair the UI kit uses, with its WCAG contrast ratio
 * computed from the token values of a theme file (resources/css/themes/<theme>.css),
 * in either colour scheme: `light` (the file's @theme blocks) or `dark` (the light
 * tokens overridden by the file's `@variant dark` block). The gallery's contrast
 * table, tests/Feature/UiKitContrastTest.php and scripts/contrast.php all read this
 * list, so the ratios cannot drift from the tokens. The documented ratio (sixth
 * column) is the one measured for the NewBanger theme in light; every theme and
 * scheme must meet the required ratio (fifth column).
 *
 * Colours are theme tokens, `white` or `black`, or `name/NN` for a colour at NN%
 * opacity over the pair's background. `surface` is the page (white in light),
 * `card` a panel and `popover` a menu, dialog, sheet or toast.
 */
class KitContrast
{
    /** Themes shipped with the kit. */
    public const THEMES = ['neutral', 'newbanger'];

    /** Colour schemes every shipped theme defines. */
    public const MODES = ['light', 'dark'];

    private static string $theme = 'neutral';

    private static string $mode = 'light';

    /** @var array<string, array<string, string>> */
    private static array $tokens = [];

    /** Measure against another theme (a shipped name or an absolute path) in a scheme. */
    public static function useTheme(string $theme, string $mode = 'light'): void
    {
        if (! in_array($mode, self::MODES, true)) {
            throw new InvalidArgumentException("Unknown colour scheme [{$mode}].");
        }

        self::$theme = $theme;
        self::$mode = $mode;
    }

    public static function theme(): string
    {
        return self::$theme;
    }

    public static function mode(): string
    {
        return self::$mode;
    }

    public static function themePath(string $theme): string
    {
        return str_contains($theme, '/') ? $theme : dirname(__DIR__, 2).'/resources/css/themes/'.$theme.'.css';
    }

    /** Fixed colours a pair may name besides theme tokens. */
    private const PALETTE = [
        'white' => '#ffffff',
        'black' => '#000000',
    ];

    /**
     * [use, foreground, background, kind (text | ui), required ratio, documented ratio].
     *
     * @return list<array{0: string, 1: string, 2: string, 3: string, 4: float, 5: float}>
     */
    public static function pairs(): array
    {
        return [
            // Text roles
            ['Titles, strong text', 'ink', 'surface', 'text', 4.5, 15.81],
            ['Body text', 'ink-body', 'surface', 'text', 4.5, 11.64],
            ['Meta, captions, labels, placeholders', 'ink-muted', 'surface', 'text', 4.5, 5.85],
            ['Meta on a tinted page', 'ink-muted', 'page-muted', 'text', 4.5, 5.47],
            ['Table header, panel footer text', 'ink-muted', 'panel-footer', 'text', 4.5, 5.61],
            ['Links and accent text', 'primary-text', 'surface', 'text', 4.5, 5.21],
            ['Rising delta, success text', 'success-text', 'surface', 'text', 4.5, 5.36],
            ['Falling delta, error text', 'danger-text', 'surface', 'text', 4.5, 6.42],
            ['Chart ticks and tooltip dates', 'chart-tick', 'surface', 'text', 4.5, 5.85],

            // Buttons, every state
            ['Primary button', 'on-primary', 'primary', 'text', 4.5, 5.32],
            ['Primary button, hover', 'on-primary', 'primary-fill-hover', 'text', 4.5, 5.97],
            ['Primary button, pressed', 'on-primary', 'primary-fill-active', 'text', 4.5, 4.62],
            ['Secondary button', 'on-secondary', 'secondary', 'text', 4.5, 15.81],
            ['Secondary button, hover', 'on-secondary', 'secondary-light', 'text', 4.5, 13.14],
            ['Secondary button, pressed', 'on-secondary', 'neutral-900', 'text', 4.5, 18.78],
            ['Destructive button', 'on-danger', 'danger', 'text', 4.5, 4.77],
            ['Destructive button, hover', 'on-danger', 'danger-hover', 'text', 4.5, 6.42],
            ['Destructive button, pressed', 'on-danger', 'danger-active', 'text', 4.5, 8.36],
            ['Outline and ghost, hover', 'ink', 'surface-subtle', 'text', 4.5, 14.63],
            ['Outline and ghost, pressed; toggle on', 'ink', 'surface-muted', 'text', 4.5, 13.63],
            ['Disabled control text', 'ink-muted', 'surface-subtle', 'text', 4.5, 5.42],
            ['Disabled link', 'ink-muted', 'surface', 'text', 4.5, 5.85],
            ['Selected tab, hovered tab', 'ink', 'surface', 'text', 4.5, 15.81],
            ['Segment, hover', 'ink', 'surface-subtle', 'text', 4.5, 14.63],
            ['Unselected and disabled segment', 'ink-muted', 'surface-subtle', 'text', 4.5, 5.42],

            // Notices and badges, on their own fills
            ['Danger alert and toast', 'danger-strong', 'danger-tint', 'text', 4.5, 7.64],
            ['Warning alert and toast', 'warning-strong', 'warning-tint', 'text', 4.5, 8.73],
            ['Success alert and toast', 'success-strong', 'success-tint', 'text', 4.5, 7.23],
            ['Neutral alert', 'ink', 'panel-footer', 'text', 4.5, 15.15],
            ['Info toast', 'info-strong', 'info-tint', 'text', 4.5, 8.11],
            ['Success badge', 'success-strong', 'success-tint', 'text', 4.5, 7.23],
            ['Warning badge', 'warning-text', 'warning-tint', 'text', 4.5, 6.84],
            ['Danger badge', 'danger-text', 'danger-tint', 'text', 4.5, 5.87],
            ['Info badge', 'info-text', 'info-tint', 'text', 4.5, 6.28],
            ['Neutral badge', 'ink', 'surface-muted', 'text', 4.5, 13.63],
            ['Accent badge', 'primary-text', 'primary/10', 'text', 4.5, 4.67],

            // Dark admin sidebar (#212322)
            ['Sidebar item', 'white/70', 'sidebar', 'text', 4.5, 8.40],
            ['Sidebar section label and count', 'white/65', 'sidebar', 'text', 4.5, 7.44],
            ['Sidebar icon', 'white/60', 'sidebar', 'ui', 3.0, 6.56],
            ['Sidebar active item', 'white', 'sidebar', 'text', 4.5, 15.81],
            ['Focus outline on the sidebar', 'focus', 'sidebar', 'ui', 3.0, 3.84],

            // Boundaries and marks (non-text, 3:1)
            ['Control border', 'control', 'surface', 'ui', 3.0, 3.65],
            ['Disabled control border (dashed)', 'control', 'surface-subtle', 'ui', 3.0, 3.38],
            ['Selected segment edge on its track', 'control', 'surface-subtle', 'ui', 3.0, 3.38],
            ['Control border, hover', 'control-hover', 'surface', 'ui', 3.0, 4.97],
            ['Primary button boundary', 'primary-hover', 'surface', 'ui', 3.0, 3.70],
            ['Destructive button boundary', 'danger', 'surface', 'ui', 3.0, 4.77],
            ['Focus outline', 'focus', 'surface', 'ui', 3.0, 4.12],
            ['Selected tab underline, checked control', 'secondary', 'surface', 'ui', 3.0, 15.81],
            ['Disabled checked control', 'ink-muted', 'surface', 'ui', 3.0, 5.85],
            ['Spinner arc', 'ink', 'surface', 'ui', 3.0, 15.81],
            ['Progress bar', 'chart-1', 'surface', 'ui', 3.0, 15.81],
            ['Money series', 'chart-accent', 'surface', 'ui', 3.0, 5.21],
            ['Series 1', 'chart-1', 'surface', 'ui', 3.0, 15.81],
            ['Series 2, sparklines', 'chart-2', 'surface', 'ui', 3.0, 5.85],
            ['Series 3 (dashed on lines)', 'chart-3', 'surface', 'ui', 3.0, 3.16],
            ['Horizontal bar on its track', 'chart-2', 'surface-muted', 'ui', 3.0, 5.05],

            // Checklist (x-ui.checklist): the next step sits on a surface-subtle tint
            ['Checklist done step label', 'ink-muted', 'surface', 'text', 4.5, 5.85],
            ['Checklist next step label', 'ink', 'surface-subtle', 'text', 4.5, 14.63],
            ['Checklist next step hint', 'ink-muted', 'surface-subtle', 'text', 4.5, 5.42],
            ['Checklist next step action', 'primary-text', 'surface-subtle', 'text', 4.5, 4.82],
            ['Checklist done check', 'ink', 'surface', 'ui', 3.0, 15.81],
            ['Checklist step not done (circle)', 'control', 'surface', 'ui', 3.0, 3.65],
            ['Checklist next step not done (circle on the tint)', 'control', 'surface-subtle', 'ui', 3.0, 3.38],
            ['Checklist progress fill on its track', 'chart-1', 'surface-muted', 'ui', 3.0, 13.63],
            ['Checklist focus outline on the next-step tint', 'focus', 'surface-subtle', 'ui', 3.0, 3.81],

            // Workflow: stepper, timeline, tag input, avatar group, sortable, slider, tree, banner
            ['Stepper current marker number', 'on-secondary', 'secondary', 'text', 4.5, 15.81],
            ['Stepper complete marker ring and check', 'secondary', 'surface', 'ui', 3.0, 15.81],
            ['Stepper upcoming marker ring', 'control', 'surface', 'ui', 3.0, 3.65],
            ['Stepper upcoming label and number', 'ink-muted', 'surface', 'text', 4.5, 5.85],
            ['Stepper error label, ring and description', 'danger-text', 'surface', 'text', 4.5, 6.42],
            ['Timeline marker ring (no actor)', 'control', 'surface', 'ui', 3.0, 3.65],
            ['Tag chip text', 'ink', 'surface-muted', 'text', 4.5, 13.63],
            ['Tag chip remove icon', 'ink-muted', 'surface-muted', 'ui', 3.0, 5.05],
            ['Tag chip remove icon, hover', 'ink', 'line-strong', 'ui', 3.0, 10.53],
            ['Focus outline on a tag chip', 'focus', 'surface-muted', 'ui', 3.0, 3.55],
            ['Avatar group "+N"', 'ink', 'surface-muted', 'text', 4.5, 13.63],
            ['Avatar group tooltip', 'on-secondary', 'secondary', 'text', 4.5, 15.81],
            ['Sortable grip', 'ink-muted', 'surface', 'ui', 3.0, 5.85],
            ['Sortable lifted row label', 'ink', 'surface-subtle', 'text', 4.5, 14.63],
            ['Sortable grip on the lifted row', 'ink-muted', 'surface-subtle', 'ui', 3.0, 5.42],
            ['Focus outline on the lifted row', 'focus', 'surface-subtle', 'ui', 3.0, 3.81],
            ['Slider track', 'control', 'surface', 'ui', 3.0, 3.65],
            ['Slider chosen range', 'secondary', 'surface', 'ui', 3.0, 15.81],
            ['Slider thumb edge', 'control', 'surface', 'ui', 3.0, 3.65],
            ['Tree selected row', 'ink', 'surface-muted', 'text', 4.5, 13.63],
            ['Tree hovered row', 'ink', 'surface-subtle', 'text', 4.5, 14.63],
            ['Tree check box', 'control', 'surface', 'ui', 3.0, 3.65],
            ['Tree check box on a hovered row', 'control', 'surface-subtle', 'ui', 3.0, 3.38],
            ['Tree checked box', 'secondary', 'surface', 'ui', 3.0, 15.81],
            ['Tree chevron', 'ink-muted', 'surface', 'ui', 3.0, 5.85],
            ['Tree focus outline on the selected row', 'focus', 'surface-muted', 'ui', 3.0, 3.55],
            ['Banner info', 'info-text', 'info-tint', 'text', 4.5, 6.28],
            ['Banner success', 'success-strong', 'success-tint', 'text', 4.5, 7.23],
            ['Banner warning', 'warning-strong', 'warning-tint', 'text', 4.5, 8.73],
            ['Banner danger', 'danger-strong', 'danger-tint', 'text', 4.5, 7.64],
            ['Focus outline on the info banner', 'focus', 'info-tint', 'ui', 3.0, 3.78],
            ['Focus outline on the success banner', 'focus', 'success-tint', 'ui', 3.0, 3.91],
            ['Focus outline on the warning banner', 'focus', 'warning-tint', 'ui', 3.0, 3.97],
            ['Focus outline on the danger banner', 'focus', 'danger-tint', 'ui', 3.0, 3.76],

            // Progress: x-ui.progress tones and buffer, x-ui.progress-ring, x-ui.progress-steps, x-ui.upload-progress
            ['Progress fill on its track', 'chart-1', 'surface-muted', 'ui', 3.0, 13.63],
            ['Progress success fill on its track', 'success-text', 'surface-muted', 'ui', 3.0, 4.62],
            ['Progress success fill on white', 'success-text', 'surface', 'ui', 3.0, 5.36],
            ['Progress warning fill on its track', 'warning-text', 'surface-muted', 'ui', 3.0, 6.11],
            ['Progress warning fill on white', 'warning-text', 'surface', 'ui', 3.0, 7.09],
            ['Progress danger fill on its track', 'danger-text', 'surface-muted', 'ui', 3.0, 5.53],
            ['Progress danger fill on white', 'danger-text', 'surface', 'ui', 3.0, 6.42],
            ['Progress fill against its buffer', 'chart-1', 'line-strong', 'ui', 3.0, 10.53],
            ['Progress success fill against its buffer', 'success-text', 'line-strong', 'ui', 3.0, 3.57],
            ['Progress warning fill against its buffer', 'warning-text', 'line-strong', 'ui', 3.0, 4.72],
            ['Progress danger fill against its buffer', 'danger-text', 'line-strong', 'ui', 3.0, 4.28],
            ['Upload failed status and error', 'danger-text', 'surface', 'text', 4.5, 6.42],

            // Files: x-ui.file-card, x-ui.attachment-list, x-ui.file-manager, x-ui.file-preview, x-ui.file
            ['File type label', 'ink-muted', 'surface', 'text', 4.5, 5.85],
            ['File type label in a card or thumbnail box', 'ink-muted', 'surface-subtle', 'text', 4.5, 5.42],
            ['File type label on a selected item', 'ink-muted', 'surface-muted', 'text', 4.5, 5.05],
            ['File name and size on a hovered item', 'ink', 'surface-subtle', 'text', 4.5, 14.63],
            ['File manager selected item name', 'ink', 'surface-muted', 'text', 4.5, 13.63],
            ['File manager selected item edge', 'control-hover', 'surface-muted', 'ui', 3.0, 4.29],
            ['File manager check box', 'control', 'surface', 'ui', 3.0, 3.65],
            ['File manager checked box', 'secondary', 'surface', 'ui', 3.0, 15.81],
            ['File manager check mark', 'on-secondary', 'secondary', 'ui', 3.0, 15.81],
            ['File manager focus outline', 'focus', 'surface', 'ui', 3.0, 4.12],
            ['File manager focus outline on a selected item', 'focus', 'surface-muted', 'ui', 3.0, 3.55],
            ['File manager drop target edge', 'secondary', 'surface-muted', 'ui', 3.0, 13.63],
            ['File manager drop area edge', 'control', 'surface-subtle', 'ui', 3.0, 3.38],
            ['File manager drop hint', 'ink', 'surface-subtle', 'text', 4.5, 14.63],
            ['File manager rename field border', 'control', 'surface', 'ui', 3.0, 3.65],
            ['File manager rename error', 'danger-text', 'surface-muted', 'text', 4.5, 5.53],
            ['File field refused file reason', 'danger-text', 'surface', 'text', 4.5, 6.42],
            ['File preview zoom level and details', 'ink-muted', 'surface', 'text', 4.5, 5.85],

            // Chat and community: x-ui.chat, x-ui.chat.inbox, x-ui.post, x-ui.reactions, x-ui.poll,
            // x-ui.channel-list, x-ui.leaderboard, x-ui.follow-button
            ['Chat sent bubble text', 'ink', 'surface-subtle', 'text', 4.5, 14.63],
            ['Chat link, mention or hashtag in a sent bubble', 'primary-text', 'surface-subtle', 'text', 4.5, 4.82],
            ['Chat link, mention or hashtag in a received bubble or post', 'primary-text', 'surface', 'text', 4.5, 5.21],
            ['Chat time and states', 'ink-muted', 'surface', 'text', 4.5, 5.85],
            ['Chat "Not sent." and Retry', 'danger-text', 'surface', 'text', 4.5, 6.42],
            ['Chat new messages button', 'on-secondary', 'secondary', 'text', 4.5, 15.81],
            ['Chat typing dots', 'ink-muted', 'surface', 'ui', 3.0, 5.85],
            ['Composer border', 'control', 'surface', 'ui', 3.0, 3.65],
            ['Composer counter near the limit', 'danger-text', 'surface', 'text', 4.5, 6.42],
            ['Inbox open conversation name', 'ink', 'surface-muted', 'text', 4.5, 13.63],
            ['Inbox open conversation preview and time', 'ink-muted', 'surface-muted', 'text', 4.5, 5.05],
            ['Inbox hovered row preview', 'ink-muted', 'surface-subtle', 'text', 4.5, 5.42],
            ['Inbox unread preview', 'ink-body', 'surface', 'text', 4.5, 11.64],
            ['Like pressed (filled heart)', 'primary-text', 'surface', 'ui', 3.0, 5.21],
            ['Post action, hover', 'ink', 'surface-subtle', 'text', 4.5, 14.63],
            ['Reaction pressed edge', 'control-hover', 'surface-muted', 'ui', 3.0, 4.29],
            ['Reaction pressed count', 'ink', 'surface-muted', 'text', 4.5, 13.63],
            ['Reaction picker label', 'ink-body', 'popover', 'text', 4.5, 11.64],
            ['Reaction picker label, focused', 'ink-body', 'surface-subtle', 'text', 4.5, 10.77],
            ['Follow button', 'on-primary', 'primary', 'text', 4.5, 5.32],
            ['Follow button boundary', 'primary-hover', 'surface', 'ui', 3.0, 3.70],
            ['Following (outline) edge', 'control', 'surface', 'ui', 3.0, 3.65],
            ['Poll choice edge, chosen', 'control-hover', 'surface-muted', 'ui', 3.0, 4.29],
            ['Poll chosen radio or box', 'secondary', 'surface-muted', 'ui', 3.0, 13.63],
            ['Poll bar on its track', 'chart-1', 'surface-muted', 'ui', 3.0, 13.63],
            ['Channel unread dot', 'ink', 'surface', 'ui', 3.0, 15.81],
            ['Channel unread dot, hover', 'ink', 'surface-subtle', 'ui', 3.0, 14.63],
            ['Channel open row text', 'ink', 'surface-muted', 'text', 4.5, 13.63],
            ['Channel prefix on the open row', 'ink-muted', 'surface-muted', 'text', 4.5, 5.05],
            ['Leaderboard up', 'success-text', 'surface', 'text', 4.5, 5.36],
            ['Leaderboard down', 'danger-text', 'surface', 'text', 4.5, 6.42],
            ['Leaderboard up on your row', 'success-text', 'surface-muted', 'text', 4.5, 4.62],
            ['Leaderboard down on your row', 'danger-text', 'surface-muted', 'text', 4.5, 5.53],
            ['Leaderboard sub line on your row', 'ink-muted', 'surface-muted', 'text', 4.5, 5.05],
            ['Focus outline on a muted row', 'focus', 'surface-muted', 'ui', 3.0, 3.55],

            // Raised surfaces: panels (`card`) and popovers (menus, dialogs, sheets,
            // toasts). White in light; one and two steps above the page in dark.
            ['Text in a panel', 'ink', 'card', 'text', 4.5, 15.81],
            ['Meta in a panel', 'ink-muted', 'card', 'text', 4.5, 5.85],
            ['Links in a panel', 'primary-text', 'card', 'text', 4.5, 5.21],
            ['Field error in a panel', 'danger-text', 'card', 'text', 4.5, 6.42],
            ['Control border in a panel', 'control', 'card', 'ui', 3.0, 3.65],
            ['Focus outline in a panel', 'focus', 'card', 'ui', 3.0, 4.12],
            ['Checked control in a panel', 'secondary', 'card', 'ui', 3.0, 15.81],
            ['Primary button boundary in a panel', 'primary-hover', 'card', 'ui', 3.0, 3.70],
            ['Text in a popover, dialog or toast', 'ink', 'popover', 'text', 4.5, 15.81],
            ['Body text in a popover', 'ink-body', 'popover', 'text', 4.5, 11.64],
            ['Meta in a popover, dialog or toast', 'ink-muted', 'popover', 'text', 4.5, 5.85],
            ['Links and Today in a popover', 'primary-text', 'popover', 'text', 4.5, 5.21],
            ['Destructive menu item', 'danger-text', 'popover', 'text', 4.5, 6.42],
            ['Destructive menu item, hover', 'danger-text', 'danger-tint', 'text', 4.5, 5.87],
            ['Menu item, hover', 'ink', 'surface-subtle', 'text', 4.5, 14.63],
            ['Control border in a dialog', 'control', 'popover', 'ui', 3.0, 3.65],
            ['Focus outline in a popover', 'focus', 'popover', 'ui', 3.0, 4.12],
            ['Checked control in a dialog', 'secondary', 'popover', 'ui', 3.0, 15.81],
            ['Destructive button boundary in a dialog', 'danger', 'popover', 'ui', 3.0, 4.77],
            ['Table header and footer text', 'ink', 'panel-footer', 'text', 4.5, 15.15],
            ['Meta on the panel footer', 'ink-muted', 'panel-footer', 'text', 4.5, 5.61],
            ['Read-only field text', 'ink', 'panel-footer', 'text', 4.5, 15.15],
            ['Control border on a tinted page', 'control', 'page-muted', 'ui', 3.0, 3.41],
            ['Text on a tinted page', 'ink', 'page-muted', 'text', 4.5, 14.77],
            ['Tooltip', 'on-secondary', 'secondary', 'text', 4.5, 15.81],
            ['Focus outline on a hover fill', 'focus', 'surface-subtle', 'ui', 3.0, 3.81],
            ['Selected row, focus outline', 'focus', 'surface-muted', 'ui', 3.0, 3.55],

            // Charts in a card (x-ui.card.chart): marks on the panel surface
            ['Chart ticks in a card', 'chart-tick', 'card', 'text', 4.5, 5.85],
            ['Money series in a card', 'chart-accent', 'card', 'ui', 3.0, 5.21],
            ['Series 1 in a card', 'chart-1', 'card', 'ui', 3.0, 15.81],
            ['Series 2 in a card', 'chart-2', 'card', 'ui', 3.0, 5.85],
            ['Series 3 in a card', 'chart-3', 'card', 'ui', 3.0, 3.16],
            ['Chart tooltip text', 'ink', 'popover', 'text', 4.5, 15.81],

            // Status text on the surfaces it sits on
            ['Success text in a panel', 'success-text', 'card', 'text', 4.5, 5.36],
            ['Warning text on the page', 'warning-text', 'surface', 'text', 4.5, 7.09],
            ['Info text on the page', 'info-text', 'surface', 'text', 4.5, 6.83],

            // Date and time pickers (x-ui.date, x-ui.date-range, x-ui.time-input ...)
            ['Calendar day, weekday header, time option', 'ink', 'surface', 'text', 4.5, 15.81],
            ['Calendar day or option, hover and active', 'ink', 'surface-subtle', 'text', 4.5, 14.63],
            ['Calendar active option outline', 'control', 'surface-subtle', 'ui', 3.0, 3.38],
            ['Calendar chosen day, week, month, time and pressed preset', 'on-secondary', 'secondary', 'text', 4.5, 15.81],
            ['Calendar days inside a range', 'ink', 'surface-muted', 'text', 4.5, 13.63],
            ['Calendar disabled day inside a range', 'ink-muted', 'surface-muted', 'text', 4.5, 5.05],
            ['Calendar disabled day, week number, outside day', 'ink-muted', 'surface', 'text', 4.5, 5.85],
            ['Calendar Today and Done', 'primary-text', 'surface', 'text', 4.5, 5.21],
            ['Calendar Today and Done, hover', 'primary-text', 'surface-subtle', 'text', 4.5, 4.82],
            ['Calendar preset edge', 'control', 'surface', 'ui', 3.0, 3.65],
            ['Calendar focus outline on a range tint', 'focus', 'surface-muted', 'ui', 3.0, 3.55],
            ['Calendar focus outline beside a chosen day', 'focus', 'secondary', 'ui', 3.0, 3.84],
        ];
    }

    public static function ratio(string $foreground, string $background): float
    {
        $bg = self::rgb($background, self::rgb(self::$mode === 'dark' ? 'surface' : 'white', [255, 255, 255]));
        $fg = self::rgb($foreground, $bg);

        $l1 = self::luminance($fg);
        $l2 = self::luminance($bg);

        return round((max($l1, $l2) + 0.05) / (min($l1, $l2) + 0.05), 2);
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $under  what a translucent colour sits on
     * @return array{0: int, 1: int, 2: int}
     */
    public static function rgb(string $color, array $under): array
    {
        [$name, $alpha] = str_contains($color, '/') ? explode('/', $color, 2) : [$color, '100'];
        $hex = ltrim(self::hex($name), '#');
        $rgb = array_map(fn (string $pair): int => hexdec($pair), str_split($hex, 2));
        $a = (int) $alpha / 100;

        return array_map(fn (int $c, int $u): int => (int) round($c * $a + $u * (1 - $a)), $rgb, $under);
    }

    public static function hex(string $name): string
    {
        if (isset(self::PALETTE[$name])) {
            return self::PALETTE[$name];
        }

        $key = self::$theme.'|'.self::$mode;
        self::$tokens[$key] ??= self::$mode === 'dark' ? self::darkTokens(self::$theme) : self::lightTokens(self::$theme);

        return self::$tokens[$key][$name] ?? throw new InvalidArgumentException("Unknown colour token [{$name}].");
    }

    /**
     * The colour tokens a theme sets for light (everything before its `@variant dark` block).
     *
     * @return array<string, string>
     */
    public static function lightTokens(string $theme): array
    {
        return self::parse(self::split($theme)[0]);
    }

    /**
     * The colour tokens the theme's `@variant dark` block sets, on their own.
     *
     * @return array<string, string>
     */
    public static function darkOverrides(string $theme): array
    {
        return self::parse(self::split($theme)[1]);
    }

    /**
     * Dark: the light tokens with the dark block's values over them.
     *
     * @return array<string, string>
     */
    public static function darkTokens(string $theme): array
    {
        return array_merge(self::lightTokens($theme), self::darkOverrides($theme));
    }

    /**
     * Token names declared in each part (any value: hex, rgb()), for the "every light
     * token has a dark value" check.
     *
     * @return array{light: list<string>, dark: list<string>}
     */
    public static function declaredNames(string $theme): array
    {
        $names = fn (string $css): array => preg_match_all('/--color-([a-z0-9-]+)\s*:/', $css, $m) ? array_values(array_unique($m[1])) : [];
        [$light, $dark] = self::split($theme);

        return ['light' => $names($light), 'dark' => $names($dark)];
    }

    /**
     * @return array{0: string, 1: string} the light part and the dark block of a theme file
     */
    private static function split(string $theme): array
    {
        $path = self::themePath($theme);

        if (! is_file($path)) {
            throw new InvalidArgumentException("Unknown theme [{$theme}] ({$path}).");
        }

        $css = (string) file_get_contents($path);
        $at = strpos($css, '@variant dark {');

        return $at === false ? [$css, ''] : [substr($css, 0, $at), substr($css, $at)];
    }

    /**
     * @return array<string, string>
     */
    private static function parse(string $css): array
    {
        preg_match_all('/--color-([a-z0-9-]+):\s*(#[0-9A-Fa-f]{6})\s*;/', $css, $matches, PREG_SET_ORDER);

        return collect($matches)->mapWithKeys(fn (array $m): array => [$m[1] => strtolower($m[2])])->all();
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $rgb
     */
    private static function luminance(array $rgb): float
    {
        [$r, $g, $b] = array_map(function (int $c): float {
            $c /= 255;

            return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        }, $rgb);

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }
}
