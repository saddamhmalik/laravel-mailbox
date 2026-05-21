<?php

declare(strict_types=1);

namespace LaravelMailbox\Commands;

use Illuminate\Console\Command;
use LaravelMailbox\Contracts\MailboxContract;

final class ClearCommand extends Command
{
    protected $signature = 'mailbox:clear';

    protected $description = 'Clear all captured emails from the mailbox';

    public function handle(MailboxContract $mailbox): int
    {
        $count = $mailbox->count();
        $mailbox->flush();

        $this->components->info(sprintf('Cleared %d captured %s.', $count, $count === 1 ? 'email' : 'emails'));

        return self::SUCCESS;
    }
}
