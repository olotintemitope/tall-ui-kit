<?php

use Illuminate\Auth\GenericUser;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use TallUiKit\Support\ChromeCache;
use TallUiKit\Support\Files;
use TallUiKit\Support\KitContrast;
use TallUiKit\Uploads\ChunkedUploads;
use Workbench\App\Files\DemoDrive;
use Workbench\App\Livewire\Files\ManagerDemo;
use Workbench\App\Livewire\Files\PreviewDemo;
use Workbench\App\Livewire\Files\UploadFieldsDemo;

/*
| File components (docs/components/files.md): x-ui.file-card, x-ui.attachment-list,
| x-ui.file-manager, x-ui.file-preview, the upgraded x-ui.file and its chunk endpoint.
| Markup, accessibility attributes, escaping of user-supplied names, backward
| compatibility of x-ui.file and x-ui.upload-progress, and the payload budget.
*/

function fc(string $blade, array $data = []): string
{
    return Blade::render($blade, $data);
}

const EVIL = '<img src=x onerror=alert(1)>.pdf';

describe('Files helper', function () {
    it('labels types from the name, then the mime type', function () {
        expect(Files::typeLabel('Report.PDF'))->toBe('PDF')
            ->and(Files::typeLabel('photo.jpeg'))->toBe('JPG')
            ->and(Files::typeLabel('song', 'audio/mpeg'))->toBe('MP3')
            ->and(Files::typeLabel('archive.tar.gz'))->toBe('GZ')
            ->and(Files::typeLabel('no-extension'))->toBe('FILE')
            ->and(Files::typeLabel('weird.verylongext'))->toBe('FILE');
    });

    it('picks the preview kind and formats sizes and hints', function () {
        expect(Files::kind('a.pdf'))->toBe('pdf')
            ->and(Files::kind('a', 'image/png'))->toBe('image')
            ->and(Files::kind('a.mp3'))->toBe('audio')
            ->and(Files::kind('a.webm'))->toBe('video')
            ->and(Files::kind('a.csv'))->toBe('text')
            ->and(Files::kind('a.exe'))->toBe('other')
            ->and(Files::bytes(2400000))->toBe('2.4 MB')
            ->and(Files::bytes(980))->toBe('980 B')
            ->and(Files::parseSize('10MB'))->toBe(10485760)
            ->and(Files::parseSize('500 kb'))->toBe(512000)
            ->and(Files::hint('.pdf,image/*', 5242880, 4, 10485760, true))->toBe('PDF or images, up to 5 MB each. At most 4 files, 10 MB in total.')
            ->and(Files::hint(null, null, null, null, false))->toBeNull();
    });
});

