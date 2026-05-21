<?php

declare(strict_types=1);

namespace LaravelMailbox\Contracts;

use Closure;
use LaravelMailbox\Data\CapturedEmailData;
use LaravelMailbox\Testing\MailboxAssertions;

interface MailboxContract
{
    public function capture(CapturedEmailData $data): void;

    public function assertSent(Closure|int|null $callback = null): MailboxAssertions;

    public function assertNothingSent(): MailboxAssertions;

    public function assertSentCount(int $count): MailboxAssertions;

    public function flush(): void;

    public function count(): int;
}
