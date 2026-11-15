{{--
    Long form with an in-page section list. From lg the list is a sticky 200px column on
    the left (under the top bar); the link for the section in view gets
    aria-current="true" and ink text (IntersectionObserver in uiSectionNav, no scroll
    listener); a click scrolls to the section and moves focus to its heading. Below lg
    the list sits above the form as plain links, not sticky. Give each section an id and
    an h2 (x-ui.form-section does both with `id`).
    Props: sections (id => label), label ("Sections"). Slot: the sections.
--}}
@props(['sections' => [], 'label' => 'Sections'])

<div {{ $attributes->class(['grid grid-cols-1 items-start gap-6 lg:grid-cols-[200px_minmax(0,1fr)]']) }} data-form-layout="section-nav">
    <nav aria-label="{{ $label }}" x-data="uiSectionNav" class="min-w-0 lg:sticky lg:top-20" data-section-nav>
        <ul class="flex flex-wrap gap-x-4 gap-y-1 lg:flex-col lg:gap-0.5">
            @foreach ($sections as $id => $text)
                <li>
                    <a
                        href="#{{ $id }}"
                        x-on:click="go($event, @js($id))"
                        x-bind:aria-current="current === @js($id) ? 'true' : null"
                        x-bind:class="current === @js($id) ? 'text-ink lg:bg-black/6' : 'text-ink-muted'"
                        class="block rounded-md py-1 text-body transition-colors duration-100 hover:text-ink focus-ring lg:px-2.5 aria-[current=true]:font-medium"
                    >{{ $text }}</a>
                </li>
            @endforeach
        </ul>
    </nav>
    <div class="flex min-w-0 flex-col gap-6">{{ $slot }}</div>
</div>
