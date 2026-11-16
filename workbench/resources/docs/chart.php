<?php

return [
    'intro' => 'Server-rendered SVG charts: line, bar, stacked-bar, bar-horizontal and sparkline, up to 3 series. Pass static `labels` and `series` arrays; a small Alpine component only adds the hover crosshair, tooltip and keyboard read-out.',
    'keyboard' => [
        ['Tab', 'Focuses the plot of a line, bar or stacked-bar chart.'],
        ['Right / Left', 'Moves the active point and announces its label and values.'],
        ['Home / End', 'Jumps to the first or last point.'],
        ['Escape', 'Hides the read-out.'],
        ['Enter / Space', 'On the chart/table toggle, switches the view.'],
    ],
    'a11y' => [
        'The accessible name is a summary: title, period, total and change ("Revenue, last 7 days, ₦1,841,000 total, up 14% vs prior period").',
        'Every chart carries a data table: visually hidden, or shown in place of the plot with `toggle`.',
        'Point read-outs go to a polite live region.',
        'Give each sparkline a `label` that says the trend ("Zenith Foods, rising").',
        'Money charts mask axis values, tooltips, table cells and the summary under privacy mode.',
    ],
    'do' => [
        'Use chart tokens (`chart-1`, `chart-2`, `chart-3`, `chart-accent`) for series colours.',
        'Use `bar-horizontal` or `x-ui.ranked-list` with `share` for ranked categories.',
        'Use `x-ui.segment-bar` for one whole split into parts.',
    ],
    'dont' => [
        'Draw donut, pie or gauge charts, or fill under a line with a gradient.',
        'Add a fourth series: it throws in local and testing.',
        'Animate charts yourself: they draw in once (`motion="none"` opts out).',
    ],
];
