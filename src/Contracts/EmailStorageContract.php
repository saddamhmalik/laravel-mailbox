<?php

declare(strict_types=1);

namespace LaravelMailbox\Contracts;

use LaravelMailbox\Data\CapturedEmailData;
use LaravelMailbox\Models\MailboxEmail;

interface EmailStorageContract
{
    public function store(CapturedEmailData $data): MailboxEmail;

    /**
     * @return array<int, MailboxEmail>
     */
    public function all(): array;

    public function flush(): void;
}
