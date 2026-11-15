<?php

use Workbench\App\DataTable\DemoTables;

/*
| The workbench gallery (composer serve) renders every page in both themes, with no
| native <select> and the theme's stylesheet linked.
*/

beforeEach(fn () => DemoTables::reset());

it('renders each gallery page in both themes', function (string $uri, string $heading) {
    foreach (['neutral', 'newbanger'] as $theme) {
        $this->get($uri.'?theme='.$theme)
            ->assertOk()
            ->assertSee($heading)
            ->assertSee('kit-assets/'.$theme.'.css', false)
            ->assertSee('dataset.uiStoragePrefix', false)
            ->assertDontSee('<select', false);
    }
})->with([
    ['/', 'Basics'],
    ['/forms', 'Forms'],
    ['/overlays', 'Overlays'],
    ['/data', 'Data display'],
    ['/charts', 'Charts'],
    ['/tables', 'Tables'],
    ['/checklist', 'Checklist'],
    ['/sidebar', 'Sidebar'],
]);

it('serves only built gallery assets', function () {
    $this->get('/kit-assets/..%2Fcomposer.json')->assertNotFound();
});
