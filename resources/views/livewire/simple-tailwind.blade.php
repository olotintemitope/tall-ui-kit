{{-- Livewire's default simple pagination view (WithPagination), replaced by the x-ui kit. --}}
<div>
    <x-ui.pagination :paginator="$paginator" livewire :scroll-to="$scrollTo ?? 'body'" />
</div>