describe('file card and attachment list', function () {
    it('shows the type label, the full wrapping name, size, a one-line status pill and named actions', function () {
        $html = fc('<x-ui.file-card name="Quarterly report.pdf" :size="2400000" status="failed" error="Dropped." retry-action="retry(1)" remove-action="remove(1)" />');

        expect($html)->toContain('class="ui-file-type')->toContain('>PDF<')
            ->toContain('wrap-anywhere')->not->toMatch('/\b(truncate|line-clamp|text-ellipsis)\b/')
            ->toContain('2.4 MB')
            ->toContain('class="ui-badge" data-tone="danger" data-size="sm" data-file-status>Failed')
            ->toContain('aria-label="Retry upload of Quarterly report.pdf"')->toContain('wire:click="retry(1)"')
            ->toContain('aria-label="Remove Quarterly report.pdf"')
            ->toContain('Dropped.');
    });

    it('never offers Download for infected or blocked files', function () {
        foreach (['infected' => 'Infected', 'blocked' => 'Blocked'] as $status => $word) {
            $html = fc('<x-ui.file-card name="x.exe" href="/x" status="'.$status.'" remove-action="rm" />');
            expect($html)->toContain('>'.$word.'</span>')->not->toContain('data-file-action="download"')->toContain('data-file-action="remove"');
        }
    });

    it('shows a progress bar while uploading and Cancel through an event without Livewire', function () {
        $html = fc('<x-ui.file-card name="a.zip" status="uploading" :progress="45" :actions="[\'cancel\']" />');

        expect($html)->toContain('Uploading 45%')->toContain('<progress')->toContain('Cancel upload of a.zip')->toContain('ui-file');
    });

    it('makes a compact card with a link one link', function () {
        $html = fc('<x-ui.file-card variant="compact" name="Brief.pdf" href="/brief" meta="240 KB" data-chat-file />');

        expect($html)->toMatch('/<a href="\/brief"[^>]*data-chat-file/')->toContain('240 KB')->toContain(', PDF file')->not->toContain('<button');
    });

    it('escapes names everywhere they appear', function () {
        $html = fc('<x-ui.file-card :name="$n" href="/x" remove-action="rm" />', ['n' => EVIL]);
        $list = fc('<x-ui.attachment-list :items="[[\'key\' => \'a\', \'name\' => $n]]" remove-action="removeFile" />', ['n' => EVIL]);

        foreach ([$html, $list] as $out) {
            expect($out)->not->toContain('<img src=x')->toContain('&lt;img src=x onerror=alert(1)&gt;');
        }
    });

    it('lists files in a named list, calls actions with the key, and prints the empty line', function () {
        $html = fc('<x-ui.attachment-list label="Receipts" :items="[[\'key\' => \'r-1\', \'name\' => \'a.pdf\'], [\'key\' => \'r-2\', \'name\' => \'b.png\', \'status\' => \'done\']]" remove-action="removeReceipt" variant="card" />');

        expect($html)->toContain('<ul aria-label="Receipts"')->toContain('data-attachment-list="card"')
            ->toContain('wire:click="removeReceipt(&#039;r-1&#039;)"')->toContain('Uploaded')
            ->and(fc('<x-ui.attachment-list :items="[]" empty="No files." />'))->toContain('No files.')->not->toContain('<ul');
    });
});

describe('file preview', function () {
    $base = ['key' => 'k', 'size' => 1200, 'created' => '2026-11-10 10:00:00', 'modified' => '2026-11-12 10:00:00'];

    it('picks the viewer by type and uses the URLs the app gives', function () use ($base) {
        expect(fc('<x-ui.file-preview :file="$f" />', ['f' => $base + ['name' => 'a.png', 'url' => '/f/a']]))
            ->toContain('data-file-preview="image"')->toContain('src="/f/a"')->toContain('aria-label="Zoom in"')->toContain('data-zoom="fit"')
            ->and(fc('<x-ui.file-preview :file="$f" />', ['f' => $base + ['name' => 'a.pdf', 'url' => '/f/b']]))
            ->toContain('<object data="/f/b" type="application/pdf"')->toContain('Open a.pdf in a new tab')
            ->and(fc('<x-ui.file-preview :file="$f" />', ['f' => $base + ['name' => 'a.mp3', 'url' => '/f/c']]))
            ->toContain('data-file-preview="audio"')->toContain('<audio')
            ->and(fc('<x-ui.file-preview :file="$f" />', ['f' => $base + ['name' => 'a.mp4', 'url' => '/f/d']]))
            ->toContain('<video')
            ->and(fc('<x-ui.file-preview :file="$f" />', ['f' => $base + ['name' => 'a.bin', 'url' => '/f/e']]))
            ->toContain('No preview for this type of file')->toContain('download');
    });

    it('shows text through code-block, escaped and capped', function () use ($base) {
        $html = fc('<x-ui.file-preview :file="$f" :max-text="40" />', ['f' => $base + ['name' => 'notes.md', 'url' => '/n', 'text' => '<script>alert(1)</script> and a lot more text after it']]);

        expect($html)->toContain('data-code-block')->toContain('&lt;script&gt;alert(1)&lt;/script&gt;')->not->toContain('<script>alert')
            ->toContain('Showing the first 40 characters');
    });

    it('lists details with x-ui.time and custom fields, and steps through a set', function () use ($base) {
        $files = [['key' => 'a', 'name' => 'a.png'], ['key' => 'k', 'name' => 'b.pdf'], ['key' => 'c', 'name' => 'c.txt']];
        $html = fc('<x-ui.file-preview :file="$f" :files="$files" navigate-action="show" />', ['f' => $base + ['name' => 'b.pdf', 'url' => '/b', 'fields' => ['Owner' => EVIL]], 'files' => $files]);

        expect($html)->toContain('2 of 3')->toContain('aria-label="Previous file: a.png"')->toContain('aria-label="Next file: c.txt"')
            ->toContain('<time datetime="2026-11-10')->toContain('Owner')->not->toContain('<img src=x')
            ->toContain('uiFilePreview')->toContain('show');
    });

    it('renders in a dialog or a sheet by name', function () use ($base) {
        expect(fc('<x-ui.file-preview variant="dialog" name="pv" :file="$f" />', ['f' => $base + ['name' => 'a.png', 'url' => '/a']]))->toContain('data-dialog-host="pv"')
            ->and(fc('<x-ui.file-preview variant="sheet" name="ps" :file="$f" />', ['f' => $base + ['name' => 'a.png', 'url' => '/a']]))->toContain('data-dialog-host="ps"');
    });
});

