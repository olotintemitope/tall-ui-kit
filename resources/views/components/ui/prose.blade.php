{{--
    Long user- or admin-authored content (help articles, campaign briefs, ticket
    messages), styled by the scoped `.prose-kit` class in resources/css/app.css.
    Props: size (default 14/20 | sm 13/20 dense, for ticket threads).
    User HTML must be sanitised on the server before it is printed with {!! !!};
    never print raw user input here.
--}}
@props(['size' => 'default'])

<div {{ $attributes->class(['prose-kit', 'prose-kit-sm' => $size === 'sm']) }} data-prose>{{ $slot }}</div>