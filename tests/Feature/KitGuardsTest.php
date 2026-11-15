<?php

use Illuminate\Support\Facades\File;

/*
| Class-level guards for the kit's own views (docs/rules.md): nothing is cut off, no
| "AI look" classes, every border 1px, only tokens. Blade comments are ignored, so a
| comment may name a banned class.
*/

function kitViewSources(): array
{
    return collect(File::allFiles(kit_path('resources/views')))
        ->filter(fn ($file) => str_ends_with($file->getFilename(), '.blade.php'))
        ->mapWithKeys(fn ($file) => [$file->getRelativePathname() => preg_replace('/\{\{--.*?--\}\}/s', '', $file->getContents())])
        ->all();
}

function offenders(string $pattern): array
{
    return collect(kitViewSources())->filter(fn (string $source) => preg_match($pattern, $source))->keys()->values()->all();
}

it('never truncates, ellipsises or clamps text', function () {
    expect(offenders('/\b(truncate|text-ellipsis|line-clamp-\d)\b/'))->toBe([]);
});

it('uses no AI-look classes: gradients, blur, glow, oversized radii or thick edges', function () {
    expect(offenders('/\b(bg-gradient-|bg-linear-|backdrop-blur|rounded-(2xl|3xl)|border-[lrtbxy]?-[2-8]\b|divide-[xy]-[2-8]\b|shadow-(lg|xl|2xl))/'))->toBe([]);
});

it('uses tokens, not raw hex colours or Tailwind greys, in class attributes', function () {
    expect(offenders('/class="[^"]*(\[#[0-9a-fA-F]{3,6}\]|\bgray-\d)/'))->toBe([]);
});

it('never fades a disabled control with opacity', function () {
    expect(offenders('/(disabled|state-disabled):opacity-/'))->toBe([]);
});
