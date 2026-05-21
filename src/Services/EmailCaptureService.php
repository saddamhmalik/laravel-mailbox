<?php

declare(strict_types=1);

namespace LaravelMailbox\Services;

use LaravelMailbox\Contracts\EmailRepositoryContract;
use LaravelMailbox\Contracts\EmailStorageContract;
use LaravelMailbox\Data\CapturedEmailData;
use LaravelMailbox\Events\EmailCaptured;
use LaravelMailbox\Models\MailboxEmail;
use LaravelMailbox\Storage\ArrayEmailStorage;

final class EmailCaptureService
{
    public function __construct(
        private readonly EmailStorageContract $storage,
        private readonly ?EmailRepositoryContract $repository = null,
        private readonly int $maxEmails = 1000,
    ) {}

    public function capture(CapturedEmailData $data): MailboxEmail
    {
        $email = $this->storage->store($data);

        if ($this->repository !== null && ! $this->storage instanceof ArrayEmailStorage) {
            $this->enforceMaxEmails();
        }

        event(new EmailCaptured($email));

        return $email;
    }

    private function enforceMaxEmails(): void
    {
        if ($this->repository === null) {
            return;
        }

        $count = $this->repository->count();

        if ($count <= $this->maxEmails) {
            return;
        }

        $excess = $count - $this->maxEmails;

        MailboxEmail::query()
            ->orderBy('created_at')
            ->limit($excess)
            ->delete();
    }
}
