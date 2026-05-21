<?php

declare(strict_types=1);

namespace LaravelMailbox\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class InstallCommand extends Command
{
    protected $signature = 'mailbox:install
                            {--migrate : Run migrations after publishing}
                            {--no-assets : Skip publishing UI assets}
                            {--force : Overwrite existing files}';

    protected $description = 'Install Laravel Mailbox';

    public function handle(): int
    {
        $this->components->info('Installing Laravel Mailbox...');

        $force = (bool) $this->option('force');

        $this->call('vendor:publish', [
            '--tag' => 'mailbox-config',
            '--force' => $force,
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'mailbox-migrations',
            '--force' => $force,
        ]);

        if (! $this->option('no-assets')) {
            $this->call('vendor:publish', [
                '--tag' => 'mailbox-assets',
                '--force' => $force,
            ]);
        }

        if ($this->option('migrate') || $this->confirm('Run mailbox migrations now?', true)) {
            $this->runMailboxMigrations();
        }

        $this->newLine();
        $this->components->info('Add the following to your .env file:');
        $this->line('MAIL_MAILER=mailbox');
        $this->newLine();
        $this->components->info('Add this mailer to config/mail.php:');
        $this->line(<<<'PHP'
'mailers' => [
    // ...
    'mailbox' => [
        'transport' => 'mailbox',
    ],
],
PHP);

        $this->newLine();
        $this->components->info('Open your inbox: php artisan mailbox:open');

        return self::SUCCESS;
    }

    private function runMailboxMigrations(): void
    {
        $paths = collect(File::glob(database_path('migrations/*mailbox_emails*.php')))
            ->filter(static fn (string $path): bool => ! str_ends_with($path, '.stub'))
            ->values();

        if ($paths->isEmpty()) {
            $this->components->warn('No mailbox migrations found. Run vendor:publish --tag=mailbox-migrations first.');

            return;
        }

        foreach ($paths as $path) {
            $this->call('migrate', [
                '--path' => $path,
                '--realpath' => true,
                '--force' => true,
            ]);
        }
    }
}
