{{--
    Native month input styled as x-ui.date: same border, height and widths; the picker
    indicator is redrawn in ink-muted (app.css). Props: size, width, invalid, min, max,
    step. Wiring and passthrough as x-ui.input. docs/ui-kit-html.md lists browser support.
--}}
@props(['size' => 'default', 'invalid' => null, 'width' => null])

<x-ui.input type="month" :size="$size" :invalid="$invalid" :width="$width" {{ $attributes }} />
