@php
    use TallUiKit\Support\KitContrast;
    use Workbench\App\Docs\Markdown;

    // Every colour token, measured against the page (`surface`) in each of the four sets.
    $sets = [];
    foreach (KitContrast::THEMES as $theme) {
        foreach (KitContrast::MODES as $mode) {
            $sets[$theme.' '.$mode] = [$theme, $mode];
        }
    }
    $names = array_keys(KitContrast::lightTokens('neutral'));
    $rows = [];
    foreach ($names as $name) {
        $cells = [];
        foreach ($sets as [$theme, $mode]) {
            KitContrast::useTheme($theme, $mode);
            try {
                $hex = KitContrast::hex($name);
                $ratio = preg_match('/^#[0-9a-f]{6}$/i', $hex) ? KitContrast::ratio($name, 'surface') : null;
            } catch (\Throwable) {
                $hex = null;
                $ratio = null;
            }
            $cells[] = [$hex, $ratio];
        }
        $rows[$name] = $cells;
    }
    KitContrast::useTheme('neutral', 'light');
    $roles = [
        'type-title' => 'Payouts', 'type-heading' => 'Recent transfers', 'type-subheading' => 'Bank details',
        'type-label' => 'Status', 'type-body' => 'Funds arrive within one working day.', 'type-body-strong' => 'Adaeze Okafor',
        'type-field-label' => 'Account number', 'type-meta' => 'Updated 3 minutes ago', 'type-caption' => 'Use the 10-digit NUBAN.',
        'type-figure' => '₦4,250,000', 'type-numeric' => 'TXN-9F3C21AB',
    ];
@endphp
<x-workbench::docs.layout :title="$title" description="Colour tokens with measured contrast in all four theme and mode sets, type roles, spacing, radii, elevation, states and motion.">
    <div class="flex max-w-5xl flex-col gap-10">
        <header class="flex flex-col gap-2">
            <p class="type-label">Guide</p>
            <h1 class="type-title">Tokens</h1>
            <p class="type-body max-w-prose">Use tokens and type roles only. The tables below are read from the theme files at build time, so they always match the stylesheets.</p>
        </header>

        <section aria-labelledby="colours" class="flex flex-col gap-3">
            <h2 id="colours" class="type-heading">Colours</h2>
            <p class="type-meta max-w-prose">Each cell: the value and its contrast against the page (<code class="type-numeric">surface</code>) in that set. Text needs 4.5:1 and boundaries and marks 3:1; <code class="type-numeric">composer contrast</code> checks every pair the kit actually uses, on its real background.</p>
            <x-ui.table caption="Colour tokens in the four theme and mode sets" sticky>
                <x-slot:head>
                    <x-ui.table.th>Token</x-ui.table.th>
                    @foreach (array_keys($sets) as $set)<x-ui.table.th>{{ ucfirst($set) }}</x-ui.table.th>@endforeach
                </x-slot:head>
                @foreach ($rows as $name => $cells)
                    <x-ui.table.row>
                        <x-ui.table.td mono strong>{{ $name }}</x-ui.table.td>
                        @foreach ($cells as [$hex, $ratio])
                            <x-ui.table.td>
                                @if ($hex)
                                    <span class="flex items-center gap-2 whitespace-nowrap">
                                        <span class="size-4 shrink-0 rounded-sm border border-line-strong" style="background: {{ $hex }}" aria-hidden="true"></span>
                                        <span class="type-numeric">{{ $hex }}</span>
                                        @if ($ratio)<span class="type-caption">{{ number_format($ratio, 2) }}:1</span>@endif
                                    </span>
                                @else
                                    <span class="type-caption">n/a</span>
                                @endif
                            </x-ui.table.td>
                        @endforeach
                    </x-ui.table.row>
                @endforeach
            </x-ui.table>
            <x-ui.prose>{!! Markdown::file('docs/tokens.md', 'Colour tokens') !!}</x-ui.prose>
        </section>

        <section aria-labelledby="type" class="flex flex-col gap-3">
            <h2 id="type" class="type-heading">Type roles</h2>
            <x-ui.table caption="Type roles with samples">
                <x-slot:head>
                    <x-ui.table.th>Role</x-ui.table.th>
                    <x-ui.table.th>Sample</x-ui.table.th>
                </x-slot:head>
                @foreach ($roles as $role => $sample)
                    <x-ui.table.row>
                        <x-ui.table.td mono strong>{{ $role }}</x-ui.table.td>
                        <x-ui.table.td><span class="{{ $role }}">{{ $sample }}</span></x-ui.table.td>
                    </x-ui.table.row>
                @endforeach
            </x-ui.table>
            <x-ui.prose>{!! Markdown::file('docs/tokens.md', 'Type roles') !!}</x-ui.prose>
        </section>

        <section aria-labelledby="spacing" class="flex flex-col gap-3">
            <h2 id="spacing" class="type-heading">Spacing, radii, elevation, focus</h2>
            <x-ui.prose>{!! Markdown::file('docs/tokens.md', 'Spacing, radii, elevation, focus') !!}</x-ui.prose>
        </section>

        <section aria-labelledby="states" class="flex flex-col gap-3">
            <h2 id="states" class="type-heading">States</h2>
            <x-ui.prose>{!! Markdown::file('docs/tokens.md', 'States') !!}</x-ui.prose>
        </section>

        <section aria-labelledby="motion" class="flex flex-col gap-3">
            <h2 id="motion" class="type-heading">Motion</h2>
            <x-ui.prose>{!! Markdown::file('docs/motion.md') !!}</x-ui.prose>
        </section>
    </div>
</x-workbench::docs.layout>
