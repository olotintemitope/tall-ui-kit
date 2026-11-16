<?php

namespace Workbench\App\Docs;

use Illuminate\Http\Response;

/**
 * Serves the docs site from the workbench (/docs/...). `composer docs:build` renders
 * the same routes to static HTML (BuildDocsCommand), so the site is always the kit.
 */
final class DocsController
{
    public function guide(string $page = 'index')
    {
        abort_unless(isset(Catalog::GUIDES[$page]), 404);
        Site::$current = $page;

        return view('docs.guides.'.$page, ['title' => Catalog::GUIDES[$page]]);
    }

    public function components()
    {
        Site::$current = 'components/index';

        return view('docs.components', ['title' => 'Components']);
    }

    public function component(string $slug)
    {
        $page = Catalog::component($slug);
        abort_if($page === null, 404);
        Site::$current = 'components/'.$slug;

        return view('docs.component', [
            'title' => $page['title'],
            'slug' => $slug,
            'page' => $page,
            'sources' => array_map(fn ($name) => Catalog::source($name), $page['components']),
            'examples' => Catalog::examples($slug),
            'notes' => Catalog::notes($slug),
        ]);
    }

    public function pattern(string $slug)
    {
        abort_unless(isset(Catalog::PATTERNS[$slug]), 404);
        Site::$current = 'patterns/'.$slug;

        return view('docs.patterns.'.$slug, ['title' => Catalog::PATTERNS[$slug], 'examples' => Catalog::examples('patterns-'.$slug)]);
    }

    public function search(): Response
    {
        return response(SearchIndex::script(), 200, ['Content-Type' => 'text/javascript']);
    }
}
