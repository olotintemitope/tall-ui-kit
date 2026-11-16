<?php

return [
    'a11y' => [
        'A `<nav aria-label="Breadcrumb">` around an ordered list; name it differently with `label` when a page has two.',
        'The last item is the current page: text with `aria-current="page"`, not a link.',
        'The "/" separators are hidden from assistive tech.',
    ],
    'do' => [
        'End the trail with the page\'s own title or reference.',
        'Let long names wrap; below `sm` only the parent and the current page show.',
    ],
    'dont' => [
        'Repeat the breadcrumb in the page when the shell top bar already draws it from `breadcrumbs`.',
        'Use it as the only way back on a phone: it collapses to the parent.',
    ],
];
