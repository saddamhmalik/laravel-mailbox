<?php

declare(strict_types=1);

namespace LaravelMailbox\Commands;

use Illuminate\Console\Command;
use LaravelMailbox\Services\PruneService;

final class PruneCommand extends Command
{
    protected $signature = 'mailbox:prune {--days= : Days to keep emails}';

    protected $description = 'Prune old captured emails from the mailbox';

    public function handle(PruneService $pruneService): int
    {
        $days = (int) ($this->option('days') ?? config('mailbox.prune.older_than_days', 7));

        $deleted = $pruneService->prune($days);

        $this->components->info(sprintf('Pruned %d %s older than %d days.', $deleted, $deleted === 1 ? 'email' : 'emails', $days));

        return self::SUCCESS;
    }
}
