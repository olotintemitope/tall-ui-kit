<?php

return [
    'intro' => 'Custom date and time fields with a popup below the field, never the browser\'s picker: `x-ui.date`, `x-ui.datetime`, `x-ui.time-input`, `x-ui.month`, `x-ui.week` and `x-ui.date-range`, all on the `x-ui.date-picker` engine. Values stay ISO.',
    'keyboard' => [
        ['Down / Alt+Down', 'In the field: opens the popup.'],
        ['Enter', 'In the field: parses the typed text (15/11/2026, 15 Nov 2026, today, 6pm).'],
        ['Escape', 'In the field: puts the last value back. In the popup: closes it and returns focus to what opened it (in a range with the start chosen, drops the start first).'],
        ['Left / Right', 'Day grid: a day. Month grid: a month. Time columns: switch column.'],
        ['Up / Down', 'Day grid: a week. Month grid: a row of 3 months. Week grid: a week. Time columns: an hour or minute.'],
        ['Page Up / Page Down', 'Day and week grids: a month. Month grid: a year.'],
        ['Shift+Page Up / Shift+Page Down', 'Day and week grids: a year.'],
        ['Home / End', 'Day grid: start and end of the week. Month grid: the row. Week grid: the month\'s first and last week.'],
        ['Enter / Space', 'Chooses the focused day, month, week, hour or minute.'],
        ['Tab / Shift+Tab', 'Cycles inside the popup (a modal dialog).'],
    ],
    'a11y' => [
        'The popup is a `role="dialog"` named "Choose date"; the grid is a `table role="grid"` labelled by a heading that announces the month politely as it changes.',
        'Days have a full date as their name, `aria-selected`, `aria-disabled` and `aria-current="date"` for today; disabled days are struck through, never faded.',
        'Focus moves into the grid (the chosen day, else today) on open and back to the calendar button after a choice.',
        'Typed text that is not a date, or is outside `min` and `max`, leaves the value as it was, marks the field invalid and says why in a polite region.',
        'The month and year buttons open custom listboxes below them: no native `<select>`.',
    ],
    'events' => [
        'Bind `wire:model` (any modifier) or `x-model` on the component. Values: date `Y-m-d`, datetime `Y-m-d\TH:i`, time `H:i` (also with `hours="12"`), month `Y-m`, week `2026-W47`.',
        'Range: `<x-ui.date-range wire:model.start.live="from" wire:model.end.live="to" />` sends one request when the range is chosen, or bind one array `[\'start\' => , \'end\' => ]` with `wire:model`. Plain forms post `name[start]` and `name[end]`.',
        'Inside a Livewire view the server prints the label from the bound value, so the first paint and every morph show it. Preset dates are worked out on the server in the app timezone.',
    ],
    'do' => [
        'Pass `min`, `max` and `disabledDates` (dates, `sat`, `sun`, `weekends`) so unavailable days cannot be chosen, and check them again on the server.',
        'Put `defer` on pickers in long lists.',
    ],
    'dont' => [
        'Use `<input type="date">` or any native date or time type: `DatePickerTest` fails on one.',
        'Use two date fields for a period: use `x-ui.date-range`.',
    ],
];
