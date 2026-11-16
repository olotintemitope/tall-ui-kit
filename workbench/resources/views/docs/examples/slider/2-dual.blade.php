{{-- Two thumbs that never cross --}}
<div class="max-w-md">
    <x-ui.slider label="Follower range" dual :min="0" :max="1000000" :step="10000" :page-step="100000" :value="[50000, 400000]" min-label="Fewest followers" max-label="Most followers" />
</div>
