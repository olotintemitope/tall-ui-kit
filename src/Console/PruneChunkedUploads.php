<?php

namespace TallUiKit\Console;

use Illuminate\Console\Command;
use TallUiKit\Uploads\ChunkedUploads;

/**
 * Deletes chunked uploads (finished or not) older than --hours (config
 * tall-ui-kit.chunked_uploads.expire_after, 24). Schedule it:
 * Schedule::command('tall-ui-kit:prune-uploads')->hourly();
 */
final class PruneChunkedUploads extends Command
{
    protected $signature = 'tall-ui-kit:prune-uploads {--hours= : Delete uploads older than this many hours}';

    protected $description = 'Delete old chunked uploads from x-ui.file';

    public function handle(): int
    {
        $count = ChunkedUploads::prune($this->option('hours') !== null ? (int) $this->option('hours') : null);
        $this->info("Deleted {$count} ".($count === 1 ? 'upload' : 'uploads').'.');

        return self::SUCCESS;
    }
}
