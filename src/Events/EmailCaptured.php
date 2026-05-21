<?php

declare(strict_types=1);

namespace LaravelMailbox\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use LaravelMailbox\Models\MailboxEmail;

class EmailCaptured
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly MailboxEmail $email,
    ) {}
}
