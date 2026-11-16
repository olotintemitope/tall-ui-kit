{{-- Loading cells keep the strip's shape --}}
<x-ui.stat-strip :columns="3">
    <x-ui.stat label="Settled today" :value="3820000" money sub="Since midnight" />
    <x-ui.skeleton.stat />
    <x-ui.skeleton.stat />
</x-ui.stat-strip>
