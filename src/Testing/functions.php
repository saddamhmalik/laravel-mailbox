<?php

declare(strict_types=1);

use LaravelMailbox\Contracts\MailboxContract;
use LaravelMailbox\Testing\MailboxExpectation;

if (! function_exists('expectMailbox')) {
    function expectMailbox(): MailboxExpectation
    {
        return new MailboxExpectation(app(MailboxContract::class));
    }
}
