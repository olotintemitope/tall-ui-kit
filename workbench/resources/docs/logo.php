<?php

return [
    'a11y' => [
        'Each image has the brand name as its alt text; with no images the name prints as text in the heading role.',
        'Both images are printed and CSS shows one, so a scheme change needs no reload and screen readers hear the name once.',
        'With `href` the logo is a link with the kit focus ring; its name is the brand name, so do not add "Home" around it.',
    ],
    'do' => [
        'Set both `brand.logo_light` and `brand.logo_dark` when the mark has dark ink.',
        'Link it to the app\'s home page in headers and auth pages.',
    ],
    'dont' => [
        'Use it inside `x-ui.sidebar.header`: the sidebar prints its own logo.',
        'Put the logo in emails or PDFs through this component: they stay light, use the light image directly.',
    ],
];
