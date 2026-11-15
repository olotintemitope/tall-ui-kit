<?php

return [

    /*
    | Prefix for every key the kit keeps in localStorage or sessionStorage (sidebar
    | collapse, focus mode, privacy mode, wizard drafts). x-ui.head prints it on <html>
    | for the scripts. An app moving onto the kit can keep its old keys this way.
    */
    'storage_prefix' => env('TALL_UI_STORAGE_PREFIX', 'tallui_'),

    /*
    | Brand slots for x-ui.sidebar.header. With no logo the name prints as text. The
    | header's `logo` slot overrides both. logo_light sits on light sidebars,
    | logo_dark on the dark one.
    */
    'brand' => [
        'name' => env('APP_NAME', 'App'),
        'logo_light' => null,
        'logo_dark' => null,
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

];
