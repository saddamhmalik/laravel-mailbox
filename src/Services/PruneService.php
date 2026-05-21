<?php

declare(strict_types=1);

namespace LaravelMailbox\Services;

use LaravelMailbox\Contracts\EmailRepositoryContract;

final class PruneService
{
    public function __construct(
        private readonly EmailRepositoryContract $repository,
    ) {}

    public function prune(int $olderThanDays): int
    {
        $date = now()->subDays($olderThanDays);

        return $this->repository->pruneOlderThan($date);
    }
}
