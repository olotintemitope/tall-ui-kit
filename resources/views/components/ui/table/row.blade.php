{{-- Table row with a quiet hover. Attributes (wire:key, data-*) pass through. --}}
<tr {{ $attributes->class(['transition-colors duration-100 hover:bg-panel-footer']) }}>{{ $slot }}</tr>
