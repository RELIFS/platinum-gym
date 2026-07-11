<?php

namespace App\Console\Commands;

use App\Models\AiConversation;
use Illuminate\Console\Command;

class PruneGymmiConversationsCommand extends Command
{
    protected $signature = 'gymmi:prune-conversations {--days= : Override retention days} {--dry-run : Count records without deleting them}';

    protected $description = 'Prune expired Gymmi operational conversation logs';

    public function handle(): int
    {
        $days = max(1, (int) ($this->option('days') ?: config('gymmi.retention_days', 30)));
        $cutoff = now()->subDays($days);
        $query = AiConversation::query()->where('updated_at', '<', $cutoff);
        $count = (clone $query)->count();

        if ($this->option('dry-run')) {
            $this->info("{$count} percakapan Gymmi lebih lama dari {$days} hari.");

            return self::SUCCESS;
        }

        $deleted = 0;

        do {
            $ids = AiConversation::query()
                ->where('updated_at', '<', $cutoff)
                ->orderBy('id')
                ->limit(200)
                ->pluck('id');

            if ($ids->isEmpty()) {
                break;
            }

            $deleted += AiConversation::query()->whereKey($ids)->delete();
        } while (true);

        $this->info("{$deleted} percakapan Gymmi dihapus.");

        return self::SUCCESS;
    }
}
