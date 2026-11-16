{{-- A summary row with the picker, and a custom set --}}
<div class="space-y-3">
    <x-ui.reactions label="Docs reactions" subject="docs-r1" :reactions="[['key' => 'like', 'count' => 1240, 'mine' => true], ['key' => 'love', 'count' => 18]]" />
    <x-ui.reactions label="Docs custom reactions" subject="docs-r2" size="sm" :options="['up' => ['▲', 'Agree'], 'down' => ['▼', 'Disagree']]" :reactions="[['key' => 'up', 'count' => 7]]" />
</div>
