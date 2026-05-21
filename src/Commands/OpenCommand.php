<?php

declare(strict_types=1);

namespace LaravelMailbox\Commands;

use Illuminate\Console\Command;
final class OpenCommand extends Command
{
    protected $signature = 'mailbox:open';

    protected $description = 'Open the mailbox UI in your browser';

    public function handle(): int
    {
        $url = url(config('mailbox.route.prefix', 'mailbox'));

        $this->components->info('Opening mailbox at: '.$url);

        if (PHP_OS_FAMILY !== 'Windows') {
            exec(sprintf('open %s 2>/dev/null || xdg-open %s 2>/dev/null', escapeshellarg($url), escapeshellarg($url)));
        }

        return self::SUCCESS;
    }
}
