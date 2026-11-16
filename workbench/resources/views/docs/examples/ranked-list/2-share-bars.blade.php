{{-- Share bars: each row against the largest value --}}
<div class="max-w-lg">
    <x-ui.ranked-list>
        <x-ui.ranked-item label="Card" :value="812" :share="1" />
        <x-ui.ranked-item label="Bank transfer" :value="464" :share="0.57" />
        <x-ui.ranked-item label="USSD" :value="139" :share="0.17" />
        <x-ui.ranked-item label="Mobile money" :value="58" :share="0.07" />
    </x-ui.ranked-list>
</div>
