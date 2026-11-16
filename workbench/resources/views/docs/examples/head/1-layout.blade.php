{{-- In the layout, before the stylesheet --}}
<x-ui.code-block label="resources/views/components/layouts/app.blade.php" copy>{{ implode("\n", [
    '<!DOCTYPE html>',
    '<html lang="en">',
    '<head>',
    '    <meta charset="utf-8">',
    '    <meta name="viewport" content="width=device-width, initial-scale=1">',
    '    <title>Payouts</title>',
    '    <'.'x-ui.head />',
    "    @"."vite(['resources/css/app.css', 'resources/js/app.js'])",
    '</head>',
]) }}</x-ui.code-block>
