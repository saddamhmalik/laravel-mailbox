<?php

declare(strict_types=1);

namespace LaravelMailbox\Testing;

use Closure;
use LaravelMailbox\Models\MailboxEmail;
use PHPUnit\Framework\Assert as PHPUnit;

final class MailboxAssertions
{
    /**
     * @param  array<int, MailboxEmail>  $emails
     */
    public function __construct(
        private readonly array $emails,
    ) {}

    public function assertSent(?Closure $callback = null): self
    {
        PHPUnit::assertNotEmpty(
            $this->emails,
            'The mailbox did not capture any emails.',
        );

        if ($callback !== null) {
            $matched = collect($this->emails)->contains($callback);

            PHPUnit::assertTrue(
                $matched,
                'The mailbox did not capture an email matching the given criteria.',
            );
        }

        return $this;
    }

    public function assertNothingSent(): self
    {
        PHPUnit::assertEmpty(
            $this->emails,
            sprintf(
                'The mailbox captured %d unexpected %s.',
                count($this->emails),
                count($this->emails) === 1 ? 'email' : 'emails',
            ),
        );

        return $this;
    }

    public function assertSentCount(int $count): self
    {
        PHPUnit::assertCount(
            $count,
            $this->emails,
            sprintf(
                'The mailbox captured %d emails instead of %d.',
                count($this->emails),
                $count,
            ),
        );

        return $this;
    }

    public function assertSentTo(string $address): self
    {
        return $this->assertSent(static fn (MailboxEmail $email): bool => $email->hasTo($address));
    }

    public function assertSentWithSubject(string $subject): self
    {
        return $this->assertSent(static fn (MailboxEmail $email): bool => $email->hasSubject($subject));
    }
}
