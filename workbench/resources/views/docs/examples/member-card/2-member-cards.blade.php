{{-- Member cards --}}
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <x-ui.member-card key="docs-bola" name="Bola Ade" role="Producer" message-href="#message" :stats="[['label' => 'Followers', 'value' => 2310], ['label' => 'Posts', 'value' => 88]]" />
    <x-ui.member-card key="docs-chidi" name="Chidi Eze" role="Finance" following :stats="[['label' => 'Followers', 'value' => 940], ['label' => 'Posts', 'value' => 12]]" />
</div>
