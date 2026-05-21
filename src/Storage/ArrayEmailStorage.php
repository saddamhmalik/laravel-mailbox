<?php

declare(strict_types=1);

namespace LaravelMailbox\Storage;

use LaravelMailbox\Contracts\EmailStorageContract;
use LaravelMailbox\Data\CapturedEmailData;
use LaravelMailbox\Models\MailboxEmail;

final class ArrayEmailStorage implements EmailStorageContract
{
    /** @var array<int, MailboxEmail> */
    private array $emails = [];

    private int $id = 0;

    public function store(CapturedEmailData $data): MailboxEmail
    {
        $this->id++;

        $email = new MailboxEmail($data->toArray());
        $email->id = $this->id;
        $email->exists = true;

        $this->emails[$this->id] = $email;

        return $email;
    }

    public function all(): array
    {
        return array_values($this->emails);
    }

    public function flush(): void
    {
        $this->emails = [];
        $this->id = 0;
    }
}
