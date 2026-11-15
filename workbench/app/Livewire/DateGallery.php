<?php

namespace Workbench\App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Workbench /dates: every custom date and time picker bound to a Livewire property,
 * so the value the server receives (shown beside each one) can be checked: ISO Y-m-d,
 * H:i, Y-m, 2026-W47, Y-m-d\TH:i, and a range as two dates. Plain sample data.
 */
class DateGallery extends Component
{
    public string $launch = '2026-11-21';

    public string $deadline = '';

    public string $from = '2026-11-01';

    public string $to = '2026-11-15';

    /** @var array{start: string, end: string} */
    public array $period = ['start' => '', 'end' => ''];

    public string $startsAt = '2026-11-21T18:00';

    public string $digest = '09:30';

    public string $dropTime = '18:00';

    public string $month = '2026-11';

    public string $week = '2026-W47';

    public string $dialogDate = '';

    public function save(): void
    {
        $this->validate([
            'launch' => ['required', 'date_format:Y-m-d'],
            'deadline' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'startsAt' => ['nullable', 'date_format:Y-m-d\TH:i'],
            'digest' => ['nullable', 'date_format:H:i'],
            'month' => ['nullable', 'date_format:Y-m'],
            'week' => ['nullable', 'regex:/^\d{4}-W\d{2}$/'],
        ]);

        $this->dispatch('toast', variant: 'success', title: 'Saved.');
    }

    public function render(): View
    {
        return view('livewire.date-gallery');
    }
}