describe('file manager', function () {
    $items = [
        ['id' => 'f1', 'name' => 'Invoices', 'folder' => true, 'count' => 3, 'modified' => '2026-11-12 10:00:00'],
        ['id' => 'f2', 'name' => EVIL, 'size' => 2400000, 'href' => '/dl/2', 'modified' => '2026-11-15 10:00:00'],
    ];

    it('is one multi-select listbox with roving focus and no Alpine per item', function () use ($items) {
        $html = fc('<x-ui.file-manager :items="$items" :breadcrumbs="[[\'id\' => null, \'label\' => \'My files\']]" open-action="openItem" rename-action="rename" move-action="move" delete-action="delete" create-folder-action="createFolder" sort-action="sortBy" upload="uploads" id="fm" />', ['items' => $items]);

        expect($html)->toContain('role="listbox" aria-multiselectable="true" aria-label="My files"')
            ->and(substr_count($html, 'role="option"'))->toBe(2)
            ->and(substr_count($html, 'aria-selected="false" tabindex="0"'))->toBe(1)
            ->and($html)->toContain('wire:ignore.self')->toContain('aria-selected="false"')
            ->toContain('data-folder')->toContain('3 items')->toContain('2.4 MB')->toContain('data-href="/dl/2"')
            ->toContain('uiFileManager')->toContain('role="status" aria-live="polite"')
            ->toContain('aria-current="location"')
            ->toContain('data-fm-move-dialog')->toContain('role="tree"')
            ->toContain('data-confirm="destructive"')
            ->toContain('wire:model="uploads"')->toContain('data-upload-list="uploads"')
            ->toContain('role="menuitemradio"')
            ->and(substr_count($html, 'x-data'))->toBeLessThan(20);
    });

    it('escapes item names', function () use ($items) {
        $html = fc('<x-ui.file-manager :items="$items" />', ['items' => $items]);

        expect($html)->not->toContain('<img src=x')->toContain('&lt;img src=x onerror=alert(1)&gt;.pdf');
    });

    it('hides commands that have no action', function () use ($items) {
        $html = fc('<x-ui.file-manager :items="$items" />', ['items' => $items]);

        expect($html)->not->toContain('data-fm-move-dialog')->not->toContain('data-confirm=')->not->toContain('data-fm-new-folder')
            ->not->toContain('data-fm-upload')->not->toContain('data-fm-action="rename"');
    });

    it('has loading, error and empty states, and Load more', function () use ($items) {
        expect(fc('<x-ui.file-manager loading />'))->toContain('data-fm-skeleton')->toContain('role="status"')
            ->and(fc('<x-ui.file-manager error="Could not load." retry-action="retry" />'))->toContain('data-alert="danger"')->toContain('Could not load.')->toContain('wire:click="retry"')
            ->and(fc('<x-ui.file-manager :items="[]" empty="Nothing here." />'))->toContain('Nothing here.')->not->toContain('role="listbox"')
            ->and(fc('<x-ui.file-manager :items="$items" :total="60" load-more-action="loadMore" />', ['items' => $items]))->toContain('Showing 2 of 60');
    });

    it('lays out grid and list from one markup, with the stored view', function () use ($items) {
        expect(fc('<x-ui.file-manager :items="$items" view="list" persist="my-view" />', ['items' => $items]))
            ->toContain('data-view="list"')->toContain('my-view')->toContain('data-segmented');
    });
});

