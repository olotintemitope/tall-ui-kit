<?php

use Workbench\App\Docs\BuildDocsCommand;
use Workbench\App\Docs\Catalog;
use Workbench\App\Docs\ComponentSource;
use Workbench\App\Docs\Markdown;
use Workbench\App\Docs\SearchIndex;
use Workbench\App\Docs\Site;

/*
| The docs site (composer docs:build) is generated from the kit: every component file
| belongs to exactly one page, every page renders, props come from @props.
*/

afterEach(function () {
    Site::$static = false;
    Site::$current = 'index';
});

it('documents every component file on exactly one page', function () {
    $documented = collect(Catalog::components())->flatMap(fn ($page) => $page['components']);

    expect($documented->duplicates()->all())->toBe([])
        ->and(array_values(array_diff(array_keys(Catalog::files()), $documented->all())))->toBe([])
        ->and(array_values(array_diff($documented->all(), array_keys(Catalog::files()))))->toBe([]);
});

it('gives every component page at least one live example', function () {
    $missing = collect(Catalog::components())->keys()->filter(fn ($slug) => Catalog::examples($slug) === [])->values()->all();

    expect($missing)->toBe([]);
});

it('renders every page of the site', function () {
    foreach (BuildDocsCommand::paths() as $path) {
        $uri = '/docs'.($path === 'index' ? '' : '/'.preg_replace('#/index$#', '', $path));
        $this->get($uri)->assertOk()->assertDontSee('<select', false)->assertDontSee('type="date"', false);
    }
});

it('reads props, defaults, types and descriptions from the component source', function () {
    $button = ComponentSource::read(Catalog::root(), 'ui/button.blade.php');
    $variant = collect($button->props)->firstWhere('name', 'variant');

    expect($button->name)->toBe('ui.button')
        ->and($variant['default'])->toBe("'default'")
        ->and($variant['type'])->toBe('string')
        ->and($variant['description'])->toContain('outline')
        ->and($button->slots)->toContain('default');
});

it('indexes every page for search', function () {
    $urls = array_column(SearchIndex::entries(), 'u');

    expect($urls)->toContain('components/button', 'tokens', 'patterns/forms')
        ->and(count($urls))->toBe(count(BuildDocsCommand::paths()) - 1);
});

it('writes relative links in the static site', function () {
    Site::$static = true;
    Site::$current = 'components/button';

    expect(Site::url('tokens'))->toBe('../tokens.html')
        ->and(Site::url('components/select', 'api'))->toBe('../components/select.html#api')
        ->and(Site::asset('neutral.css'))->toBe('../assets/neutral.css');
});

it('turns doc links into site links and makes tables and code scrollable by keyboard', function () {
    $html = Markdown::render("See [rules](rules.md).\n\n| a |\n|---|\n| b |\n\n```\ncode\n```");

    expect($html)->toContain('href="'.url('/docs/rules').'"')
        ->toContain('role="region"')->toContain('tabindex="0"')
        ->toContain('<pre tabindex="0">');
});
