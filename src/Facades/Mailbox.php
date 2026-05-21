<?php

declare(strict_types=1);

namespace LaravelMailbox\Facades;

use Closure;
use Illuminate\Support\Facades\Facade;
use LaravelMailbox\Contracts\MailboxContract;
use LaravelMailbox\Testing\MailboxAssertions;

/**
 * @method static void capture(\LaravelMailbox\Data\CapturedEmailData $data)
 * @method static MailboxAssertions assertSent(Closure|int|null $callback = null)
 * @method static MailboxAssertions assertNothingSent()
 * @method static MailboxAssertions assertSentCount(int $count)
 * @method static void flush()
 * @method static int count()
 *
 * @see MailboxContract
 */
class Mailbox extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return MailboxContract::class;
    }
}