describe('file field: backward compatibility', function () {
    it('renders exactly the v0.8 markup without the new props', function () {
        $html = fc('<x-ui.file name="a" multiple accept=".pdf" hint="PDF only." />');

        expect($html)->toContain('read(input)')->toContain('x-on:change="read($el)"')->not->toContain('uiFile(')
            ->toContain('PDF only.')->toContain('aria-label="Chosen files"')->toContain('livewire-upload-progress')
            ->and(fc('<x-ui.file name="b" variant="images" multiple />'))->toContain('data-file-previews')->toContain('accept="image/*"')->not->toContain('uiFile(')
            ->and(fc('<x-ui.file name="c"><x-slot:files><p>Server list</p></x-slot:files></x-ui.file>'))->toContain('Server list');
    });

    it('keeps capture on the plain input', function () {
        expect(fc('<x-ui.file name="p" capture="environment" />'))->toContain('capture="environment"')->not->toContain('uiFile(');
    });

    it('keeps x-ui.upload-progress rows and the Livewire list', function () {
        $row = fc('<x-ui.upload-progress name="stems.zip" :size="2400000" :progress="45" cancel-action="cancel(1)" />');

        expect($row)->toContain('data-upload-row')->toContain('data-status="uploading"')->toContain('Uploading, 45%')
            ->toContain('data-upload-action="cancel"')->toContain('>ZIP<')
            ->and(fc('<x-ui.upload-progress.list for="files" multiple />'))->toContain('uiUploadProgress')->toContain('data-upload-list="files"')->toContain('type(file)');
    });

    it('keeps chat and timeline attachments as links with their hooks', function () {
        $chat = fc('<x-ui.chat.message key="m1" author="Ada" body="Hi" :attachments="[[\'type\' => \'file\', \'name\' => $n, \'href\' => \'/a\', \'meta\' => \'2 KB\']]" />', ['n' => EVIL]);
        $timeline = fc('<x-ui.timeline.item actor="Ada" text="attached" :attachment="[\'name\' => \'s.pdf\', \'href\' => \'/s\', \'meta\' => \'240 KB\']" />');

        expect($chat)->toMatch('/<a href="\/a"[^>]*data-chat-file/')->toContain('2 KB')->not->toContain('<img src=x')
            ->and($timeline)->toMatch('/<a href="\/s"[^>]*data-timeline-attachment/')->toContain('240 KB');
    });
});

describe('file field: new props', function () {
    it('switches to the lazy component with limits and writes the hint', function () {
        $html = fc('<x-ui.file name="d" multiple accept=".pdf,image/*" max-size="5MB" :max-files="4" max-total-size="10MB" paste />');

        expect($html)->toContain('uiFile(')->toContain('PDF or images, up to 5 MB each. At most 4 files, 10 MB in total. You can also paste files.')
            ->toContain('aria-label="Files not added"')->toContain('wire:ignore')->toContain('data-file-paste')
            ->toContain('5242880');
    });

    it('makes the chosen files sortable with handles and move buttons', function () {
        $html = fc('<x-ui.file name="d" multiple reorder reorder-action="reorderDocuments" />');

        expect($html)->toContain('data-sortable data-file-list')->toContain('data-sortable-handle')->toContain('data-sortable-move="up"')
            ->toContain('data-sortable-live')->toContain('reorderDocuments');
    });

    it('marks a chunked input and passes the endpoint', function () {
        $html = fc('<x-ui.file name="v" chunked="/ui/uploads/" capture />');

        expect($html)->toContain('data-chunked')->toContain('capture="environment"')->toContain('uploads');
    });
});

