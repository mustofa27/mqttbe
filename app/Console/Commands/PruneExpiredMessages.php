<?php

namespace App\Console\Commands;

use App\Models\Message;
use Illuminate\Console\Command;

class PruneExpiredMessages extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'messages:prune-expired {--chunk=1000 : Number of rows to delete per batch}';

    /**
     * The console command description.
     */
    protected $description = 'Delete expired message rows based on expires_at';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $chunkSize = max((int) $this->option('chunk'), 1);
        $totalDeleted = 0;

        do {
            $ids = Message::whereNotNull('expires_at')
                ->where('expires_at', '<=', now())
                ->orderBy('id')
                ->limit($chunkSize)
                ->pluck('id');

            $deleted = 0;
            if ($ids->isNotEmpty()) {
                $deleted = Message::whereIn('id', $ids)->delete();
            }

            $totalDeleted += $deleted;
        } while ($deleted === $chunkSize);

        $this->info("Pruned {$totalDeleted} expired message(s).");

        return self::SUCCESS;
    }
}
