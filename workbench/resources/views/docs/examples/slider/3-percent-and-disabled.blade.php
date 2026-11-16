{{-- Percent, units and disabled --}}
<div class="flex max-w-md flex-col gap-6">
    <x-ui.slider label="Revenue share" :value="70" format="percent" :step="5" />
    <x-ui.slider label="Minimum saves" :max="500" :step="10" :value="120" unit="saves" />
    <x-ui.slider label="Platform fee" :value="10" format="percent" disabled />
</div>
