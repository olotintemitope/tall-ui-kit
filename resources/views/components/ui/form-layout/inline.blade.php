{{--
    Dense horizontal form: x-ui.field `inline` rows (label left in a 220px column, control
    right) divided by 1px `line` rules, inside a panel. For settings with many short
    controls (switches, selects, steppers). Stacks on phones.
    Props: none. Slot: x-ui.field inline elements (or x-ui.switch rows).
--}}
<div {{ $attributes->class(['flex flex-col divide-y divide-line -my-3']) }} data-form-layout="inline">{{ $slot }}</div>
