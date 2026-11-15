<?php

use TallUiKit\Tests\TestCase;

pest()->extend(TestCase::class)->in('Feature', 'Unit');

/** Absolute path inside the package (resources/js/ui.js, resources/views, ...). */
function kit_path(string $path = ''): string
{
    return dirname(__DIR__).($path === '' ? '' : '/'.ltrim($path, '/'));
}
