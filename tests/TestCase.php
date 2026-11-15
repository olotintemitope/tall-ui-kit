<?php

namespace TallUiKit\Tests;

use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use WithWorkbench;

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('k', 32)));
        $app['config']->set('app.env', 'testing');
        // The kit's sample data and fixtures are written in Lagos time (UTC+1).
        $app['config']->set('app.timezone', 'Africa/Lagos');
        date_default_timezone_set('Africa/Lagos');
        // Recommended for apps: only x-ui.icon draws Lucide icons (docs/components/icons.md).
        $app['config']->set('blade-icons.components.disabled', true);
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
    }
}
