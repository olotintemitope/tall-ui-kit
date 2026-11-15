{{--
    Native date input styled like x-ui.input (the browser draws the picker; the WebKit
    indicator is redrawn in ink-muted in app.css), w-40 by default. Props: size, width,
    invalid, min, max. Wiring and passthrough as x-ui.input. Siblings with the same
    look: x-ui.time-input, x-ui.datetime, x-ui.month, x-ui.week.
--}}
@props(['size' => 'default', 'invalid' => null, 'width' => null])

<x-ui.input type="date" :size="$size" :invalid="$invalid" :width="$width" {{ $attributes }} />
