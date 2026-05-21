<?php

declare(strict_types=1);

namespace LaravelMailbox\Transport;

use LaravelMailbox\Contracts\MailboxContract;
use LaravelMailbox\Support\EmailMessageParser;
use Stringable;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;

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
                mailer: config('mail.default'),
                queue: $this->resolveQueue(),
            ));

            return;
        }

        if ($original instanceof RawMessage) {
            $this->mailbox->capture($this->parser->parseRaw(
                $original,
                mailer: config('mail.default'),
                queue: $this->resolveQueue(),
            ));
        }
    }

    private function resolveQueue(): ?string
    {
        if (app()->runningInConsole() && app()->bound('queue.connection')) {
            return config('queue.default');
        }

        return null;
    }
}
