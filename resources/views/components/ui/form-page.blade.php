{{--
    Tinted level-0 region for forms inside an otherwise white, flat page. It bleeds to the
    edges of <main> so panels never sit on plain white. Whole form pages should instead
    pass surface="muted" to their layout. Children are 24px apart.
--}}
<div {{ $attributes->class(['-mx-4 flex flex-col gap-6 bg-page-muted px-4 py-6 md:-mx-6 md:px-6 lg:-mx-8 lg:px-8']) }} data-form-page>{{ $slot }}</div>
