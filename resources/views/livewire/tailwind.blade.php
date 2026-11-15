{{-- Livewire's default pagination view (WithPagination), replaced by the x-ui kit. --}}
<div>
    <x-ui.pagination :paginator="$paginator" :elements="$elements" livewire :scroll-to="$scrollTo ?? 'body'" />
</div>
