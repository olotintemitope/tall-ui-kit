<?php

namespace Workbench\App\Files;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use TallUiKit\Support\Files;

/**
 * Workbench /files: generated bodies for the demo drive, so previews and downloads work
 * without stored files: an SVG for images, a one-page PDF, text, a short WAV tone.
 * Only ids in the session's drive answer.
 */
final class DemoFileController
{
    public function __invoke(Request $request, string $id): Response
    {
        $item = DemoDrive::get($id);
        abort_if($item === null || $item['folder'], 404);
        $name = $item['name'];
        $kind = Files::kind($name, $item['type']);
        $download = $request->boolean('download');

        [$body, $type] = match ($kind) {
            'image' => [self::svg($name), 'image/svg+xml'],
            'pdf' => [self::pdf($name), 'application/pdf'],
            'audio' => [self::wav(), 'audio/wav'],
            'text' => [self::text($name), 'text/plain; charset=utf-8'],
            default => [str_repeat("demo\n", 64), 'application/octet-stream'],
        };

        // SVG and text never run script; the PDF viewer needs no sandbox (it would block it).
        $csp = in_array($kind, ['image', 'text'], true) ? ['Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; sandbox"] : [];

        return response($body, 200, $csp + [
            'Content-Type' => $type,
            'Content-Disposition' => ($download ? 'attachment' : 'inline').'; filename="'.addcslashes(preg_replace('/[^\x20-\x7E]/', '_', $name), '"\\').'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=600',
        ]);
    }

    public static function text(string $name): string
    {
        if (str_ends_with(strtolower($name), '.csv')) {
            $rows = ['date,description,amount'];
            for ($i = 1; $i <= 40; $i++) {
                $rows[] = sprintf('2026-10-%02d,Transfer to <script>alert(%d)</script> vendor %d,%d.00', min(28, $i), $i, $i, $i * 1250);
            }

            return implode("\n", $rows)."\n";
        }

        return "# Meeting notes\n\n- Ship the file components in v0.9.\n- Names are user input: <b>escape</b> them.\n- Check 320px: long names wrap.\n";
    }

    private static function svg(string $name): string
    {
        $hue = hexdec(substr(md5($name), 0, 2)) % 360;

        return '<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="800" viewBox="0 0 1200 800">'
            .'<rect width="1200" height="800" fill="hsl('.$hue.',30%,86%)"/>'
            .'<circle cx="600" cy="400" r="220" fill="hsl('.$hue.',30%,62%)"/>'
            .'<rect x="120" y="560" width="960" height="120" fill="hsl('.$hue.',25%,40%)"/></svg>';
    }

    private static function pdf(string $name): string
    {
        $text = preg_replace('/[^A-Za-z0-9 .\-]/', '', $name);
        $stream = "BT /F1 24 Tf 72 720 Td ({$text}) Tj ET";
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            '<< /Length '.strlen($stream)." >>\nstream\n{$stream}\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];
        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $i => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($i + 1)." 0 obj\n{$object}\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        return $pdf.'trailer << /Size '.(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
    }

    private static function wav(): string
    {
        $rate = 8000;
        $samples = '';
        for ($i = 0; $i < $rate * 2; $i++) {
            $samples .= chr((int) (128 + 40 * sin(2 * M_PI * 440 * $i / $rate)));
        }

        return 'RIFF'.pack('V', 36 + strlen($samples)).'WAVEfmt '.pack('VvvVVvv', 16, 1, 1, $rate, $rate, 1, 8).'data'.pack('V', strlen($samples)).$samples;
    }
}
