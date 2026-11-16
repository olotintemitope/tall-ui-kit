<?php

return [
    'intro' => 'Put once in the layout `<head>`, before the stylesheet. It applies the stored colour scheme, privacy mode and, with `area`, the sidebar state before first paint, so nothing flashes.',
    'a11y' => [
        'Prints nothing visible. A wrong order (the stylesheet first) shows a flash of the light scheme, which is a problem for people sensitive to sudden brightness.',
        'The scheme chosen here is the one `x-ui.theme-toggle` shows as checked on first paint.',
    ],
    'do' => [
        'Put it in every layout, including auth and error pages, so the scheme holds across the app.',
        'Pass the shell\'s `area` in shells with a collapsible sidebar.',
    ],
    'dont' => [
        'Place it after the stylesheet or at the end of `<body>`.',
        'Set `html[data-color-scheme]` yourself: use `window.uiColorScheme.set()` or the toggle.',
    ],
];
