<?php

declare(strict_types=1);

namespace LaravelMailbox\Testing;

use LaravelMailbox\Contracts\MailboxContract;
use LaravelMailbox\Models\MailboxEmail;
use PHPUnit\Framework\Assert as PHPUnit;

final class MailboxExpectation
{
    private ?string $recipient = null;

    private ?string $subject = null;

    private ?string $from = null;

    private ?int $count = null;

    public function __construct(
        private readonly MailboxContract $mailbox,
    ) {}

    public function to(string $address): self
    {
        $this->recipient = $address;

        return $this;
    }

    public function from(string $address): self
    {
        $this->from = $address;

        return $this;
    }

    public function withSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function count(int $count): self
    {
        $this->count = $count;

        return $this;
    }

    public function assert(): void
    {
        $emails = $this->mailbox instanceof \LaravelMailbox\Services\MailboxService
            ? $this->mailbox->emails()
            : [];

        if ($this->count !== null) {
            PHPUnit::assertCount($this->count, $emails);

            return;
        }

        $matched = collect($emails)->contains(function (MailboxEmail $email): bool {
            if ($this->recipient !== null && ! $email->hasTo($this->recipient)) {
                return false;
            }

            if ($this->from !== null && ! $email->hasFrom($this->from)) {
                return false;
            }

            if ($this->subject !== null && ! $email->hasSubject($this->subject)) {
                return false;
            }

            return true;
        });

        PHPUnit::assertTrue(
            $matched,
            'No captured email matched the mailbox expectation.',
        );
    }
}
