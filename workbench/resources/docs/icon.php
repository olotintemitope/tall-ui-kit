<?php

return [
    'a11y' => [
        'Icons are `aria-hidden="true"`: the text or control beside them carries the name.',
        'An icon-only button needs an `aria-label` that says the action and, in lists, the record ("Edit invoice INV-2048").',
        'An icon that carries meaning on its own (a status) needs text beside it too; never rely on its colour.',
    ],
    'do' => [
        'Size with `size-4` (16px) in controls and text, `size-5` at most elsewhere.',
        'Use the Lucide React names (`chart-line`, not `line-chart`); `UiKitIconsTest` fails on a name that does not resolve.',
    ],
    'dont' => [
        'Put icons in coloured tiles or circles, or use them as section decoration.',
        'Use `<x-lucide-*>` components: they are switched off; use `x-ui.icon`.',
    ],
];
