<?php

declare(strict_types=1);

namespace LaravelMailbox\Storage;

use LaravelMailbox\Contracts\EmailRepositoryContract;
use LaravelMailbox\Contracts\EmailStorageContract;
use LaravelMailbox\Data\CapturedEmailData;
use LaravelMailbox\Models\MailboxEmail;

final class DatabaseEmailStorage implements EmailStorageContract
{
    public function __construct(
        private readonly EmailRepositoryContract $repository,
    ) {}

    public function store(CapturedEmailData $data): MailboxEmail
    {
        return $this->repository->store($data);
    }

    public function all(): array
    {
        return [];
    }

    public function flush(): void
    {
        $this->repository->deleteAll();
    }
}
