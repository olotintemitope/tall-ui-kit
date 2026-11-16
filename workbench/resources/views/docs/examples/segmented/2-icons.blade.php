{{-- Icons with labels, icon only, small and disabled --}}
<div class="flex flex-wrap items-center gap-3">
    <x-ui.segmented label="View" value="table" :options="[
        'table' => ['label' => 'Table', 'icon' => 'table'],
        'chart' => ['label' => 'Chart', 'icon' => 'chart-line'],
    ]" />
    <x-ui.segmented label="Layout" value="list" size="sm" :options="[
        'list' => ['label' => 'List', 'icon' => 'list', 'iconOnly' => true],
        'grid' => ['label' => 'Grid', 'icon' => 'layout-grid', 'iconOnly' => true],
    ]" />
    <x-ui.segmented label="Currency" value="ngn" size="sm" :options="[
        'ngn' => 'NGN',
        'usd' => 'USD',
        'gbp' => ['label' => 'GBP', 'disabled' => true],
    ]" />
</div>
