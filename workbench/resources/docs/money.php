<?php

return [
    'intro' => '`x-ui.money` prints naira or dollars and blurs under privacy mode; `x-ui.number` formats plain, compact and percent values; `x-ui.time` prints dates in a machine-readable `<time>` element, absolute or relative, in Africa/Lagos by default.',
    'a11y' => [
        'Under privacy mode a masked amount is named "hidden value" and its title says how to reveal it; the `lean` form blurs without the name.',
        'Compact numbers keep the full value in `title`.',
        '`x-ui.time` always sets `datetime` to ISO 8601; relative times keep the absolute date and time in `title`.',
        'A null time prints an en dash; a null amount prints a muted dash.',
    ],
    'do' => [
        'Use `lean` money in table cells and dense rows (one span, no Alpine).',
        'Pass `now` in tests and docs so relative times are stable.',
        'Use `TallUiKit\\Support\\Format` in PHP for the same formatting outside Blade.',
    ],
    'dont' => [
        'Format money by hand with `number_format`: privacy mode will not mask it.',
        'Print raw ISO dates to people.',
    ],
];
