<?php

declare(strict_types=1);

namespace LaravelMailbox\Transport;

use LaravelMailbox\Contracts\MailboxContract;
use LaravelMailbox\Support\EmailMessageParser;
use Symfony\Component\Mailer\Transport\AbstractTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;

final class MailboxTransportFactory extends AbstractTransportFactory
{
    protected function getSupportedSchemes(): array
    {
        return ['mailbox'];
    }

    protected function createTransport(Dsn $dsn): TransportInterface
    {
        return new MailboxTransport(
            mailbox: app(MailboxContract::class),
            parser: app(EmailMessageParser::class),
        );
    }
}
