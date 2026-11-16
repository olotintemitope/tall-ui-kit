{{-- A shell with a collapsible sidebar --}}
<x-ui.code-block label="resources/views/components/layouts/admin.blade.php" copy>{{ implode("\n", [
    '<head>',
    '    <meta charset="utf-8">',
    '    <meta name="viewport" content="width=device-width, initial-scale=1">',
    '    <'.'x-ui.head area="admin" />',
    "    @"."vite(['resources/css/app.css', 'resources/js/app.js'])",
    '</head>',
]) }}</x-ui.code-block>
