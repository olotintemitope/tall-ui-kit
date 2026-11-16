<?php

return [

    /*
    | Prefix for every key the kit keeps in localStorage or sessionStorage (sidebar
    | collapse, focus mode, privacy mode, wizard drafts). x-ui.head prints it on <html>
    | for the scripts. An app moving onto the kit can keep its old keys this way.
    */
    'storage_prefix' => env('TALL_UI_STORAGE_PREFIX', 'tallui_'),

    /*
    | Brand slots for x-ui.sidebar.header and x-ui.logo. With no logo the name prints
    | as text. The header's `logo` slot overrides both. logo_light sits on light
    | grounds; logo_dark on dark ones: the dark sidebar, and everything in the dark
    | colour scheme (x-ui.logo and light sidebars swap to it there).
    */
    'brand' => [
        'name' => env('APP_NAME', 'App'),
        'logo_light' => null,
        'logo_dark' => null,
    ],

    /*
    | Colour scheme (docs/theming.md "Dark mode"). x-ui.head prints
    | html[data-color-scheme] before first paint: the stored choice, else `default`.
    | "system" follows the operating system (prefers-color-scheme).
    |   default  light | dark | system
    |   schemes  the choices x-ui.theme-toggle offers; ['light'] keeps the app light
    |            (stored choices are ignored and the toggle renders nothing)
    |   persist  local: localStorage only. server: also the bound ColorSchemeStore,
    |            through WithColorScheme on the Livewire component around the toggle;
    |            x-ui.head then prints the stored value, so it follows the user
    |            across devices (localStorage stays the fallback).
    */
    'color_scheme' => [
        'default' => env('TALL_UI_COLOR_SCHEME', 'system'),
        'schemes' => ['light', 'dark', 'system'],
        'persist' => 'local',
    ],

    /*
    | Render plain paginators and Livewire's WithPagination through x-ui.pagination.
    */
    'pagination' => true,

    /*
    | x-ui.progress-bar (in the layout) replaces Livewire's wire:navigate progress bar,
    | so the kit turns Livewire's off. False leaves livewire.navigate alone.
    */
    'progress_bar' => true,

    'sidebar' => [
        // Hrefs that are active only on an exact match (area roots such as '/admin').
        'exact' => [],
    ],

    'data_table' => [
        /*
        | How WithDataTable checks abilities (tableAbility(), Column/Action ability).
        | null: Gate::allows($ability). A string: Gate::allows($gate, $ability), for an
        | app with one capability gate (NewBanger uses 'capability').
        */
        'gate' => null,
    ],

    'exports' => [
        // Time zone for exported dates and export ranges (null: app.timezone).
        'timezone' => null,
        // x-ui.export-columns: switch PDFs to landscape, and suggest CSV, above these column counts.
        'pdf_landscape_after' => 8,
        'pdf_suggest_csv_after' => 12,
    ],

    /*
    | x-ui.file `chunked` (docs/components/forms.md, "Chunked uploads"): the endpoint the
    | app registers with TallUiKit\Uploads\ChunkedUploads::routes() keeps each upload in
    | its own folder on a LOCAL disk until ChunkedUploads::find($id)->store() moves it.
    |   chunk_size    bytes per request (raw PATCH bodies; keep it under the web server's
    |                 body limit)
    |   max_size      the largest file accepted
    |   expire_after  hours before tall-ui-kit:prune-uploads deletes an upload
    */
    'chunked_uploads' => [
        'disk' => env('TALL_UI_CHUNK_DISK', 'local'),
        'directory' => 'tall-ui-kit-chunks',
        'chunk_size' => 2 * 1024 * 1024,
        'max_size' => 2 * 1024 * 1024 * 1024,
        'expire_after' => 24,
    ],

];
