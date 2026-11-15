{{-- Workbench /dates: the custom date and time pickers, live (DateGallery). --}}
<x-workbench::gallery.layout title="Dates">
    <x-ui.page-header eyebrow="UI kit" title="Dates and times" description="Custom pickers: the popup opens below its field, typing is parsed, and the server receives ISO values." />
    @livewire(\Workbench\App\Livewire\DateGallery::class)
</x-workbench::gallery.layout>
