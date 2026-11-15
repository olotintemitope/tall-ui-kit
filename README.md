# TALL UI kit

`olotintemitope/tall-ui-kit`: a flat, dense, accessible UI kit for Laravel 13, Livewire 4.4, Alpine and Tailwind 4. About 170 anonymous Blade components under the familiar `x-ui.*` and `x-data-table.*` names, a server-driven data table trait, server-rendered charts, Tailwind 4 tokens with two themes, and Alpine behaviour as ES modules. Extracted from the NewBanger app, where every component was built and QA'd.

Private package, proprietary licence.

- Look: Stripe, Linear, Vercel. 1px borders, hierarchy from type roles, no "AI look" ([rules](docs/rules.md)).
- Accessibility: WCAG 2.2 AA, every contrast pair measured per theme, focus never lost ([accessibility](docs/accessibility.md)).
- Performance: server-rendered SVG charts, lazy GSAP and `x-anchor`, deferred sections ([budgets](docs/performance.md)).

## Install

The repository is private, so add it as a VCS repository (your Composer needs a GitHub token with access):

```json
"repositories": [
    { "type": "vcs", "url": "https://github.com/olotintemitope/tall-ui-kit" }
]
```

```bash
composer require olotintemitope/tall-ui-kit:^0.1
npm install gsap
php artisan vendor:publish --tag=tall-ui-kit-config   # optional
```

Requires PHP 8.4, `laravel/framework` 13, `livewire/livewire` 4.4 and `mallardduck/blade-lucide-icons` 1.23 (pinned: 1.24 redraws icons, see [icons](docs/components/copy-and-icons.md)). The service provider is auto-discovered.

### CSS (Tailwind 4)

```css
/* resources/css/app.css */
@import 'tailwindcss';
@import '../../vendor/olotintemitope/tall-ui-kit/resources/css/tall-ui-kit.css';
@import '../../vendor/olotintemitope/tall-ui-kit/resources/css/themes/neutral.css';
```

Switch theme by changing the last line to `themes/newbanger.css` ([theming](docs/theming.md)).

### JavaScript

```js
// resources/js/app.js
import '../../vendor/olotintemitope/tall-ui-kit/resources/js/index.js';
```

Per-feature modules and the lazy behaviour: [javascript](docs/javascript.md).

### Layout

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-ui.head />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-white font-body text-ink">
    <x-ui.progress-bar />
    <main id="main" tabindex="-1">{{ $slot }}</main>
    <x-ui.toaster />
    @livewireScripts
</body>
</html>
```

Recommended in `config/blade-icons.php`: `'components' => ['disabled' => true]`, so the 1,650 `<x-lucide-*>` components are not registered (the kit draws icons through `x-ui.icon` only), and run `php artisan icons:cache` in production.

## A page with the kit

```blade
<x-ui.page-header title="Payouts" description="Requests waiting for review.">
    <x-slot:actions>
        <x-ui.button variant="outline" x-on:click="$dispatch('open-dialog', 'export')">Export</x-ui.button>
        <x-ui.button wire:click="approveAll" loading="approveAll">Approve all</x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

<x-ui.stat-strip :columns="3">
    <x-ui.stat label="Waiting" :value="$waiting" />
    <x-ui.stat label="Total" :value="$total" money />
    <x-ui.stat label="Oldest" :value="$oldest" />
</x-ui.stat-strip>

<x-data-table :table="$this->dataTable()" />
```

## What is in the package

| Area | Where |
|---|---|
| Blade components (`x-ui.*`, `x-data-table.*`) | `resources/views/components` |
| Livewire pagination views (WithPagination renders `x-ui.pagination`) | `resources/views/livewire` |
| Tokens and component CSS, themes | `resources/css/tall-ui-kit.css`, `resources/css/themes/{neutral,newbanger}.css` |
| Alpine behaviour | `resources/js/index.js` and modules |
| Data table: `WithDataTable` trait, `Column`, `Filter`, `Action`, `TableView`, `RowRenderer` | `src/Livewire/Concerns`, `src/Support/DataTable` |
| Charts: `ChartPlot`, `NiceScale`, `TickThinning` | `src/Support/Charts` |
| Support: `Format`, `Money` (NGN and USD), `FormControl`, `LucideIcons`, `Embed`, `KitContrast`, `SidebarContrast`, `SidebarTones`, `Exports\ExportColumn`, `Exports\ExportRange` | `src/Support` |

Not in the package (app code): navigation data, shells and command palette data, the export pipeline (`WithExports`, writer, Dompdf), the notification inbox, and any business components.

## Documentation

- [Rules](docs/rules.md): no AI look, borders, focus, contrast, below-only popups, no native `<select>`, tables, toasts above modals, one-line pills, short references and UUID URLs
- [Theming](docs/theming.md) and [tokens, type roles and states](docs/tokens.md)
- [Components index](docs/components/README.md): every component with its props and slots
  - [Forms and form layouts](docs/components/forms.md), [HTML5 and media elements](docs/components/html-elements.md)
  - [Navigation: tabs, sidebar, focus mode](docs/components/navigation.md)
  - [Overlays: dialogs, confirmations](docs/components/overlays.md)
  - [Loading, toasts, notifications](docs/components/feedback.md)
  - [Cards and charts](docs/components/data-display.md)
  - [Data table](docs/components/data-table.md), [exports](docs/components/exports.md)
  - [Icons and copy to clipboard](docs/components/copy-and-icons.md)
- [Accessibility and focus](docs/accessibility.md), [motion](docs/motion.md), [performance budgets](docs/performance.md), [JavaScript](docs/javascript.md)

## Configuration

`config/tall-ui-kit.php`: `storage_prefix`, `brand` (name and logos), `pagination` (route paginators through `x-ui.pagination`), `progress_bar` (turn Livewire's navigate bar off for `x-ui.progress-bar`), `sidebar.exact`, `data_table.gate`, `exports`. To change a component's markup, publish the views (`--tag=tall-ui-kit-views`); a published copy in `resources/views/vendor/tall-ui-kit/components` wins.

## Gallery (workbench)

```bash
composer install && npm install
npm run build                 # builds both themes and the kit JS into workbench/dist
composer serve                # http://127.0.0.1:8000 (?theme=neutral or ?theme=newbanger)
```

Pages: Basics, Forms, Overlays, Data display, Charts, Data table (Livewire, 320 sample rows on a private SQLite file), Sidebar.

## Checks

```bash
composer test                 # Pest + Orchestra Testbench: component renders, data table, contrast, guards
composer contrast             # every contrast pair for both themes; exits 1 on a failure
BASE=http://127.0.0.1:8000 npm run a11y   # axe-core on every gallery page, both themes, 1440 and 390
```

## AI skills

- Claude: `skills/claude/tall-ui-kit/` (an Agent Skill: `SKILL.md` plus references). Install by copying or symlinking it to `~/.claude/skills/tall-ui-kit/`.
- ChatGPT: `skills/chatgpt/` (see its README for upload steps).

## Licence

Proprietary. Copyright Temitope Olotin. All rights reserved; see [LICENSE](LICENSE).
