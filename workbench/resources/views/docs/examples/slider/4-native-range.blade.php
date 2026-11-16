{{-- Native range with uneven steps --}}
<div class="max-w-md">
    <x-ui.range label="Playlist reach" dual :steps="[0, 1000, 5000, 10000, 50000, 100000, 500000]" :value="[1000, 100000]" open-ended />
</div>
