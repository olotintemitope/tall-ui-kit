{{-- Form label in the field-label role (13px, 500, ink). Pass `for` to point at the control. --}}
<label {{ $attributes->class(['type-field-label peer-disabled:cursor-not-allowed peer-disabled:text-ink-muted']) }}>{{ $slot }}</label>
