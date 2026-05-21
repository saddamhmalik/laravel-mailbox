<?php

declare(strict_types=1);

namespace LaravelMailbox\Transport;

use LaravelMailbox\Contracts\MailboxContract;
use LaravelMailbox\Support\EmailMessageParser;
use Symfony\Component\Mailer\Exception\UnsupportedSchemeException;
use Symfony\Component\Mailer\Transport\AbstractTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\TransportInterface;

final class MailboxTransportFactory extends AbstractTransportFactory
{
    public function create(Dsn $dsn): TransportInterface
    {
        if ($dsn->getScheme() === 'mailbox') {
            return new MailboxTransport(
                mailbox: app(MailboxContract::class),
                parser: app(EmailMessageParser::class),
            );
        }

        throw new UnsupportedSchemeException($dsn, 'mailbox', $this->getSupportedSchemes());
    }

    /**
     * @return list<string>
     */
    protected function getSupportedSchemes(): array
    {
        return ['mailbox'];
    }
}
