<?php

declare(strict_types=1);

namespace LaravelMailbox\Drivers;

use Illuminate\Mail\MailManager;
use LaravelMailbox\Contracts\MailboxContract;
use LaravelMailbox\Support\EmailMessageParser;
use LaravelMailbox\Transport\MailboxTransport;

final class MailboxMailDriverRegistrar
{
    public static function register(MailManager $manager): void
    {
        $manager->extend('mailbox', static function (array $config = []) use ($manager): MailboxTransport {
            return new MailboxTransport(
                mailbox: $manager->getApplication()->make(MailboxContract::class),
                parser: $manager->getApplication()->make(EmailMessageParser::class),
            );
        });
    }
}
