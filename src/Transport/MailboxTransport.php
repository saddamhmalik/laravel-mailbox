<?php

declare(strict_types=1);

namespace LaravelMailbox\Transport;

use LaravelMailbox\Contracts\MailboxContract;
use LaravelMailbox\Support\EmailMessageParser;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Email;

final class MailboxTransport extends AbstractTransport
{
    public function __construct(
        private readonly MailboxContract $mailbox,
        private readonly EmailMessageParser $parser,
    ) {
        parent::__construct();
    }

    public function __toString(): string
    {
        return 'mailbox';
    }

    protected function doSend(SentMessage $message): void
    {
        $original = $message->getOriginalMessage();

        if ($original instanceof Email) {
            $this->mailbox->capture($this->parser->parse(
                $original,
                mailer: $this->mailerName(),
                queue: $this->resolveQueue(),
            ));

            return;
        }

        $this->mailbox->capture($this->parser->parseRaw(
            $original,
            mailer: $this->mailerName(),
            queue: $this->resolveQueue(),
        ));
    }

    private function mailerName(): ?string
    {
        $mailer = config('mail.default');

        return is_string($mailer) ? $mailer : null;
    }

    private function resolveQueue(): ?string
    {
        if (app()->runningInConsole() && app()->bound('queue.connection')) {
            $queue = config('queue.default');

            return is_string($queue) ? $queue : null;
        }

        return null;
    }
}