describe('chunked uploads', function () {
    beforeEach(function () {
        $this->actingAs(new GenericUser(['id' => 7]));
        Storage::fake('chunks');
        config(['tall-ui-kit.chunked_uploads.disk' => 'chunks', 'tall-ui-kit.chunked_uploads.chunk_size' => 64 * 1024]);
    });

    $patch = fn ($test, string $id, string $bytes, int $offset) => $test->call('PATCH', '/workbench/uploads/'.$id, [], [], [], [
        'HTTP_UPLOAD_OFFSET' => (string) $offset, 'CONTENT_TYPE' => 'application/octet-stream', 'CONTENT_LENGTH' => (string) strlen($bytes), 'HTTP_ACCEPT' => 'application/json',
    ], $bytes);

    it('starts, appends at the offset, resumes, assembles and hands the file over', function () use ($patch) {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $body = str_repeat('a', 70 * 1024).str_repeat('b', 30 * 1024);
        $id = $this->postJson('/workbench/uploads', ['name' => '../../etc/'.EVIL, 'size' => strlen($body), 'type' => 'text/plain'])
            ->assertCreated()->assertJsonPath('offset', 0)->assertJsonPath('chunk_size', 65536)->json('id');

        $patch($this, $id, substr($body, 0, 65536), 0)->assertOk()->assertJsonPath('offset', 65536)->assertJsonPath('done', false);
        // A retried piece at an old offset is refused with the server's offset (resume point).
        $patch($this, $id, substr($body, 0, 65536), 0)->assertStatus(409)->assertJsonPath('offset', 65536);
        $this->getJson('/workbench/uploads/'.$id)->assertOk()->assertJsonPath('offset', 65536);
        $patch($this, $id, str_repeat('x', 70000), 65536)->assertStatus(413);
        $patch($this, $id, substr($body, 65536), 65536)->assertOk()->assertJsonPath('done', true);

        $upload = ChunkedUploads::find($id);
        expect($upload->done())->toBeTrue()
            ->and($upload->name())->toBe(EVIL)
            ->and(file_get_contents($upload->path()))->toBe($body)
            ->and($upload->toUploadedFile()->getClientOriginalName())->toBe(EVIL);

        $path = $upload->store('kept', 'chunks');
        expect($path)->toStartWith('kept/')->toEndWith('.pdf')
            ->and(Storage::disk('chunks')->get($path))->toBe($body)
            ->and(ChunkedUploads::find($id))->toBeNull();
    });

    it('refuses other people, bad ids and oversize files, and deletes on request', function () use ($patch) {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        config(['tall-ui-kit.chunked_uploads.max_size' => 1000]);
        $this->postJson('/workbench/uploads', ['name' => 'big.bin', 'size' => 5000])->assertStatus(422);
        $id = $this->postJson('/workbench/uploads', ['name' => 'a.txt', 'size' => 10])->json('id');

        $this->getJson('/workbench/uploads/not-a-uuid')->assertNotFound();
        // Someone else: not found, not forbidden (no hint that it exists).
        $this->actingAs(new GenericUser(['id' => 8]));
        expect(ChunkedUploads::find($id))->toBeNull();
        $patch($this, $id, 'abc', 0)->assertNotFound();
        $this->getJson('/workbench/uploads/'.$id)->assertNotFound();
    });

    it('cancels with DELETE and prunes old uploads', function () {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $id = $this->postJson('/workbench/uploads', ['name' => 'a.txt', 'size' => 10])->json('id');
        $this->deleteJson('/workbench/uploads/'.$id)->assertNoContent();
        expect(ChunkedUploads::find($id))->toBeNull();

        $old = ChunkedUploads::create('old.txt', 5, null);
        $this->travel(2)->days();
        $this->artisan('tall-ui-kit:prune-uploads')->expectsOutputToContain('Deleted 1 upload.')->assertSuccessful();
        expect(Storage::disk('chunks')->exists($old->folder()))->toBeFalse();
    });

    it('cleans names to a safe base name', function () {
        expect(ChunkedUploads::cleanName('../../a/b.txt'))->toBe('b.txt')
            ->and(ChunkedUploads::cleanName("bad\x00name.pdf"))->toBe('badname.pdf')
            ->and(ChunkedUploads::cleanName('...'))->toBe('upload');
    });
});

