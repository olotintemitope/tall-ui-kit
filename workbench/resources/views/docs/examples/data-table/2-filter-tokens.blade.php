{{-- Active filter tokens: x-ui.filter-token, as the table's toolbar shows them --}}
<div x-data class="flex flex-wrap items-center gap-2">
    <h3 class="sr-only">Active filters</h3>
    <ul class="flex flex-wrap items-center gap-2">
        <li><x-ui.filter-token label="Status: Pending" x-on:click="$el.closest('li').remove(); $refs.clear.focus()" /></li>
        <li><x-ui.filter-token label="Method: Bank transfer" x-on:click="$el.closest('li').remove(); $refs.clear.focus()" /></li>
        <li><x-ui.filter-token label="Amount: over ₦500,000" x-on:click="$el.closest('li').remove(); $refs.clear.focus()" /></li>
    </ul>
    <x-ui.button variant="ghost" size="sm" x-ref="clear" x-on:click="$root.querySelector('ul').replaceChildren()">Clear all</x-ui.button>
</div>
