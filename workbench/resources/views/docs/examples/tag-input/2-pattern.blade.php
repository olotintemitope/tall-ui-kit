{{-- Codes checked against a pattern --}}
<x-ui.field label="Discount codes" name="tagCodes" hint="Paste several, separated by commas or one per line.">
    <x-ui.tag-input :value="['LAUNCH24', 'LAGOS10']" pattern="[A-Z0-9]{4,12}" pattern-message="Use 4 to 12 capital letters or digits." placeholder="Add a code" width="lg" />
</x-ui.field>