describe('workbench demos', function () {
    beforeEach(fn () => DemoDrive::reset());

    it('renames, refuses duplicates, moves, refuses a folder into itself, deletes and creates folders', function () {
        $c = Livewire::test(ManagerDemo::class);

        expect($c->instance()->rename('notes', 'Invoices'))->toContain('already')
            ->and($c->instance()->rename('notes', 'Minutes.md'))->toBeNull()
            ->and($c->instance()->move(['invoices'], 'paid'))->toContain('into itself')
            ->and($c->instance()->move(['report'], 'invoices'))->toBeNull()
            ->and(DemoDrive::get('report')['parent'])->toBe('invoices')
            ->and($c->instance()->delete(['invoices']))->toBeNull()
            ->and(DemoDrive::get('inv-0999'))->toBeNull()
            ->and($c->instance()->createFolder('Mixes'))->toBeNull()
            ->and($c->instance()->createFolder('a/b'))->toContain('slashes');

        Livewire::test(ManagerDemo::class)->call('openItem', 'nope')->assertStatus(404);
    });

    it('sends the manager chrome once, then only a placeholder', function () {
        preg_match_all('/data-fm-chrome="([^"]+)"/', Livewire::test(ManagerDemo::class)->html(), $all);
        $m = [1 => $all[1][0]];
        $full = Livewire::test(ManagerDemo::class)->call('openItem', 'scans')->html();
        $test = Livewire::test(ManagerDemo::class);
        ChromeCache::fake(array_unique($all[1]));
        $lean = $test->call('openItem', 'scans')->html();
        ChromeCache::fake(null);

        expect($m[1])->toStartWith('drive:')
            ->and($full)->toContain('data-fm-bulk-bar')->toContain('data-fm-move-dialog')
            ->and($lean)->not->toContain('data-fm-bulk-bar')->not->toContain('data-fm-move-dialog')
            ->toContain('wire:key="'.$m[1].'-top" wire:ignore')->toContain('scan-0001.pdf')
            ->and(strlen($lean))->toBeLessThan(strlen($full) / 2);
    });

    it('keeps every manager, preview and upload response under the 40 KB payload budget', function () {
        preg_match('/data-fm-chrome="([^"]+)"/', Livewire::test(ManagerDemo::class)->html(), $m);
        // As in the browser: the first render is the page; every update names the chrome it holds.
        $manager = function () use ($m) {
            ChromeCache::fake(null);
            $test = Livewire::test(ManagerDemo::class);
            ChromeCache::fake([$m[1]]);

            return $test;
        };
        $sizes = [
            'open folder' => $manager()->call('openItem', 'scans')->effects,
            'load more' => $manager()->call('openItem', 'scans')->call('loadMore')->effects,
            'rename' => $manager()->call('rename', 'notes', 'New notes.md')->effects,
            'sort' => $manager()->call('sortBy', 'size', 'desc')->effects,
            'open file' => $manager()->call('openItem', 'notes')->effects,
            'move' => $manager()->call('move', ['report'], 'invoices')->effects,
            'delete' => $manager()->call('delete', ['archive'])->effects,
            'create folder (Move dialog resent)' => $manager()->call('createFolder', 'Mixes')->effects,
            'preview next' => Livewire::test(PreviewDemo::class)->call('show', 'long')->effects,
            'upload fields' => Livewire::test(UploadFieldsDemo::class)->set('large', ['not-mine'])->effects,
        ];

        ChromeCache::fake(null);

        foreach ($sizes as $name => $effects) {
            expect(strlen(json_encode($effects)))->toBeLessThan(40_000, $name);
        }
    });

    it('steps the preview only through known keys and reorders uploads by index', function () {
        Livewire::test(PreviewDemo::class)->call('show', 'report')->assertSet('current', 'report')->call('show', '../etc')->assertStatus(404);
        Livewire::test(UploadFieldsDemo::class)->call('reorderDocuments', [0, 0])->assertStatus(422);
    });

    it('renders the Files gallery page', function () {
        $this->get('/files')->assertOk()->assertSee('File manager')->assertSee('data-file-manager', false)->assertDontSee('<select', false);
    });
});

it('measures the file component colour pairs', function () {
    expect(collect(KitContrast::pairs())->filter(fn ($p) => str_starts_with($p[0], 'File'))->count())->toBeGreaterThanOrEqual(15);
});
